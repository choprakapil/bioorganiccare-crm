<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'subscription' => \App\Http\Middleware\EnsureSubscriptionActive::class,
            'module' => \App\Http\Middleware\EnforcePlanLimits::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'check.permission' => \App\Http\Middleware\CheckStaffPermissions::class,
            'tenant' => \App\Http\Middleware\ResolveTenantContext::class,
            'require.specialty.admin' => \App\Http\Middleware\RequireSpecialtyForAdmin::class,
            'block.staff.deletion' => \App\Http\Middleware\BlockStaffDeletion::class,
            'require.doctor.role' => \App\Http\Middleware\RequireDoctorRole::class,
            'block.receptionist' => \App\Http\Middleware\BlockReceptionist::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated.'
                ], 401);
            }
        });
    })->create();
