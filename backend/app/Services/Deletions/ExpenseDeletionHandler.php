<?php

namespace App\Services\Deletions;

use App\Models\Expense;

class ExpenseDeletionHandler implements DeletionHandlerInterface
{
    public function summary(int $id): array
    {
        $exp = Expense::findOrFail($id);
        return [
            'entity'            => 'expense',
            'id'                => $id,
            'entity_name'       => $exp->description ?? "Expense #{$id}",
            'force_delete_safe' => true,
            'cascade_deletable' => false,
        ];
    }
}
