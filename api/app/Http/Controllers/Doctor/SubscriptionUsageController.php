<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionUsageService;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Support\Context\TenantContext;

class SubscriptionUsageController extends Controller
{
    protected SubscriptionUsageService $usageService;
    protected TenantContext $context;

    public function __construct(SubscriptionUsageService $usageService, TenantContext $context)
    {
        $this->usageService = $usageService;
        $this->context = $context;
    }

    /**
     * GET /subscription/usage
     *
     * Returns a complete usage dashboard:
     *   - patients used vs limit
     *   - appointments used vs monthly limit
     *   - staff used vs limit
     *   - plan details and subscription window
     */
    public function usage(Request $request)
    {
        $doctor = $this->context->getClinicOwner();

        if (!$doctor) {
            return response()->json(['message' => 'Clinic context required.'], 422);
        }
        $cacheKey = "subscription_usage_{$doctor->id}";

        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached) {
            return response()->json($cached);
        }

        $doctor->load('plan');
        $plan = $doctor->plan;

        if (!$plan) {
            return response()->json(['message' => 'No plan assigned'], 422);
        }

        // ── Patients ──────────────────────────────────────────────────────────
        $patientCount = Patient::where('doctor_id', $doctor->id)->count();
        $maxPatients  = $plan->max_patients;

        // ── Appointments (current billing cycle) ─────────────────────────────
        $apptQuery = Appointment::where('doctor_id', $doctor->id)
            ->where('created_at', '>=', $doctor->subscription_started_at);
        if ($doctor->subscription_renews_at) {
            $apptQuery->where('created_at', '<', $doctor->subscription_renews_at);
        }
        $apptCount = $apptQuery->count();
        $maxAppts  = $plan->max_appointments_monthly;

        // ── Staff ─────────────────────────────────────────────────────────────
        $staffCount = User::where('doctor_id', $doctor->id)->where('role', 'staff')->count();
        $maxStaff   = $plan->max_staff;

        $buildUsage = function (int $used, int $limit) {
            $unlimited = $limit === -1;
            return [
                'used'      => $used,
                'limit'     => $unlimited ? null : $limit,
                'remaining' => $unlimited ? null : max(0, $limit - $used),
                'percent'   => $unlimited ? null : ($limit > 0 ? round(($used / $limit) * 100, 1) : 100.0),
                'unlimited' => $unlimited,
            ];
        };

        $response = [
            'plan' => [
                'id'   => $plan->id,
                'name' => $plan->name,
                'tier' => $plan->tier,
            ],
            'subscription' => [
                'status'    => $doctor->subscription_status,
                'started_at' => $doctor->subscription_started_at,
                'renews_at'  => $doctor->subscription_renews_at,
                'grace_ends_at' => $doctor->subscription_grace_ends_at,
            ],
            'usage' => [
                'patients'     => $buildUsage($patientCount, $maxPatients),
                'appointments' => $buildUsage($apptCount, $maxAppts),
                'staff'        => $buildUsage($staffCount, $maxStaff),
            ],
        ];

        \Illuminate\Support\Facades\Cache::put($cacheKey, $response, 900); // 15 mins

        return response()->json($response);
    }
}
