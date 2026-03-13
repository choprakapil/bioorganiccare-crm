<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use App\Services\SubscriptionUsageService;
use App\Events\DoctorPlanUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminSubscriptionController extends Controller
{
    protected $usageService;

    public function __construct(SubscriptionUsageService $usageService)
    {
        $this->usageService = $usageService;
    }

    /**
     * List all doctors with subscription summary.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'doctor')
            ->with('plan:id,name') // Eager load plan name
            ->select([
                'id', 
                'name', 
                'email', 
                'clinic_name', 
                'plan_id',
                'subscription_status',
                'billing_interval',
                'subscription_started_at',
                'subscription_renews_at',
                'subscription_grace_ends_at'
            ]);

        // Optional filtering
        if ($request->has('status')) {
            $query->where('subscription_status', $request->status);
        }

        $doctors = $query->paginate(20);

        return response()->json($doctors);
    }

    /**
     * Get detailed subscription metrics for a specific doctor.
     */
    public function show(User $doctor)
    {
        if ($doctor->role !== 'doctor') {
            abort(404, 'User is not a doctor.');
        }

        $doctor->load('plan');

        $detail = [
            'doctor' => $doctor->only(['id', 'name', 'email', 'clinic_name', 'phone']),
            'plan' => $doctor->plan,
            'lifecycle' => [
                'status' => $doctor->subscription_status,
                'interval' => $doctor->billing_interval,
                'started_at' => $doctor->subscription_started_at,
                'renews_at' => $doctor->subscription_renews_at,
                'grace_ends_at' => $doctor->subscription_grace_ends_at,
            ],
            'usage' => [
                'current_cycle_count' => $this->usageService->getCycleUsage($doctor),
                'remaining_quota' => $this->usageService->getRemainingQuota($doctor),
                'usage_percentage' => $this->usageService->getUsagePercentage($doctor),
                'is_blocked' => $this->usageService->isBlocked($doctor),
            ]
        ];

        return response()->json($detail);
    }

    /**
     * Renew subscription (Extensions).
     * Shifts the billing window forward.
     */
    public function renew(User $doctor)
    {
        if ($doctor->role !== 'doctor') abort(404, 'User is not a doctor.');

        return DB::transaction(function () use ($doctor) {
            $now = now();
            
            // Safety: Determine where the new cycle should start (extension base)
            $currentRenewsAt = $doctor->subscription_renews_at ? Carbon::parse($doctor->subscription_renews_at) : null;
            $extensionBase = ($currentRenewsAt && $currentRenewsAt->isFuture()) 
                ? $currentRenewsAt 
                : $now;

            // Determine Duration
            $interval = $doctor->billing_interval === 'yearly' ? 'addYear' : 'addMonth';
            $newRenewsAt = $extensionBase->copy()->$interval();

            // Usage Persistence Logic:
            // We DO NOT change started_at, meaning we just extend the window.
            // This is crucial if we are just "adding time" to an existing cycle.
            // If the user was expired, we MIGHT want to reset started_at to now,
            // but the request specifically asked NOT to reset started_at in renew().
            // Ideally, if expired, we should restart, but "renew" implies extension.
            // If started_at is very old, the usage count might be huge (lifetime usage),
            // but for "Billing Window" logic, having a 2-year window means 2 years of usage usage.
            // IMPORTANT: If the renewal bridges a gap, we might effectively be creating a long window.
            // However, sticking to the instruction: "Keep started_at unchanged".
            
            $doctor->update([
                // 'subscription_started_at' => unchanged,
                'subscription_renews_at' => $newRenewsAt,
                'subscription_status' => 'active',
                'subscription_grace_ends_at' => null,
            ]);

            AuditLog::log(
                'subscription_renewed',
                "Subscription renewed for Dr. {$doctor->name}. New expiration: {$newRenewsAt->toDateString()}.",
                ['doctor_id' => $doctor->id, 'admin_id' => auth()->id()]
            );

            return response()->json([
                'message' => 'Subscription renewed successfully.',
                'doctor' => $doctor
            ]);
        });
    }

    /**
     * Restart subscription (Immediate Reset).
     * Ignores existing cycles and starts fresh from NOW.
     */
    public function restart(User $doctor)
    {
        if ($doctor->role !== 'doctor') abort(404, 'User is not a doctor.');

        return DB::transaction(function () use ($doctor) {
            $now = now();
            $interval = $doctor->billing_interval === 'yearly' ? 'addYear' : 'addMonth';
            $newRenewsAt = $now->copy()->$interval();

            $doctor->update([
                'subscription_started_at' => $now,
                'subscription_renews_at' => $newRenewsAt,
                'subscription_status' => 'active',
                'subscription_grace_ends_at' => null,
            ]);

            AuditLog::log(
                'subscription_restarted',
                "Subscription force restarted for Dr. {$doctor->name}.",
                ['doctor_id' => $doctor->id, 'admin_id' => auth()->id()]
            );

            return response()->json([
                'message' => 'Subscription restarted successfully.',
                'doctor' => $doctor
            ]);
        });
    }

    /**
     * Cancel subscription.
     */
    public function cancel(User $doctor)
    {
        if ($doctor->role !== 'doctor') abort(404, 'User is not a doctor.');

        return DB::transaction(function () use ($doctor) {
            $doctor->update([
                'subscription_status' => 'cancelled',
                'subscription_grace_ends_at' => null,
            ]);

            AuditLog::log(
                'subscription_cancelled',
                "Subscription cancelled for Dr. {$doctor->name}.",
                ['doctor_id' => $doctor->id, 'admin_id' => auth()->id()]
            );

            return response()->json([
                'message' => 'Subscription cancelled.',
                'doctor' => $doctor
            ]);
        });
    }

    /**
     * Grant Lifetime status.
     */
    public function lifetime(User $doctor)
    {
        if ($doctor->role !== 'doctor') abort(404, 'User is not a doctor.');

        return DB::transaction(function () use ($doctor) {
            $doctor->update([
                'subscription_status' => 'lifetime',
                // 'subscription_started_at' => unchanged,
                'subscription_renews_at' => null, // No renewal needed
                'subscription_grace_ends_at' => null,
            ]);

            AuditLog::log(
                'subscription_lifetime_granted',
                "Lifetime subscription granted to Dr. {$doctor->name}.",
                ['doctor_id' => $doctor->id, 'admin_id' => auth()->id()]
            );

            return response()->json([
                'message' => 'Lifetime subscription granted.',
                'doctor' => $doctor
            ]);
        });
    }
    /**
     * Update Doctor's Subscription Plan explicitly.
     */
    public function updatePlan(Request $request, User $doctor)
    {
        if ($doctor->role !== 'doctor') abort(404, 'User is not a doctor.');

        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $response = DB::transaction(function () use ($doctor, $validated) {
            $doctor->update([
                'plan_id' => $validated['plan_id']
            ]);

            AuditLog::log(
                'subscription_plan_updated',
                "Subscription Plan updated for Dr. {$doctor->name}.",
                ['doctor_id' => $doctor->id, 'admin_id' => auth()->id()]
            );

            return response()->json([
                'message' => 'Plan Updated successfully',
                'doctor' => $doctor
            ]);
        });

        // Fire safely AFTER the database transaction commits completely!
        event(new DoctorPlanUpdated($doctor->id));
        Log::info("DoctorPlanUpdated dispatched for doctor {$doctor->id}");

        return $response;
    }
}
