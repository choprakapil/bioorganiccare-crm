<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Enums\InvoiceStatus;
use App\Enums\TreatmentStatus;

use App\Support\Context\TenantContext;

class PatientController extends Controller
{
    private TenantContext $context;

    public function __construct(TenantContext $context)
    {
        $this->context = $context;
    }

    public function index()
    {
        // Strict Tenancy: Doctor and Staff see their Clinic's patients
        // Optimization: Remove deep eager loading and implement pagination
        $patients = Patient::where('doctor_id', $this->context->getClinicOwner()->id)
            ->withSum('invoices as invoice_total', 'total_amount')
            ->withSum('invoices as amount_paid', 'paid_amount')
            ->latest()
            ->paginate(20);

        return response()->json($patients);
    }

    /**
     * Get full patient profile with all relations for deep dives.
     */
    public function full($id)
    {
        $ownerId = $this->context->getClinicOwner()->id;
        $patient = Patient::with(['invoices.items', 'appointments', 'treatments'])
            ->where('doctor_id', $ownerId)
            ->findOrFail($id);

        if ($patient->doctor_id !== $ownerId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($patient);
    }

    public function store(Request $request, \App\Services\InvoiceService $invoiceService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('patients')->where(function ($query) {
                    return $query->where('doctor_id', $this->context->getClinicOwner()->id);
                })
            ],
            'age' => 'nullable|integer',
            'gender' => 'nullable|string',
            'address' => 'nullable|string',
            'first_visit_date' => 'nullable|date',
            // Billing & Clinical
            'payment_status' => 'nullable|string|in:' . implode(',', InvoiceStatus::all()),
            'payment_method' => 'nullable|string',
            'services' => 'nullable|array',
            'medicines' => 'nullable|array',
            'subtotal' => 'nullable|numeric', 
            'discount' => 'nullable|numeric',
            'total_amount' => 'nullable|numeric',
            'paid_amount' => 'nullable|numeric',
            'balance_due' => 'nullable|numeric',
            'due_date' => 'nullable|date',
        ]);

        // Validate Partial Payment Logic
        if (isset($validated['payment_status']) && $validated['payment_status'] === InvoiceStatus::PARTIAL) {
            if (!isset($validated['paid_amount']) || !isset($validated['balance_due'])) {
                return response()->json(['message' => 'Partial payment requires paid_amount and balance_due'], 422);
            }
        }

        $ownerId = $this->context->getClinicOwner()->id;

        return \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $request, $ownerId, $invoiceService) {
            // 0. Enforce Plan Limits (Race-Condition Safe)
            $owner = \App\Models\User::where('id', $ownerId)->lockForUpdate()->first();
            $plan = $owner->plan;

            if ($plan && !$plan->isUnlimited('max_patients')) {
                $currentCount = \App\Models\Patient::where('doctor_id', $ownerId)->count();
                if ($currentCount >= $plan->max_patients) {
                    abort(422, "Patient limit reached for your plan ({$plan->max_patients}). Please upgrade.");
                }
            }

            // 1. Create Patient
            $patient = Patient::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'age' => $validated['age'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'address' => $validated['address'] ?? null,
                'first_visit_date' => $validated['first_visit_date'] ?? now(),
                'doctor_id' => $ownerId,
            ]);

            // 2. Prepare Treatments if Services/Medicines present
            $hasBilling = !empty($validated['services']) || !empty($validated['medicines']);
            $treatmentIds = [];

            if ($hasBilling) {
                // Process Services (Treatments in Catalog)
                if (!empty($validated['services'])) {
                    foreach ($validated['services'] as $service) {
                        $fee = $service['billed_price'] ?? $service['default_fee'] ?? 0;
                        
                        $catalogId = $service['id'];
                        $procedureName = $service['item_name'];

                        if (!empty($service['is_local']) && $service['is_local'] === true) {
                            $catalogId = null;
                        }

                        $t = \App\Models\Treatment::create([
                            'patient_id' => $patient->id,
                            'doctor_id' => $ownerId,
                            'catalog_id' => $catalogId,
                            'procedure_name' => $procedureName,
                            'status' => TreatmentStatus::COMPLETED,
                            'fee' => $fee,
                            'quantity' => 1
                        ]);
                        $treatmentIds[] = $t->id;
                    }
                }

                // Process Medicines (Inventory)
                if (!empty($validated['medicines'])) {
                    foreach ($validated['medicines'] as $medicine) {
                        $qty = $medicine['quantity'] ?? 1;
                        $unitPrice = $medicine['sale_price'] ?? 0;

                        // Create Treatment for Medicine to enable FIFO processing
                        $t = \App\Models\Treatment::create([
                            'patient_id' => $patient->id,
                            'doctor_id' => $ownerId,
                            'inventory_id' => $medicine['id'],
                            'procedure_name' => $medicine['item_name'],
                            'status' => TreatmentStatus::COMPLETED,
                            'fee' => $unitPrice,
                            'quantity' => $qty,
                        ]);
                        $treatmentIds[] = $t->id;
                    }
                }

                // 3. Delegate Invoice Creation & FIFO Deductions to Service
                if (!empty($treatmentIds)) {
                     $invoiceService->createFromTreatments([
                        'doctor_id' => $ownerId,
                        'patient_id' => $patient->id,
                        'treatment_ids' => $treatmentIds,
                        'status' => $validated['payment_status'] ?? InvoiceStatus::UNPAID,
                        'paid_amount' => $validated['paid_amount'] ?? 0,
                        'due_date' => $validated['due_date'] ?? null,
                        'payment_method' => $validated['payment_method'] ?? 'Cash',
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Patient registered successfully',
                'patient' => $patient
            ], 201);
        });
    }

    public function show(Patient $patient)
    {
        if ($patient->doctor_id !== $this->context->getClinicOwner()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($patient);
    }

    public function update(Request $request, Patient $patient)
    {
        if ($patient->doctor_id !== $this->context->getClinicOwner()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
                'phone' => [
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('patients')->ignore($patient->id)->where(function ($query) {
                        return $query->where('doctor_id', $this->context->getClinicOwner()->id);
                    })
                ],
            'age' => 'nullable|integer',
            'gender' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $patient->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Patient updated successfully',
            'patient' => $patient
        ]);
    }

    public function destroy(Patient $patient)
    {
        try {
            return response()->json(app(DeleteManager::class)->delete('patient', $patient->id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function restore(\Illuminate\Http\Request $request, $id)
    {
        try {
            return response()->json(app(DeleteManager::class)->restore('patient', $id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
