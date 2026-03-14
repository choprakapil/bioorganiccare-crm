<?php

namespace App\Services;

use App\Models\User;
use App\Models\Appointment;

class SubscriptionUsageService
{
    /**
     * Calculate current cycle usage for a doctor.
     * 
     * Returns the count of appointments created within the current billing window.
     * Returns 0 for non-doctors or uninitialized subscriptions.
     * Counts usage even for lifetime plans (for analytics).
     */
    public function getCycleUsage(User $doctor): int
    {
        // Safety: Only doctors have cycle usage
        if ($doctor->role !== 'doctor') {
            return 0;
        }

        // Safety: Uninitialized subscription
        if (is_null($doctor->subscription_started_at)) {
            return 0;
        }

        $query = Appointment::where('doctor_id', $doctor->id);
        
        // Strict Window Logic: Start Inclusive, End Exclusive
        // [Start, End) prevents overlap if renewal happens at exact second
        $query->where('created_at', '>=', $doctor->subscription_started_at);

        if ($doctor->subscription_renews_at) {
            $query->where('created_at', '<', $doctor->subscription_renews_at);
        }

        return $query->count();
    }

    /**
     * Calculate remaining appointments allowed in this cycle.
     * 
     * Returns null if usage is unlimited (-1) or no plan is assigned.
     */
    public function getRemainingQuota(User $doctor): ?int
    {
        // Defensive Loading: Use loaded relation if available to avoid N+1
        $plan = $doctor->relationLoaded('plan') ? $doctor->plan : $doctor->plan()->first();

        if (!$plan) {
            return null;
        }

        $limit = $plan->max_appointments_monthly;

        // Unlimited Plan
        if ($plan->isUnlimited('max_appointments_monthly')) {
            return null;
        }

        $usage = $this->getCycleUsage($doctor);

        return max(0, $limit - $usage);
    }

    /**
     * Calculate usage percentage for this cycle.
     * 
     * Returns null if usage is unlimited (-1) or no plan is assigned.
     * Returns 100.0 if limit is 0.
     */
    public function getUsagePercentage(User $doctor): ?float
    {
        // Defensive Loading
        $plan = $doctor->relationLoaded('plan') ? $doctor->plan : $doctor->plan()->first();

        if (!$plan) {
            return null;
        }

        $limit = $plan->max_appointments_monthly;

        // Unlimited Plan
        if ($plan->isUnlimited('max_appointments_monthly')) {
            return null;
        }

        // Prevent Division by Zero (Zero Limit = 100% Usage effectively)
        if ($limit === 0) {
            return 100.0;
        }

        $usage = $this->getCycleUsage($doctor);

        return round(($usage / $limit) * 100, 1);
    }

    /**
     * Determine if the doctor is currently blocked from write operations.
     * 
     * Based on subscription status and grace period.
     */
    public function isBlocked(User $doctor): bool
    {
        $status = $doctor->subscription_status;

        // Lifetime Users are NEVER blocked based on subscription status
        if ($status === 'lifetime') {
            return false;
        }

        // Hard Block States
        if (in_array($status, ['expired', 'cancelled'])) {
            return true;
        }

        // Grace Period Logic
        if ($status === 'past_due') {
            // Block if grace period is not defined or has passed
            // Strict check: if no date set, assume immediate block
            if (!$doctor->subscription_grace_ends_at || now()->gt($doctor->subscription_grace_ends_at)) {
                return true;
            }
        }

        return false;
    }
}
