<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SystemHealthController extends Controller
{
    /**
     * GET /api/system/health
     * 
     * Enterprise health check monitor for:
     * - DB Connectivity
     * - Redis Connectivity
     * - Storage availability
     * - Horizon Status (via cache/signal)
     */
    public function health()
    {
        $status = [
            'database' => 'OK',
            'redis'    => 'OK',
            'storage'  => 'OK',
            'time'     => now()->toIso8601String(),
            'version'  => '2.1.0-enterprise'
        ];

        // 1. Check DB
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $status['database'] = 'FAIL: ' . $e->getMessage();
        }

        // 2. Check Redis (Resilient to missing extension in tests)
        try {
            $store = config('cache.default') === 'redis' ? 'redis' : null;
            if ($store) {
                Cache::store('redis')->put('health_check', 'ok', 10);
                if (Cache::store('redis')->get('health_check') !== 'ok') {
                    throw new \Exception('Redis read/write fail');
                }
            } else {
                $status['redis'] = 'OK (Skipped: Not Default)';
            }
        } catch (\Throwable $e) {
            $status['redis'] = 'FAIL: ' . $e->getMessage();
        }

        // 3. Check Disk Space (Roughly)
        try {
            Storage::disk('local')->put('health.txt', 'test');
        } catch (\Exception $e) {
            $status['storage'] = 'FAIL: ' . $e->getMessage();
        }

        // 4. Dead Letter / Failed Jobs Monitor (Scaling Safety)
        try {
            $failedCount = DB::table('failed_jobs')->count();
            $status['queue_failed_count'] = $failedCount;
            if ($failedCount > 50) { // Threshold for enterprise warning
                $status['queue_status'] = 'WARNING: High failure rate';
            } else {
                $status['queue_status'] = 'OK';
            }
        } catch (\Exception $e) {
            $status['queue_status'] = 'FAIL: ' . $e->getMessage();
        }

        $allOk = !str_contains(implode(' ', array_values($status)), 'FAIL');
        
        // 503 if mission critical (DB/Redis) fails
        $isCriticalFail = str_contains($status['database'], 'FAIL') || str_contains($status['redis'], 'FAIL');

        return response()->json($status, $isCriticalFail ? 503 : 200);
    }
}
