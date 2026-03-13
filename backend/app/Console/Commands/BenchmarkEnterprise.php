<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\AnalysisService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class BenchmarkEnterprise extends Command
{
    protected $signature = 'benchmark:enterprise {doctor_id}';
    protected $description = 'Benchmark the system with 10k invoices';

    public function handle(AnalysisService $service)
    {
        $doctorId = $this->argument('doctor_id');
        $doctor = User::findOrFail($doctorId);

        $this->info("=== Benchmarking Doctor ID: {$doctorId} ===");

        // 1. Raw Performance (Cache Miss)
        Cache::forget("finance_summary_{$doctorId}");
        Cache::forget("growth_insights_{$doctorId}");

        $start = microtime(true);
        $memStart = memory_get_usage();
        
        $summary = $service->calculateFinancialSummary($doctor);
        
        $time = (microtime(true) - $start) * 1000;
        $memUsed = (memory_get_usage() - $memStart) / 1024;

        $this->info("Finance Summary (Real-time):");
        $this->line(" - Time: " . round($time, 2) . "ms");
        $this->line(" - Memory: " . round($memUsed, 2) . "KB");

        // 2. Cached Performance (Phase 2 benefit)
        Cache::put("finance_summary_{$doctorId}", $summary, 3600);
        $start = microtime(true);
        Cache::get("finance_summary_{$doctorId}");
        $timeCached = (microtime(true) - $start) * 1000;
        $this->info("Finance Summary (Cached): " . round($timeCached, 4) . "ms");

        // 3. Growth Insights
        $start = microtime(true);
        $service->calculateGrowthInsights($doctor);
        $timeInsights = (microtime(true) - $start) * 1000;
        $this->info("Growth Insights (Real-time): " . round($timeInsights, 2) . "ms");

        // 4. Query Count
        DB::enableQueryLog();
        $service->calculateFinancialSummary($doctor);
        $queryCount = count(DB::getQueryLog());
        $this->info("Total Queries for Finance: {$queryCount}");
        
        DB::flushQueryLog();
        $service->calculateGrowthInsights($doctor);
        $queryCountInsights = count(DB::getQueryLog());
        $this->info("Total Queries for Insights: {$queryCountInsights}");

        $this->info("=================================");
    }
}
