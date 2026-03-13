<?php

namespace App\Services\Deletions;

use App\Models\Inventory;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;

class InventoryDeletionHandler implements DeletionHandlerInterface
{
    public function summary(int $id): array
    {
        $item  = Inventory::withTrashed()->findOrFail($id);
        $hasBillingHistory = InvoiceItem::where('inventory_id', $id)->exists();
        $usage = DB::table('invoice_item_batch_allocations')->where('inventory_id', $id)->count();
        
        return [
            'entity'            => 'inventory',
            'id'                => $id,
            'entity_name'       => $item->item_name ?? "Batch #{$id}",
            'is_archived'       => (bool) $item->deleted_at,
            'billing_history_rows' => $hasBillingHistory ? 1 : 0,
            'batch_allocations' => $usage,
            'force_delete_safe' => (!$hasBillingHistory && $usage === 0),
            'cascade_deletable' => false,
        ];
    }
}
