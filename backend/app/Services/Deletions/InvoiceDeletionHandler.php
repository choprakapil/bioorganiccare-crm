<?php

namespace App\Services\Deletions;

use App\Models\Invoice;

class InvoiceDeletionHandler implements DeletionHandlerInterface
{
    public function summary(int $id): array
    {
        $inv = Invoice::findOrFail($id);
        return [
            'entity'            => 'invoice',
            'id'                => $id,
            'entity_name'       => "Invoice #{$id}",
            'is_finalized'      => (bool) $inv->is_finalized,
            'force_delete_safe' => !$inv->is_finalized,
            'cascade_deletable' => false,
        ];
    }
}
