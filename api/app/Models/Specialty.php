<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ModuleCacheService;

use App\Models\Traits\ProtectedDeletion;

class Specialty extends Model
{
    use SoftDeletes, ProtectedDeletion;

    protected $fillable = [
        'name',
        'slug',
        'features',
        'capabilities',
        'is_active',
        'has_teeth_chart', // Deprecated but kept for now
        'has_eye_chart'    // Deprecated but kept for now
    ];

    protected $casts = [
        'features'        => 'array',
        'capabilities'    => 'array',
        'is_active'       => 'boolean',
        'has_teeth_chart' => 'boolean',
        'has_eye_chart'   => 'boolean',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────────────────

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'specialty_module')
            ->withPivot('enabled')
            ->withTimestamps();
    }

    public function catalog()
    {
        return $this->hasMany(ClinicalCatalog::class);
    }

    public function categories()
    {
        return $this->hasMany(ClinicalServiceCategory::class);
    }

    public function pharmacyCategories()
    {
        return $this->hasMany(PharmacyCategory::class);
    }

    public function masterMedicines()
    {
        return $this->hasMany(MasterMedicine::class);
    }

    public function serviceSubmissions()
    {
        return $this->hasMany(ServiceSubmission::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LIFECYCLE — MODEL-DRIVEN CASCADE (Enterprise Phase 2)
    // ─────────────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (Specialty $specialty) {
            $isForce = $specialty->isForceDeleting();

            Log::info("[Specialty] " . ($isForce ? 'FORCE' : 'SOFT') . " delete triggered for specialty #{$specialty->id} ({$specialty->name})");

            // ── Phase 5: Bust all module caches BEFORE cascading children ──────
            // Any doctor on this specialty will get a fresh key on next request.
            ModuleCacheService::bumpSchemaVersion();

            if ($isForce) {
                static::cascadeForceDelete($specialty);
            } else {
                static::cascadeSoftDelete($specialty);
            }
        });
    }

    /**
     * SOFT DELETE CASCADE
     *
     * Archives all children through Eloquent so:
     *   - deleted_at is set on each child row
     *   - Each child's own 'deleting' event fires (triggers version snapshots, audit events)
     *   - PharmacyCategory (no SoftDeletes) is skipped if medicines exist
     *
     * Called when: $specialty->delete() (soft delete from controller)
     */
    private static function cascadeSoftDelete(Specialty $specialty): void
    {
        // 1. Clinical Catalog — soft delete each item individually (fires ClinicalCatalog events)
        ClinicalCatalog::where('specialty_id', $specialty->id)
            ->whereNull('deleted_at')
            ->each(fn($item) => $item->delete());

        // 2. Clinical Service Categories
        ClinicalServiceCategory::where('specialty_id', $specialty->id)
            ->whereNull('deleted_at')
            ->each(fn($cat) => $cat->delete());

        // 3. PharmacyCategory — NO SoftDeletes trait.
        //    Only hard-delete if empty (controller guard already enforced this at route level,
        //    but model guard is a safety net for programmatic calls).
        PharmacyCategory::where('specialty_id', $specialty->id)
            ->each(function ($cat) {
                if ($cat->medicines()->count() === 0) {
                    $cat->delete();
                } else {
                    Log::warning("[Specialty] Skipping PharmacyCategory #{$cat->id} — medicines exist. Must clear manually.");
                }
            });

        // 4. MasterMedicine — soft delete each (has SoftDeletes, events fire per record)
        MasterMedicine::where('specialty_id', $specialty->id)
            ->whereNull('deleted_at')
            ->each(fn($med) => $med->delete());

        // 5. ServiceSubmissions — DB nullOnDelete handles the FK.
        //    Submissions are audit records — preserved with specialty_id = null.

        // 6. specialty_module pivot — no model exists, bulk delete is correct
        DB::table('specialty_module')
            ->where('specialty_id', $specialty->id)
            ->delete();

        Log::info("[Specialty] Soft cascade complete for specialty #{$specialty->id}");
    }

    /**
     * FORCE DELETE CASCADE
     *
     * Only reachable AFTER SpecialtyController::forceDelete() dependency guard
     * has returned 409 if any dependencies remain. This is the final cleanup.
     * Handles already-soft-deleted children (withTrashed) that passed the guard.
     *
     * Called when: $specialty->forceDelete()
     */
    private static function cascadeForceDelete(Specialty $specialty): void
    {
        // 1. Clinical Catalog — force delete all (including previously soft-deleted)
        ClinicalCatalog::withTrashed()
            ->where('specialty_id', $specialty->id)
            ->each(fn($item) => $item->forceDelete());

        // 2. Clinical Service Categories
        ClinicalServiceCategory::withTrashed()
            ->where('specialty_id', $specialty->id)
            ->each(fn($cat) => $cat->forceDelete());

        // 3. PharmacyCategory (no SoftDeletes — hard delete)
        PharmacyCategory::where('specialty_id', $specialty->id)
            ->each(fn($cat) => $cat->delete());

        // 4. MasterMedicine — force delete all including soft-deleted
        MasterMedicine::withTrashed()
            ->where('specialty_id', $specialty->id)
            ->each(fn($med) => $med->forceDelete());

        // 5. specialty_module pivot
        DB::table('specialty_module')
            ->where('specialty_id', $specialty->id)
            ->delete();

        Log::info("[Specialty] Force cascade complete for specialty #{$specialty->id}");
    }
}
