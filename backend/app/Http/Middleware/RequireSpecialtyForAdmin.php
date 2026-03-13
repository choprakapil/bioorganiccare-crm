<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Support\Context\TenantContext;

class RequireSpecialtyForAdmin
{
    protected TenantContext $context;

    public function __construct(TenantContext $context)
    {
        $this->context = $context;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->context->getAuthUser();

        if ($user && $user->role === 'super_admin') {
            if ($request->is('api/admin/specialties/archived') || 
                $request->is('api/admin/services/archived') || 
                $request->is('api/admin/medicines/archived') || 
                $request->is('api/admin/governance/*') || 
                $request->is('api/admin/delete/*')) {
                return $next($request);
            }

            if ($request->is('api/admin/specialties/*') || 
                $request->is('api/admin/pharmacy-catalog/*') || 
                $request->is('api/admin/clinical-catalog/*') ||
                $request->is('api/admin/service-submissions/*')) {
                
                if (!$this->context->getSpecialtyId()) {
                    return response()->json([
                        'message' => 'Specialty context is required for this Admin operation.'
                    ], 422);
                }
            }
        }

        return $next($request);
    }
}
