<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\AuditLog;

use App\Support\Context\TenantContext;

class AppointmentController extends Controller
{
    private TenantContext $context;

    public function __construct(TenantContext $context)
    {
        $this->context = $context;
    }

    public function index(Request $request)
    {
        $query = Appointment::with('patient')->where('doctor_id', $this->context->getClinicOwner()->id);
        
        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => [
                'required',
                Rule::exists('patients', 'id')->where(function ($query) {
                    $query->where('doctor_id', $this->context->getClinicOwner()->id);
                }),
            ],
            'appointment_date' => 'required|date',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:Scheduled,Completed,Cancelled'
        ]);

        $ownerId = $this->context->getClinicOwner()->id;

        return \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $request, $ownerId) {
            
            // 0. Enforce Plan Limits (Race-Condition Safe)
            // Lock the owner row to prevent concurrent inserts bypassing limit
            $owner = \App\Models\User::where('id', $ownerId)->lockForUpdate()->first();
            $plan = $owner->plan;

            $limit = $plan->max_appointments_monthly;

            // Only enforce if plan has a limit AND user has valid billing window
            if ($plan && !$plan->isUnlimited('max_appointments_monthly') && $owner->subscription_started_at && $owner->subscription_renews_at) {
                
                // Count appointments created within the current billing cycle window
                // Uses the new composite index (doctor_id, created_at)
                $currentCount = \App\Models\Appointment::where('doctor_id', $ownerId)
                    ->whereBetween('created_at', [
                        $owner->subscription_started_at,
                        $owner->subscription_renews_at
                    ])
                    ->count();
                
                if ($currentCount >= $limit) {
                    abort(422, "Appointment limit reached for this billing cycle ({$limit}). Please upgrade.");
                }
            }

            // 1. Create Appointment
            $appointment = Appointment::create([
                ...$validated,
                'doctor_id' => $ownerId,
                'status' => $validated['status'] ?? 'Scheduled'
            ]);

            // 2. Audit Log
            AuditLog::log(
                'appointment_created',
                "Scheduled appointment for patient #{$appointment->patient_id} on {$appointment->appointment_date}",
                ['appointment_id' => $appointment->id]
            );

            return response()->json($appointment, 201);
        });
    }

    public function update(Request $request, Appointment $appointment)
    {
        if ($appointment->doctor_id !== $this->context->getClinicOwner()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'appointment_date' => 'sometimes|date',
            'notes' => 'sometimes|string',
            'status' => 'sometimes|string|in:Scheduled,Completed,Cancelled'
        ]);

        $appointment->update($validated);

        return response()->json($appointment);
    }

    public function destroy(Appointment $appointment)
    {
        try {
            return response()->json(app(DeleteManager::class)->delete('appointment', $appointment->id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
