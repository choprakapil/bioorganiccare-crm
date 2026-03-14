<?php

namespace App\Services\Deletions;

use App\Models\LocalService;

class LocalServiceDeletionHandler implements DeletionHandlerInterface
{
    public function summary(int $id): array
    {
        $item = LocalService::withTrashed()->findOrFail($id);
        return [
            'entity'            => 'local_service',
            'id'                => $id,
            'entity_name'       => $item->item_name,
            'is_archived'       => (bool) $item->deleted_at,
            'force_delete_safe' => true,
            'cascade_deletable' => false,
        ];
    }
}
