<?php

namespace App\Services\Deletions;

use App\Models\ClinicalCatalog;

class ServiceDeletionHandler implements DeletionHandlerInterface
{
    public function summary(int $id): array
    {
        $item  = ClinicalCatalog::withTrashed()->findOrFail($id);
        $inUse = $item->hasUsage() ? 1 : 0;
        return [
            'entity'            => 'service',
            'id'                => $id,
            'entity_name'       => $item->item_name,
            'is_archived'       => (bool) $item->deleted_at,
            'treatments'        => $inUse,
            'force_delete_safe' => ($inUse === 0),
            'cascade_deletable' => false,
        ];
    }
}
