<?php

namespace App\Services;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Treatment;
use App\Models\AuditLog;
use App\Enums\InvoiceStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AnalysisService
{
    /**
     * Calculate growth insights for a specific doctor.
     */
    public function calculateGrowthInsights(User $doctor): array
    {
        $doctorId = $doctor->id;
        $now = Carbon::now();
        $thirtyDaysAgo = $now->copy()->subDays(30);

        $insights = [];

        // 1. Revenue Insights
        $totalRev = Invoice::where('doctor_id', $doctorId)
            ->where('status', InvoiceStatus::PAID)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->sum('total_amount');

        $pendingRev = Invoice::where('doctor_id', $doctorId)
            ->whereIn('status', [InvoiceStatus::UNPAID, InvoiceStatus::PARTIAL])
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->sum('balance_due');

        if ($pendingRev > ($totalRev * 0.5)) {
            $insights[] = [
                'type'        => 'revenue',
                'priority'    => 'high',
                'title'       => 'Liquidity Risk Detected',
                'description' => "Your 30-day outstanding dues (₹" . number_format($pendingRev) . ") exceed 50% of 30-day revenue. Consider a deposit-first policy for high-value treatments."
            ];
        }

        // 2. Inventory Insights
        $lowStockItems = Inventory::where('doctor_id', $doctorId)
            ->whereRaw('stock <= reorder_level')
            ->count();
            
        $totalItems = Inventory::where('doctor_id', $doctorId)->count();

        if ($lowStockItems > 0 && $totalItems > 0) {
            $percent = ($lowStockItems / $totalItems) * 100;
            if ($percent > 20) {
                $insights[] = [
                    'type' => 'inventory',
                    'priority' => 'medium',
                    'title' => 'Inventory Bottleneck',
                    'description' => "{$lowStockItems} of your SKU items are at critical levels. This may cause procedural delays."
                ];
            }
        }

        // 3. Efficiency Insights
        $topProcedures = Treatment::where('doctor_id', $doctorId)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->select('procedure_name', DB::raw('count(*) as count'))
            ->groupBy('procedure_name')
            ->orderBy('count', 'desc')
            ->limit(3)
            ->get();

        if ($topProcedures->isNotEmpty()) {
            $top = $topProcedures->first();
            $insights[] = [
                'type' => 'efficiency',
                'priority' => 'low',
                'title' => 'Specialization Opportunity',
                'description' => "'{$top->procedure_name}' is your most frequent service this month."
            ];
        }

        // 4. Expense Pattern
        $monthlyExp = Expense::where('doctor_id', $doctorId)
            ->where('expense_date', '>=', $thirtyDaysAgo)
            ->sum('amount');
            
        if ($monthlyExp > ($totalRev * 0.4) && $totalRev > 0) {
             $insights[] = [
                'type' => 'operations',
                'priority' => 'medium',
                'title' => 'High Operational Burn',
                'description' => "Your overhead is consuming " . round(($monthlyExp/$totalRev)*100) . "% of revenue."
            ];
        }

        return [
            'status' => 'success',
            'generated_at' => $now->toDateTimeString(),
            'insights' => $insights
        ];
    }

    /**
     * Calculate financial summary for a 90-day window with comparative analysis.
     * Stripe-grade contract with O(1) Memory and Circuit Breaker.
     */
    public function calculateFinancialSummary(User $doctor, int $days = 90): array
    {
        $cacheKey = "finance_summary_v2_{$doctor->id}";

        try {
            return retry(2, function() use ($doctor, $days) {
                $doctorId = $doctor->id;
                
                // Windows
                $now = now();
                $curStart = $now->copy()->subDays($days);
                $prevStart = $now->copy()->subDays($days * 2);
                $prevEnd = $curStart->copy()->subSecond();

                // Dual-window conditional aggregation (3 queries instead of 6)
                $metrics = $this->getDualWindowMetrics($doctorId, $curStart, $now, $prevStart, $prevEnd);
                $current = $metrics['current'];
                $previous = $metrics['previous'];

                // Inventory (Snapshot) — always global, no window
                $inventoryValue = $metrics['inventory_value'];

                return [
                    'window' => [
                        'start' => $curStart->toDateString(),
                        'end'   => $now->toDateString(),
                        'days'  => $days
                    ],
                    'previous_window' => [
                        'start' => $prevStart->toDateString(),
                        'end'   => $prevEnd->toDateString(),
                    ],
                    'metrics' => [
                        'revenue' => $this->formatMetric($current['revenue'], $previous['revenue']),
                        'cash_collected' => $this->formatMetric($current['cash'], $previous['cash']),
                        'outstanding_balance' => $this->formatMetric($current['pending'], $previous['pending']),
                        'net_profit' => $this->formatMetric($current['profit'], $previous['profit']),
                        'inventory_value' => (float)$inventoryValue
                    ],
                    'is_fallback' => false,
                ];
            }, 50);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Analytics Circuit Breaker Triggered for Doctor #{$doctor->id}: " . $e->getMessage());
            
            $lastKnown = Cache::get($cacheKey);
            if ($lastKnown) {
                $lastKnown['is_fallback'] = true;
                $lastKnown['stale_data'] = true;
                return $lastKnown;
            }

            return [
                'metrics' => [
                    'revenue' => ['current' => 0, 'previous' => 0, 'change_percent' => 0],
                    'cash_collected' => ['current' => 0, 'previous' => 0, 'change_percent' => 0],
                    'outstanding_balance' => ['current' => 0, 'previous' => 0, 'change_percent' => 0],
                    'net_profit' => ['current' => 0, 'previous' => 0, 'change_percent' => 0],
                    'inventory_value' => 0
                ],
                'is_fallback' => true,
                'error' => 'High system load.'
            ];
        }
    }

    private function getRawMetrics($doctorId, $start, $end)
    {
        $invoices = DB::table('invoices')
            ->where('doctor_id', $doctorId)
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('SUM(paid_amount) as cash'),
                DB::raw('SUM(balance_due) as pending')
            )->first();

        $cogs = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.doctor_id', $doctorId)
            ->whereBetween('invoices.created_at', [$start, $end])
            ->where('invoice_items.type', 'Medicine')
            ->sum(DB::raw('invoice_items.quantity * invoice_items.unit_cost'));

        $expenses = DB::table('expenses')
            ->where('doctor_id', $doctorId)
            ->whereBetween('expense_date', [$start, $end])
            ->sum('amount');

        $rev = (float)($invoices->revenue ?? 0);
        return [
            'revenue' => $rev,
            'cash'    => (float)($invoices->cash ?? 0),
            'pending' => (float)($invoices->pending ?? 0),
            'profit'  => $rev - (float)$cogs - (float)$expenses
        ];
    }

    /**
     * Compute both current and previous window metrics in 4 queries instead of 7.
     * Uses CASE WHEN conditional aggregation to merge dual-window passes.
     */
    private function getDualWindowMetrics($doctorId, $curStart, $curEnd, $prevStart, $prevEnd)
    {
        // Q1: Invoice aggregates for both windows in one pass
        $invoices = DB::table('invoices')
            ->where('doctor_id', $doctorId)
            ->where('created_at', '>=', $prevStart)
            ->where('created_at', '<=', $curEnd)
            ->select(
                DB::raw("SUM(CASE WHEN created_at >= ? AND created_at <= ? THEN total_amount ELSE 0 END) as cur_revenue"),
                DB::raw("SUM(CASE WHEN created_at >= ? AND created_at <= ? THEN paid_amount ELSE 0 END) as cur_cash"),
                DB::raw("SUM(CASE WHEN created_at >= ? AND created_at <= ? THEN balance_due ELSE 0 END) as cur_pending"),
                DB::raw("SUM(CASE WHEN created_at >= ? AND created_at <= ? THEN total_amount ELSE 0 END) as prev_revenue"),
                DB::raw("SUM(CASE WHEN created_at >= ? AND created_at <= ? THEN paid_amount ELSE 0 END) as prev_cash"),
                DB::raw("SUM(CASE WHEN created_at >= ? AND created_at <= ? THEN balance_due ELSE 0 END) as prev_pending")
            )
            ->addBinding([$curStart, $curEnd], 'select')
            ->addBinding([$curStart, $curEnd], 'select')
            ->addBinding([$curStart, $curEnd], 'select')
            ->addBinding([$prevStart, $prevEnd], 'select')
            ->addBinding([$prevStart, $prevEnd], 'select')
            ->addBinding([$prevStart, $prevEnd], 'select')
            ->first();

        // Q2: COGS for both windows in one pass
        $cogs = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.doctor_id', $doctorId)
            ->where('invoices.created_at', '>=', $prevStart)
            ->where('invoices.created_at', '<=', $curEnd)
            ->where('invoice_items.type', 'Medicine')
            ->select(
                DB::raw("SUM(CASE WHEN invoices.created_at >= ? AND invoices.created_at <= ? THEN invoice_items.quantity * invoice_items.unit_cost ELSE 0 END) as cur_cogs"),
                DB::raw("SUM(CASE WHEN invoices.created_at >= ? AND invoices.created_at <= ? THEN invoice_items.quantity * invoice_items.unit_cost ELSE 0 END) as prev_cogs")
            )
            ->addBinding([$curStart, $curEnd], 'select')
            ->addBinding([$prevStart, $prevEnd], 'select')
            ->first();

        // Q3: Expenses for both windows in one pass
        $expenses = DB::table('expenses')
            ->where('doctor_id', $doctorId)
            ->where('expense_date', '>=', $prevStart)
            ->where('expense_date', '<=', $curEnd)
            ->select(
                DB::raw("SUM(CASE WHEN expense_date >= ? AND expense_date <= ? THEN amount ELSE 0 END) as cur_expenses"),
                DB::raw("SUM(CASE WHEN expense_date >= ? AND expense_date <= ? THEN amount ELSE 0 END) as prev_expenses")
            )
            ->addBinding([$curStart, $curEnd], 'select')
            ->addBinding([$prevStart, $prevEnd], 'select')
            ->first();

        // Q4: Inventory value (global snapshot, no window)
        $inventoryValue = DB::table('inventory_batches')
            ->where('doctor_id', $doctorId)
            ->sum(DB::raw('quantity_remaining * unit_cost'));

        $curRevenue  = (float)($invoices->cur_revenue ?? 0);
        $prevRevenue = (float)($invoices->prev_revenue ?? 0);
        $curCogs     = (float)($cogs->cur_cogs ?? 0);
        $prevCogs    = (float)($cogs->prev_cogs ?? 0);
        $curExp      = (float)($expenses->cur_expenses ?? 0);
        $prevExp     = (float)($expenses->prev_expenses ?? 0);

        return [
            'current' => [
                'revenue' => $curRevenue,
                'cash'    => (float)($invoices->cur_cash ?? 0),
                'pending' => (float)($invoices->cur_pending ?? 0),
                'profit'  => $curRevenue - $curCogs - $curExp,
            ],
            'previous' => [
                'revenue' => $prevRevenue,
                'cash'    => (float)($invoices->prev_cash ?? 0),
                'pending' => (float)($invoices->prev_pending ?? 0),
                'profit'  => $prevRevenue - $prevCogs - $prevExp,
            ],
            'inventory_value' => (float)$inventoryValue,
        ];
    }

    private function formatMetric($current, $previous)
    {
        $change = 0;
        if ($previous > 0) {
            $change = (($current - $previous) / $previous) * 100;
        } elseif ($current > 0) {
            $change = 100;
        }

        return [
            'current' => round($current, 2),
            'previous' => round($previous, 2),
            'change_percent' => round($change, 2)
        ];
    }
}
