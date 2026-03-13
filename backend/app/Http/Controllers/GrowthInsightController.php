<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Enums\InvoiceStatus;


use App\Support\Context\TenantContext;

class GrowthInsightController extends Controller
{
    protected TenantContext $context;

    public function __construct(TenantContext $context)
    {
        $this->context = $context;
    }

    public function index()
    {
        $user = $this->context->getClinicOwner();
        
        if (!$user) {
            return response()->json(['message' => 'Clinic context required.'], 422);
        }
        $cacheKey = "growth_insights_{$user->id}";

        $cachedData = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cachedData) {
            return response()->json($cachedData);
        }

        $service = new \App\Services\AnalysisService();
        $data = $service->calculateGrowthInsights($user);

        // Cache for 1 hour
        \Illuminate\Support\Facades\Cache::put($cacheKey, $data, 3600);

        return response()->json($data);
    }
}
