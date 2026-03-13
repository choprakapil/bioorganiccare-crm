<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Specialty;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    /**
     * List all doctors in the system.
     */
    public function index()
    {
        try {
            $doctors = User::where('role', 'doctor')
                ->where('email', 'not like', '%.deleted.%') // Exclude soft-deleted
                ->with(['plan', 'specialty'])
                ->withCount('patients')
                ->withCount('staff')
                ->withCount(['appointments as this_month_appointments_count' => function ($query) {
                    $query->whereMonth('appointment_date', now()->month)
                          ->whereYear('appointment_date', now()->year);
                }])
                ->latest()
                ->get();

            return response()->json($doctors);
        } catch (\Exception $e) {
            // Fail-safe: Return empty list or error, but don't crash UI with HTML
            \Illuminate\Support\Facades\Log::error('Doctors Index Failed: ' . $e->getMessage());
            // Return empty array to default to "No records" instead of crashing frontend
            return response()->json([], 200); 
        }
    }

    public function getLogs(User $user)
    {
        if ($user->role !== 'doctor') {
            return response()->json(['message' => 'Not a doctor account'], 403);
        }

        // Get logs for Doctor AND their Staff
        $teamIds = \App\Models\User::where('doctor_id', $user->id)->pluck('id')->push($user->id);

        $logs = \App\Models\AuditLog::whereIn('user_id', $teamIds)
            ->with('user:id,name,role,role_type') // Context for Admin
            ->latest()
            ->take(50)
            ->get();

        return response()->json($logs);
    }

    /**
     * Get reference data for the creation form.
     */
    public function getReferenceData()
    {
        return response()->json([
            'specialties' => Specialty::all(),
            'plans' => SubscriptionPlan::all()
        ]);
    }

    /**
     * Register a new doctor.
     */
    public function store(Request $request)
    {
        // 1-5. Transaction & Logic Update
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'phone' => 'nullable|string',
                'specialty_id' => 'required|exists:specialties,id',
                'plan_id' => 'nullable|exists:subscription_plans,id', // Make nullable to allow auto-assign
                'clinic_name' => 'nullable|string|max:255',
            ]);

            $planId = $validated['plan_id'] ?? null;
            $specialty = Specialty::findOrFail($validated['specialty_id']);

            // Plan Assignment Logic
            if ($planId) {
                // If plan is provided, validate it belongs to specialty (if enforced) or just exists (done by validation)
                $plan = \App\Models\SubscriptionPlan::find($planId);
                
                // SCHEMA-SAFE MATCHING VALIDATION
                if (\Illuminate\Support\Facades\Schema::hasColumn('subscription_plans', 'specialty_id')) {
                    if ($plan->specialty_id && $plan->specialty_id != $specialty->id) {
                        abort(422, 'Selected plan does not belong to the chosen specialty.');
                    }
                }
            } else {
                // FALLBACK: Use Default Plan
                if (!$specialty->default_plan_id) {
                    abort(422, 'Specialty default plan not configured. Cannot create doctor.');
                }
                $planId = $specialty->default_plan_id;
            }

            // Create Doctor with GUARANTEED Plan ID & Billing Lifecycle
            $doctor = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => 'doctor',
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'specialty_id' => $specialty->id,
                'plan_id' => $planId, // Guaranteed Non-Null
                'clinic_name' => $validated['clinic_name'] ?? null,
                'is_active' => true,
                'brand_color' => '#4f46e5',
                'brand_secondary_color' => '#f8fafc',
                
                // Initialize Billing Window
                'subscription_started_at' => now(),
                'subscription_renews_at' => now()->addMonth(),
                'billing_interval' => 'monthly',
                'subscription_status' => 'active',
                'subscription_grace_ends_at' => null,
            ]);

            // NOTIFY SUPER ADMINS
            $admin = User::where('role', 'super_admin')->first();
            if ($admin) {
                // Reload plan to get name safely
                $planName = $doctor->plan ? $doctor->plan->name : 'Unknown';
                
                \App\Models\Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'success',
                    'title' => 'New Clinic Onboarded',
                    'message' => "Dr. {$doctor->name} has joined the platform with the {$planName} plan."
                ]);
            }

            return response()->json($doctor, 201);
        });
    }

    /**
     * Toggle a doctor's activation status.
     */
    public function toggleActive(User $user)
    {
        if ($user->role !== 'doctor') {
            return response()->json(['message' => 'Only doctor accounts can be toggled.'], 403);
        }

        $newStatus = !$user->is_active;
        $user->update(['is_active' => $newStatus]);

        if (!$newStatus) {
            // If deactivating, kill sessions immediately
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => $newStatus ? 'Account activated successfully.' : 'Account deactivated. User logged out.',
            'is_active' => $user->is_active
        ]);
    }

    public function show(User $user)
    {
        if ($user->role !== 'doctor') {
            return response()->json(['message' => 'Not a doctor account'], 404);
        }
        return response()->json($user->load(['plan', 'specialty', 'staff']));
    }

    public function resetPassword(Request $request, User $user)
    {
        if ($user->role !== 'doctor') {
            return response()->json(['message' => 'Not a doctor account'], 403);
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8'
        ]);

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        // Security: Force logout from all sessions
        $user->tokens()->delete();

        return response()->json(['message' => 'Password reset successfully. User logged out from all devices.']);
    }

    public function destroy(User $user)
    {
        try {
            return response()->json(app(DeleteManager::class)->delete('doctor', $user->id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
