<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use App\Models\AuditLog;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Enums\TreatmentStatus;

use App\Support\Context\TenantContext;

class TreatmentController extends Controller
{
    private TenantContext $context;

    public function __construct(TenantContext $context)
    {
        $this->context = $context;
    }

    public function getByPatient($patientId)
    {
        $ownerId = $this->context->getClinicOwner()->id;
        $patient = Patient::where('doctor_id', $ownerId)->findOrFail($patientId);
        
        // Tenancy Check
        if ($patient->doctor_id !== $ownerId) {
            return response()->json(['message' => 'Unauthorized access to patient records'], 403);
        }

        $treatments = Treatment::where('patient_id', $patientId)
            ->where('doctor_id', $ownerId)
            ->latest()
            ->get();

        return response()->json($treatments);
    }

    public function store(Request $request)
    {
        $ownerId = $this->context->getClinicOwner()->id;
        $specialtyId = $this->context->getSpecialtyId();

        $validated = $request->validate([
            'patient_id' => [
                'required',
                Rule::exists('patients', 'id')->where(function ($query) use ($ownerId) {
                    $query->where('doctor_id', $ownerId);
                }),
            ],
            'catalog_id' => [
                'nullable',
                function ($attribute, $value, $fail) use ($specialtyId) {
                    if (is_string($value) && str_starts_with($value, 'local_')) {
                        // It's a local service ID, pass validation
                        return;
                    }
                    if (!\App\Models\ClinicalCatalog::where('id', $value)->where('specialty_id', $specialtyId)->exists()) {
                        $fail('The selected clinical catalog item is invalid.');
                    }
                },
            ],
            'inventory_id' => [
                'nullable',
                Rule::exists('inventory', 'id')->where(function ($query) use ($ownerId) {
                    $query->where('doctor_id', $ownerId);
                }),
            ],
            'procedure_name' => 'required|string',
            'teeth' => 'nullable|string',
            'notes' => 'nullable|string',
            'fee' => 'required|numeric',
            'quantity' => 'nullable|integer|min:1',
            'status' => 'required|in:' . implode(',', TreatmentStatus::all()),
        ]);

        // XOR Validation: Must have exactly one source
        if (!empty($validated['catalog_id']) && !empty($validated['inventory_id'])) {
            return response()->json(['message' => 'Cannot link both Service and Medicine.'], 422);
        }

        // Handle local vs global treatment selection
        if (!empty($validated['catalog_id']) && is_string($validated['catalog_id']) && str_starts_with($validated['catalog_id'], 'local_')) {
            $localId = str_replace('local_', '', $validated['catalog_id']);
            $localService = \Illuminate\Support\Facades\DB::table('local_services')
                ->where('id', $localId)
                ->where('doctor_id', $ownerId)
                ->first();
                
            if (!$localService) {
                return response()->json(['message' => 'Invalid local service selected.'], 422);
            }
            
            // Override catalog_id to null, ensure procedure_name matches local service
            $validated['catalog_id'] = null;
            $validated['procedure_name'] = $localService->item_name;
        } elseif (!empty($validated['catalog_id'])) {
            // Governance Guard: Prevent use of archived/deleted services (Global only)
            $service = \App\Models\ClinicalCatalog::withTrashed()
                ->where('specialty_id', $specialtyId)
                ->findOrFail($validated['catalog_id']);
            if ($service->trashed()) {
                return response()->json(['message' => 'Cannot record treatment: Service is archived in the clinical catalog.'], 422);
            }
        }

        $patient = Patient::where('doctor_id', $ownerId)->findOrFail($validated['patient_id']);
        if ($patient->doctor_id !== $ownerId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $unitCost = null;
        if (!empty($validated['inventory_id'])) {
            $inventory = \App\Models\Inventory::where('doctor_id', $ownerId)->findOrFail($validated['inventory_id']);
            if ($inventory->doctor_id !== $ownerId) {
                return response()->json(['message' => 'Unauthorized inventory access'], 403);
            }
            $unitCost = $inventory->purchase_cost;
        }

        $treatment = Treatment::create([
            ...$validated,
            'quantity' => $validated['quantity'] ?? 1,
            'unit_cost' => $unitCost,
            'doctor_id' => $ownerId,
        ]);

        AuditLog::log(
            'treatment_created',
            "Recorded treatment '{$treatment->procedure_name}' for patient #{$patient->id}",
            ['treatment_id' => $treatment->id, 'patient_id' => $patient->id]
        );

        return response()->json($treatment, 201);
    }

    public function update(Request $request, Treatment $treatment)
    {
        if ($treatment->doctor_id !== $this->context->getClinicOwner()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:' . implode(',', TreatmentStatus::all()),
            'notes' => 'sometimes|string|nullable',
            'teeth' => 'sometimes|string|nullable',
        ]);

        $treatment->update($validated);

        AuditLog::log(
            'treatment_updated',
            "Updated treatment '{$treatment->procedure_name}' for patient #{$treatment->patient_id}",
            ['treatment_id' => $treatment->id, 'changes' => $validated]
        );

        return response()->json($treatment);
    }

    public function destroy(Treatment $treatment)
    {
        try {
            return response()->json(app(DeleteManager::class)->delete('treatment', $treatment->id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
