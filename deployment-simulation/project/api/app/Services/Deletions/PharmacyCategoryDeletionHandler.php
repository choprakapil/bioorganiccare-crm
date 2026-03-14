<?php

namespace App\Services\Deletions;

use App\Models\PharmacyCategory;
use App\Models\MasterMedicine;

class PharmacyCategoryDeletionHandler implements DeletionHandlerInterface
{
    public function summary(int $id): array
    {
        $cat   = PharmacyCategory::findOrFail($id);
        $items = MasterMedicine::where('pharmacy_category_id', $id)->count();
        return [
            'entity'            => 'pharmacy_category',
            'id'                => $id,
            'entity_name'       => $cat->name,
            'medicines'         => $items,
            'force_delete_safe' => ($items === 0),
            'cascade_deletable' => false,
        ];
    }
}
