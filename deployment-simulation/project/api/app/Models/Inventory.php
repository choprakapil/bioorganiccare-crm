<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Traits\ProtectedDeletion;

class Inventory extends Model
{
    use SoftDeletes, \App\Traits\BelongsToTenancy, ProtectedDeletion;

    protected $table = 'inventory';

    protected $fillable = [
        'catalog_id',
        'master_medicine_id',
        'item_name',
        'sku',
        'stock',
        'reorder_level',
        'purchase_cost',
        'sale_price',
        'is_selling'
    ];

    protected $casts = [
        'purchase_cost' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock' => 'integer',
        'reorder_level' => 'integer',
        'is_selling' => 'boolean'
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function catalog()
    {
        return $this->belongsTo(ClinicalCatalog::class, 'catalog_id');
    }

    public function master_medicine()
    {
        return $this->belongsTo(MasterMedicine::class, 'master_medicine_id')->withTrashed();
    }

    public function batches()
    {
        return $this->hasMany(\App\Models\InventoryBatch::class);
    }
}
