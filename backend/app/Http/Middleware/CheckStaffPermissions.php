<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStaffPermissions
{
    /**
     * Handle an incoming request.
     * Granular permissions: patients, appointments, treatments, pharmacy, billing_write, reports, settings
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Doctor (Owner) has full access
        if ($user->role === 'doctor' || $user->role === 'super_admin') {
            return $next($request);
        }

        // Reject non-staff users after doctor/super_admin bypass.
        if ($user->role !== 'staff') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $type = $user->role_type; // assistant | receptionist
        $perms = $user->permissions;

        // Hard block: staff must always be linked to a doctor account.
        if (!$user->doctor_id) {
            return response()->json(['message' => 'Staff account not linked to a doctor.'], 403);
        }

        // permissions must be a JSON object (array cast in model)
        if (!is_array($perms)) {
            return response()->json(['message' => 'Access denied. Permissions not configured.'], 403);
        }

        // Route/module token -> granular permission key
        $map = [
            'clinical' => 'treatments',
            'patients' => 'patients',
            'appointments' => 'appointments',
            'pharmacy' => 'pharmacy',
            'billing_write' => 'billing_write',
            'insights' => 'reports',
            'reports' => 'reports',
            'settings' => 'settings',
        ];
        $key = $map[$module] ?? $module;

        // Receptionists are always blocked from these granular capabilities.
        if ($type === 'receptionist' && in_array($key, ['treatments', 'pharmacy', 'billing_write', 'reports'], true)) {
            return response()->json(['message' => 'Access denied. Restricted for Receptionists.'], 403);
        }

        // Strict allow-list: missing key => deny, non-true => deny.
        if (!array_key_exists($key, $perms) || $perms[$key] !== true) {
            return response()->json(['message' => 'Access denied by doctor.'], 403);
        }

        return $next($request);
    }
}
