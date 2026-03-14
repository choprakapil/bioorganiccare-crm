<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // System-level override: super_admin bypasses all role checks
        if ($user && $user->role === 'super_admin') {
            return $next($request);
        }

        if (!$user || !in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Your account permissions (' . ($user->role ?? 'none') . ') do not allow access to this resource.'
            ], 403);
        }

        return $next($request);
    }
}
