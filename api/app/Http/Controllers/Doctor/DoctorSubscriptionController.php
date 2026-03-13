<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionUsageService;
use Illuminate\Http\Request;

class DoctorSubscriptionController extends Controller
{
    protected $usageService;

    public function __construct(SubscriptionUsageService $usageService)
    {
        $this->usageService = $usageService;
    }

    /**
     * Get current doctor's subscription details.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        // 1. Role Guard
        if ($user->role !== 'doctor') {
            abort(403, 'Access denied. Only doctors can view subscription details.');
        }

        // 2. Load Plan
        $user->load('plan');

        // 3. Calculate Usage
        $usage = [
            'current_cycle_count' => $this->usageService->getCycleUsage($user),
            'remaining_quota' => $this->usageService->getRemainingQuota($user),
            'usage_percentage' => $this->usageService->getUsagePercentage($user),
            'is_blocked' => $this->usageService->isBlocked($user),
        ];

        // 4. Return Structure
        return response()->json([
            'doctor' => $user->only(['id', 'name', 'email', 'clinic_name']),
            'plan' => $user->plan ? $user->plan->only(['id', 'name', 'max_appointments_monthly']) : null,
            'lifecycle' => [
                'status' => $user->subscription_status,
                'interval' => $user->billing_interval,
                'started_at' => $user->subscription_started_at,
                'renews_at' => $user->subscription_renews_at,
                'grace_ends_at' => $user->subscription_grace_ends_at,
            ],
            'usage' => $usage
        ]);
    }
}
