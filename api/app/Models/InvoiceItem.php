<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'inventory_id',
        'catalog_version_snapshot',
        'name',
        'type', // Procedure, Medicine
        'quantity',
        'unit_price',
        'unit_cost',
        'fee',
        'teeth'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
