<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Traits\ProtectedDeletion;

class Treatment extends Model
{
    use SoftDeletes, \App\Traits\BelongsToTenancy, ProtectedDeletion;

    protected $fillable = [
        'patient_id',
        'catalog_id',
        'inventory_id',
        'procedure_name',
        'teeth',
        'notes',
        'status',
        'fee',
        'quantity',
        'invoice_id',
        'unit_cost',  // CRITICAL-3 FIX: Required for COGS calculation in InvoiceService
        'normalized_name',
    ];

    protected static function booted()
    {
        static::creating(function ($treatment) {
            // 1. Normalization & Catalog Auto-Link
            $treatment->procedure_name = trim($treatment->procedure_name);
            $treatment->normalized_name = strtolower(trim(preg_replace('/\s+/', ' ', $treatment->procedure_name)));
            
            if (!$treatment->catalog_id) {
                $catalogMatch = \Illuminate\Support\Facades\DB::table('clinical_catalog')
                    ->where('normalized_name', $treatment->normalized_name)
                    ->first();

                if ($catalogMatch) {
                    $treatment->catalog_id = $catalogMatch->id;
                }
            }

            // 2. Prevent Duplicate Treatments (5-minute window)
            $exists = \Illuminate\Support\Facades\DB::table('treatments')
                ->where('patient_id', $treatment->patient_id)
                ->where('procedure_name', $treatment->procedure_name)
                ->where('teeth', $treatment->teeth)
                ->where('quantity', $treatment->quantity)
                ->where('inventory_id', $treatment->inventory_id)
                ->where('created_at', '>=', now()->subMinutes(5))
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                throw new \Exception('Duplicate treatment detected within 5 minutes.');
            }
        });
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function catalog()
    {
        return $this->belongsTo(ClinicalCatalog::class, 'catalog_id');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id')->withTrashed();
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
