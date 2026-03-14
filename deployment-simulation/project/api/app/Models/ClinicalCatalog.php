<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

use App\Models\Traits\ProtectedDeletion;

class ClinicalCatalog extends Model
{
    use SoftDeletes, ProtectedDeletion;

    protected $table = 'clinical_catalog';

    protected $fillable = [
        'specialty_id',
        'category_id',
        'item_name',
        'normalized_name',
        'type',
        'default_fee',
        'created_by_user_id',
        'approved_by_user_id',
        'approved_at',
    ];

    protected $casts = [
        'default_fee'  => 'decimal:2',
        'approved_at'  => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ClinicalServiceCategory::class, 'category_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function doctorSettings(): HasMany
    {
        return $this->hasMany(DoctorServiceSetting::class, 'catalog_id');
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class, 'catalog_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(CatalogAuditLog::class, 'entity_id')
            ->where('entity_type', 'clinical');
    }

    public function catalogVersions(): HasMany
    {
        return $this->hasMany(CatalogVersion::class, 'entity_id')
            ->where('entity_type', 'clinical');
    }

    /**
     * Centralized safety check: determines if the item is part of patient history.
     */
    public function hasUsage(): bool
    {
        return DB::table('treatments')
            ->where('catalog_id', $this->id)
            ->exists();
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
        static::deleting(function (ClinicalCatalog $item) {
            if ($item->isForceDeleting()) {
                return; // Force delete — no version row needed, entity is gone
            }

            // Increment version counter
            $newVersion = ($item->version ?? 0) + 1;
            $item->withoutEvents(function () use ($item, $newVersion) {
                $item->update(['version' => $newVersion]);
            });

            DB::table('catalog_versions')->insert([
                'entity_type'    => 'clinical',
                'entity_id'      => $item->id,
                'version_number' => $newVersion,
                'old_payload'    => json_encode($item->toArray()),
                'new_payload'    => json_encode(array_merge($item->toArray(), ['deleted_at' => now()->toDateTimeString()])),
                'created_at'     => now(),
            ]);
        });
    }
}
