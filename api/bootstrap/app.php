<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Illuminate\Support\Facades\Route;
 
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        using: function () {
            // 1. API Root Status Page
            Route::get('/', function () {
                return response()->json([
                    "name" => "BioOrganicCare API",
                    "version" => "2.1",
                    "status" => "running",
                    "timestamp" => now(),
                    "environment" => app()->environment(),
                    "endpoints" => [
                        "health" => "/api/health",
                        "login" => "/api/login",
                        "metrics" => "/api/metrics",
                        "status" => "/api/status",
                        "monitor" => "/api/monitor",
                        "system" => "/api/system"
                    ]
                ]);
            });

            // 2. Health Check (Deployment Success)
            Route::get('/health', function() {
                return response()->json([
                    'status' => 'ok',
                    'time' => now()
                ]);
            });

            // 3. Detailed System Status
            Route::get('/status', function () {
                return response()->json([
                    "api" => "OK",
                    "database" => \Illuminate\Support\Facades\DB::connection()->getPdo() ? "OK" : "FAIL",
                    "storage" => is_writable(storage_path()) ? "OK" : "FAIL",
                    "time" => now(),
                    "version" => config('app.version', '2.1')
                ]);
            });

            // 4. Performance Metrics
            Route::get('/metrics', function () {
                return response()->json([
                    "memory_usage_mb" => round(memory_get_usage(true) / 1024 / 1024, 2),
                    "memory_peak_mb" => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                    "php_version" => phpversion(),
                    "laravel_version" => app()->version(),
                    "server_time" => now()
                ]);
            });

            // 5. Crash Detection / Heartbeat
            Route::get('/monitor', function () {
                try {
                    \Illuminate\Support\Facades\DB::connection()->getPdo();
                    return response()->json([
                        "status" => "healthy",
                        "database" => "connected",
                        "time" => now()
                    ]);
                } catch (\Throwable $e) {
                    return response()->json([
                        "status" => "failure",
                        "error" => $e->getMessage()
                    ], 500);
                }
            });

            // 6. System Environment Info
            Route::get('/system', function () {
                return response()->json([
                    "laravel" => app()->version(),
                    "php" => phpversion(),
                    "environment" => app()->environment(),
                    "debug" => config('app.debug')
                ]);
            });

            Route::middleware('api')
                ->prefix(trim(env('APP_ENV')) === 'production' ? '' : 'api')
                ->group(base_path('routes/api.php'));
 
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
 
            Route::get('/up', function() { return response()->json(['status' => 'ok']); });
        },
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
