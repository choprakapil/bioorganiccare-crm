<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class FinancialCacheService
{
    public static function forgetDoctorFinanceCache(int $doctorId): void
    {
        Cache::forget("finance_summary_v2_{$doctorId}");
        Cache::forget("growth_insights_{$doctorId}");
    }
}
