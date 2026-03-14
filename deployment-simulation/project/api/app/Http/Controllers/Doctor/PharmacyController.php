<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\MasterMedicine;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Support\Context\TenantContext;

class PharmacyController extends Controller
{
    private TenantContext $context;

    public function __construct(TenantContext $context)
    {
        $this->context = $context;
    }

    /**
     * List all active Master Medicines with Doctor's inventory status.
     */
    public function catalog(Request $request)
    {
        $doctor = $this->context->getClinicOwner();
        $specialtyId = $this->context->getSpecialtyId();

        if (!$specialtyId) {
            return response()->json(['message' => 'Specialty context is required.'], 422);
        }

        // Fetch active medicines with their inventory record for this doctor
        // Filter by Doctor's Specialty to ensure clean data separation
        $query = MasterMedicine::withTrashed()
            ->where('specialty_id', $specialtyId)
            ->with(['inventory' => function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id);
            }]);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $medicines = $query->orderBy('name')->limit(100)->get();

        // Transform to include 'is_in_inventory' flag and inventory details
        $medicines->transform(function($med) {
            $med->in_inventory = $med->inventory->isNotEmpty();
            $med->inventory_item = $med->inventory->first(); // Single item per doctor per master
            unset($med->inventory);
            $med->is_archived = $med->trashed();
            $med->is_usable = !$med->trashed() && $med->is_active;
            return $med;
        });

        return response()->json($medicines);
    }


}
