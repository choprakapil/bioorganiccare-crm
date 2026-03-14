<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItemBatchAllocation extends Model
{
    protected $fillable = [
        'invoice_item_id',
        'inventory_batch_id',
        'quantity_taken',
        'unit_cost'
    ];

    protected $casts = [
        'unit_cost' => 'decimal:4',
        'quantity_taken' => 'integer'
    ];

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }
}
