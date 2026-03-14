<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ModuleCacheService
 *
 * Centralises all cache invalidation logic for the doctor_modules_* key space.
 *
 * The TenantContext cache key format is:
 *   doctor_modules_{doctorId}_{specialtyId}_{specialtyUpdatedAt}_{planUpdatedAt}_{schemaVersion}
 *
 * Because the schema version (saas_module_schema_version) is part of the key,
 * bumping it via bumpSchemaVersion() causes the next request to compute a new key
 * and miss the cache — effectively instant invalidation without needing to know
 * the full key upfront.
 *
 * Individual doctor eviction is used when only one doctor changes (e.g., plan reassignment).
 * Global schema bump is used when a pivot table changes (e.g., specialty module sync).
 */
class ModuleCacheService
{
    /**
     * Bump the global schema version epoch.
     * This makes all existing doctor_modules_* cache keys stale instantly,
     * because the key suffix changes.
     *
     * Use when: specialty module pivot changed, plan module pivot changed,
     *           specialty archived/restored.
     */
    public static function bumpSchemaVersion(): void
    {
        Cache::forever('saas_module_schema_version', time());
        Log::info('[ModuleCache] Global schema version bumped — all tenant caches invalidated.');
    }

    /**
     * Invalidate cache for all doctors on a specific plan.
     * Used when a plan's properties or modules change.
     *
     * Instead of guessing the full cache key, we bump the global schema version.
     * This is semantically correct: plan module changes affect ALL doctors on that plan.
     *
     * @param int $planId
     */
    public static function invalidateByPlan(int $planId): void
    {
        // Bump global schema version — affects all doctors on this plan
        // (and all others, but that's safe since their keys also recompute)
        $affectedCount = User::where('plan_id', $planId)->where('role', 'doctor')->count();
        Log::info("[ModuleCache] Plan #{$planId} changed — {$affectedCount} doctor(s) affected. Bumping schema version.");
        self::bumpSchemaVersion();
    }

    /**
     * Invalidate cache for all doctors on a specific specialty.
     * Used when specialty module pivot changes.
     *
     * @param int $specialtyId
     */
    public static function invalidateBySpecialty(int $specialtyId): void
    {
        $affectedCount = User::where('specialty_id', $specialtyId)->where('role', 'doctor')->count();
        Log::info("[ModuleCache] Specialty #{$specialtyId} changed — {$affectedCount} doctor(s) affected. Bumping schema version.");
        self::bumpSchemaVersion();
    }

    /**
     * Invalidate cache for a single doctor.
     * Used when doctor's plan_id or specialty_id changes.
     *
     * Because we don't know the exact key suffix (timestamps, schema version),
     * we bump the global schema version. This is the least disruptive approach
     * that guarantees correctness without a key scan.
     *
     * @param int $doctorId
     */
    public static function invalidateByDoctor(int $doctorId): void
    {
        Log::info("[ModuleCache] Doctor #{$doctorId} plan/specialty changed. Bumping schema version.");
        self::bumpSchemaVersion();
    }
}
