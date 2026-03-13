<?php

namespace App\Services\Deletions;

use App\Models\User;

class StaffDeletionHandler implements DeletionHandlerInterface
{
    public function summary(int $id): array
    {
        $staff = User::withTrashed()->where('role', 'staff')->findOrFail($id);
        return [
            'entity'            => 'staff',
            'id'                => $id,
            'entity_name'       => $staff->name,
            'is_archived'       => (bool) $staff->deleted_at,
            'force_delete_safe' => true,
            'cascade_deletable' => false,
        ];
    }
}
