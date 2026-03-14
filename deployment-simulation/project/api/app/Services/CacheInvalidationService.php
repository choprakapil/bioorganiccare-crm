<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Services\FinancialCacheService;

class CacheInvalidationService
{
    /**
     * Invalidate all clinical caches for a specific doctor.
     */
    public function invalidateClinic(int $doctorId): void
    {
        FinancialCacheService::forgetDoctorFinanceCache($doctorId);
        
        $keys = [
            "subscription_usage_{$doctorId}",
            "patient_stats_{$doctorId}",
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Specifically invalidate financial cache.
     */
    public function invalidateFinance(int $doctorId): void
    {
        FinancialCacheService::forgetDoctorFinanceCache($doctorId);
    }

    /**
     * Specifically invalidate subscription cache.
     */
    public function invalidateSubscription(int $doctorId): void
    {
        Cache::forget("subscription_usage_{$doctorId}");
    }
}
