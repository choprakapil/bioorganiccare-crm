<?php

namespace App\Services\Deletions;

use App\Models\ClinicalServiceCategory;
use App\Models\ClinicalCatalog;

class CategoryDeletionHandler implements DeletionHandlerInterface
{
    public function summary(int $id): array
    {
        $cat   = ClinicalServiceCategory::withTrashed()->findOrFail($id);
        $items = ClinicalCatalog::where('category_id', $id)->count();
        return [
            'entity'            => 'category',
            'id'                => $id,
            'entity_name'       => $cat->name,
            'is_archived'       => (bool) $cat->deleted_at,
            'services'          => $items,
            'force_delete_safe' => ($items === 0),
            'cascade_deletable' => false,
        ];
    }
}
