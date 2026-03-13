<?php

namespace App\Services\Deletions;

use App\Models\MasterMedicine;
use App\Models\Inventory;

class MedicineDeletionHandler implements DeletionHandlerInterface
{
    public function summary(int $id): array
    {
        $med       = MasterMedicine::withTrashed()->findOrFail($id);
        $inventory = Inventory::withTrashed()->where('master_medicine_id', $id)->count();
        return [
            'entity'            => 'medicine',
            'id'                => $id,
            'entity_name'       => $med->name,
            'is_archived'       => (bool) $med->deleted_at,
            'inventory_batches' => $inventory,
            'force_delete_safe' => ($inventory === 0),
            'cascade_deletable' => false,
        ];
    }
}
