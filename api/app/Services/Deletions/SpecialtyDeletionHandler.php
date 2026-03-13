<?php

namespace App\Services\Deletions;

use App\Models\Specialty;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\ClinicalCatalog;

class SpecialtyDeletionHandler implements DeletionHandlerInterface
{
    public function summary(int $id): array
    {
        $specialty = Specialty::withTrashed()->findOrFail($id);
        $doctors   = User::withTrashed()->where('specialty_id', $id)->where('role', 'doctor')->count();
        $plans     = SubscriptionPlan::where('specialty_id', $id)->count();
        $clinicalItems = ClinicalCatalog::withTrashed()->where('specialty_id', $id)->count();

        return [
            'entity'            => 'specialty',
            'id'                => $id,
            'entity_name'       => $specialty->name,
            'is_archived'       => (bool) $specialty->deleted_at,
            'doctors'           => $doctors,
            'plans'             => $plans,
            'clinical_items'    => $clinicalItems,
            'force_delete_safe' => ($doctors === 0 && $plans === 0 && $clinicalItems === 0),
            'cascade_deletable' => true,
        ];
    }
}
