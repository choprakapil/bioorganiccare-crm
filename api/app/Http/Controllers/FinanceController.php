<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Enums\InvoiceStatus;
use Illuminate\Support\Facades\DB;

use App\Support\Context\TenantContext;

class FinanceController extends Controller
{
    private TenantContext $context;

    public function __construct(TenantContext $context)
    {
        $this->context = $context;
    }

    public function summary(Request $request)
    {
        // Authorization: Only Doctors see profit margins
        if (!$request->user()->hasRole('doctor')) {
            return response()->json(['message' => 'Unauthorized access to financial data'], 403);
        }

        $user = $this->context->getClinicOwner();
        $doctorId = $user->id;

        // Custom windows bypass cache for precision
        if ($request->has('start_date') || $request->has('end_date')) {
            $service = new \App\Services\AnalysisService();
            $summary = $service->calculateFinancialSummary($user, 90); // Logic for custom dates would go here
            return response()->json($summary);
        }

        // Default 90-day window uses Cache
        $cacheKey = "finance_summary_v2_{$doctorId}";
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached) {
            return response()->json($cached);
        }

        // Fallback to real-time computation
        $service = new \App\Services\AnalysisService();
        $summary = $service->calculateFinancialSummary($user);

        // Cache for 6 hours
        \Illuminate\Support\Facades\Cache::put($cacheKey, $summary, 21600);

        return response()->json($summary);
    }

    public function revenueTrend(Request $request)
    {
        // Authorization: Only Doctors see financial trends
        if (!$request->user()->hasRole('doctor')) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $days = (int) $request->get('days', 90);
        if (!in_array($days, [30, 90])) {
            $days = 90;
        }

        $user = $this->context->getClinicOwner();
        $doctorId = $user->id;
        $cacheKey = "finance:trend:{$doctorId}:{$days}";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($doctorId, $days) {
            $startDate = now()->subDays($days - 1)->startOfDay();
            
            // 1. Fetch Aggregated Data (O(1) Memory via Index-Only Scan)
            $rawPoints = DB::table('invoices')
                ->where('doctor_id', $doctorId)
                ->where('status', '!=', 'cancelled')
                ->where('created_at', '>=', $startDate)
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total_amount) as revenue')
                )
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'asc')
                ->get()
                ->pluck('revenue', 'date');

            // 2. Fill Time-Series Gaps
            $points = [];
            for ($i = 0; $i < $days; $i++) {
                $date = $startDate->copy()->addDays($i)->format('Y-m-d');
                $points[] = [
                    'date' => $date,
                    'revenue' => (float) ($rawPoints[$date] ?? 0)
                ];
            }

            return [
                'window_days' => $days,
                'points' => $points
            ];
        });
    }
}
