<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class EnsureSubscriptionActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // 1. Scope: Only Doctors and Staff are subject to subscription checks
        if (!$user || !in_array($user->role, ['doctor', 'staff'])) {
            return $next($request);
        }

        // 2. Read-Only Access is ALWAYS Allowed (GET, HEAD, OPTIONS)
        // We do not block data retrieval, only modification.
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $next($request);
        }

        // 3. Resolve Subscription Owner
        // If Staff, check their Doctor's subscription status.
        $owner = $user;
        if ($user->role === 'staff') {
            if (!$user->doctor_id) {
                 return response()->json(['message' => 'Staff account not linked to a doctor.'], 403);
            }
            $owner = User::find($user->doctor_id);
            if (!$owner) {
                return response()->json(['message' => 'Subscription verification failed: Doctor account not found.'], 403);
            }
        }

        // 4. System Initialization Check
        // If dates are NULL, the billing engine has not "claimed" this user yet.
        if (is_null($owner->subscription_started_at) || is_null($owner->subscription_renews_at)) {
            return $next($request);
        }

        // 5. Automatic Expiration Transition (Lazy Evaluation)
        // If the subscription is marked 'active' but the date has passed,
        // we automatically transition it to 'past_due' and start the grace period.
        if ($owner->subscription_status === 'active' && now()->gt($owner->subscription_renews_at)) {
            $owner->subscription_status = 'past_due';
            // Grant 3 Days Grace Period from the moment of first detection (or renews_at?)
            // Robustness: Grant from NOW to avoid retroactive expiration blocking immediately if cron missed.
            $owner->subscription_grace_ends_at = now()->addDays(3); 
            $owner->save();
        }

        // 6. Status Logic
        $status = $owner->subscription_status ?? 'active';

        // ACTIVE / LIFETIME: Allow
        if ($status === 'active' || $status === 'lifetime') {
            return $next($request);
        }

        // PAST_DUE: Grace Period Check
        if ($status === 'past_due') {
            // If grace period exists and we are within it
            if ($owner->subscription_grace_ends_at && now()->lte($owner->subscription_grace_ends_at)) {
                $response = $next($request);
                if ($response instanceof Response) {
                    $response->headers->set('X-Subscription-Warning', 'Grace Period Active');
                }
                return $response;
            }
            // Grace Period Expired -> Block Write
            return response()->json(['message' => 'Subscription past due. Payment required to continue editing data.'], 402);
        }

        // EXPIRED / CANCELLED: Block Write
        if (in_array($status, ['expired', 'cancelled'])) {
            return response()->json(['message' => 'Subscription inactive. Payment required to continue editing data.'], 402);
        }

        return $next($request);
    }
}
