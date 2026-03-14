<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

use App\Models\Traits\ProtectedDeletion;

class MasterMedicine extends Model
{
    use HasFactory, SoftDeletes, ProtectedDeletion;

    protected $fillable = [
        'name',
        'specialty_id',
        'pharmacy_category_id',
        'category',
        'unit',
        'default_purchase_price',
        'default_selling_price',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_purchase_price' => 'decimal:2',
        'default_selling_price' => 'decimal:2',
    ];

    public function inventory()
    {
        return $this->hasMany(Inventory::class, 'master_medicine_id');
    }

    public function pharmacy_category()
    {
        return $this->belongsTo(PharmacyCategory::class, 'pharmacy_category_id');
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    // ── Archive Version Snapshot (Phase 3) ────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($model) {
            if (!empty($model->item_name ?? $model->name)) {
                $source = $model->item_name ?? $model->name;
                $normalized = strtolower(trim($source));
                $normalized = preg_replace('/\s+/', ' ', $normalized);
                $model->normalized_name = $normalized;
            }
        });

        // On soft-delete: write an archive snapshot to catalog_versions
        static::deleting(function (MasterMedicine $medicine) {
            if ($medicine->isForceDeleting()) {
                return; // Force delete — no version row needed
            }

            $newVersion = ($medicine->version ?? 0) + 1;
            $medicine->withoutEvents(function () use ($medicine, $newVersion) {
                $medicine->update(['version' => $newVersion]);
            });

            DB::table('catalog_versions')->insert([
                'entity_type'    => 'pharmacy',
                'entity_id'      => $medicine->id,
                'version_number' => $newVersion,
                'old_payload'    => json_encode($medicine->toArray()),
                'new_payload'    => json_encode(array_merge($medicine->toArray(), ['deleted_at' => now()->toDateTimeString()])),
                'created_at'     => now(),
            ]);
        });
    }
}
