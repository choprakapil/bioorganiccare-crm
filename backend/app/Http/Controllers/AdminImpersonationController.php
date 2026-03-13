<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AdminImpersonationController extends Controller
{
    public function impersonate(Request $request, User $doctor)
    {
        if ($request->hasHeader('X-Impersonating')) {
            return response()->json(['message' => 'Already impersonating'], 422);
        }

        if ($request->user()->role !== 'super_admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($doctor->role !== 'doctor') {
            return response()->json(['message' => 'Can only impersonate doctors'], 422);
        }

        $admin = $request->user();

        // Create Sanctum token for doctor
        $doctorToken = $doctor->createToken('impersonation-token', [
            'impersonated_by:' . $admin->id
        ])->plainTextToken;

        // PHASE D FIX: Impersonation must be auditable
        AuditLog::log(
            'admin_impersonation_started',
            "Admin '{$admin->email}' (#{$admin->id}) started impersonating doctor '{$doctor->email}' (#{$doctor->id})",
            [
                'admin_id'    => $admin->id,
                'admin_email' => $admin->email,
                'doctor_id'   => $doctor->id,
                'doctor_email' => $doctor->email,
                'ip'          => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]
        );

        return response()->json([
            'token'          => $doctorToken,
            'impersonator_id' => $admin->id,
            'doctor'         => $doctor
        ]);
    }

    public function stop(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        $adminId = null;

        if ($token && $token->abilities) {
            foreach ($token->abilities as $ability) {
                if (str_starts_with($ability, 'impersonated_by:')) {
                    $adminId = explode(':', $ability)[1] ?? null;
                    break;
                }
            }
        }

        if (!$adminId) {
            return response()->json(['message' => 'Not an impersonation token'], 400);
        }

        // Delete the active impersonation token
        $token->delete();

        $admin = User::find($adminId);

        if (!$admin || $admin->role !== 'super_admin') {
            return response()->json(['message' => 'Invalid admin user'], 400);
        }

        // Log the stop event
        AuditLog::log(
            'admin_impersonation_stopped',
            "Impersonation stopped — admin #{$adminId} has retaken control",
            ['admin_id' => $adminId, 'impersonated_user_id' => $request->user()->id]
        );

        // Generate a new Sanctum token for the admin
        $adminToken = $admin->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Impersonation stopped',
            'token'   => $adminToken
        ]);
    }
}
