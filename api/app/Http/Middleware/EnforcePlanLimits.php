<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Module;
use Illuminate\Support\Str;

class EnforcePlanLimits
{
    public function handle(Request $request, Closure $next, string $moduleKey = 'auto'): Response
    {
        $user = $request->user();

        if (!$user || $user->role === 'super_admin') {
            return $next($request);
        }

        if ($moduleKey === 'auto') {
            $routeName = $request->route()?->getName();

            if (!$routeName) {
                return response()->json(['message' => 'Route not named.'], 403);
            }

            // Extract the prefix (e.g., 'clinical-catalog' from 'clinical-catalog.index')
            $routePrefix = explode('.', $routeName)[0];

            // Resolve standardized DB key from Registry
            $registry = config('module_registry');

            if (!isset($registry[$routePrefix])) {
                 // Fallback or Log mismatch
                 \Illuminate\Support\Facades\Log::warning("Module Drift Detected: Route prefix '{$routePrefix}' has no registry mapping.");
                 // We default to the route prefix itself to main backward compatibility if accidental
                 $moduleKey = $routePrefix; 
            } else {
                 $moduleKey = $registry[$routePrefix];
            }
        }

        $module = Module::where('key', $moduleKey)
            ->where('is_active', true)
            ->first();

        if (!$module) {
            return response()->json([
                'message' => "Module '{$moduleKey}' not found."
            ], 403);
        }

        // Resolve plan owner (Doctor) logic for Staff support
        $planOwner = $user->doctor_id
            ? \App\Models\User::find($user->doctor_id)
            : $user;

        if (!$planOwner || !$planOwner->plan) {
             // Fallback or error if orphan staff
             return response()->json(['message' => 'Subscription configuration error.'], 403);
        }

        // Guard: Block access if the specialty has been soft-deleted
        if ($planOwner->specialty && $planOwner->specialty->trashed()) {
            return response()->json([
                'message' => 'Specialty is inactive.'
            ], 403);
        }

        // 1. Check Plan Limits (Strict)
        if (!$planOwner->plan->modules()
            ->where('modules.id', $module->id) // Explicit table qualification
            ->wherePivot('enabled', true)
            ->exists()) {

            return response()->json([
                'message' => 'Not allowed by subscription plan.'
            ], 403);
        }

        // 2. Check Specialty Scope (Strict)
        // Ensure planOwner has specialty relation loaded or accessible
        if (!$planOwner->specialty || !$planOwner->specialty->modules()
            ->where('modules.id', $module->id)
            ->wherePivot('enabled', true)
            ->exists()) {

            return response()->json([
                'message' => 'Not enabled for specialty.'
            ], 403);
        }

        return $next($request);
    }
}
