<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryBatch extends Model
{
    use \App\Traits\BelongsToTenancy;

    protected $fillable = [
        'inventory_id',
        'original_quantity',
        'quantity_remaining',
        'unit_cost',
        'purchase_reference',
        'batch_type',           // 'replenishment' | 'adjustment' | 'initial'
        'adjustment_reason',    // Required for adjustments
    ];


    protected $casts = [
        'unit_cost' => 'decimal:4',
        'original_quantity' => 'integer',
        'quantity_remaining' => 'integer'
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
