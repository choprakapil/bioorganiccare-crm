<?php

namespace App\Services\Deletions;

use App\Models\LocalMedicine;

class LocalMedicineDeletionHandler implements DeletionHandlerInterface
{
    public function summary(int $id): array
    {
        $item = LocalMedicine::withTrashed()->findOrFail($id);
        return [
            'entity'            => 'local_medicine',
            'id'                => $id,
            'entity_name'       => $item->item_name,
            'is_archived'       => (bool) $item->deleted_at,
            'force_delete_safe' => true,
            'cascade_deletable' => false,
        ];
    }
}
