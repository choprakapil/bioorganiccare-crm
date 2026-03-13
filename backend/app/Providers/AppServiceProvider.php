<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Support\Context\TenantContext::class, function ($app) {
            return new \App\Support\Context\TenantContext();
        });

        $this->app->singleton(\App\Services\DeleteManager::class, function () {
            return new \App\Services\DeleteManager();
        });

        $this->app->singleton(\App\Services\DeletionWorkflowManager::class, function ($app) {
            return new \App\Services\DeletionWorkflowManager(
                $app->make(\App\Services\DeleteManager::class),
                $app->make(\App\Services\GovernanceApprovalPolicy::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\AuditEvent::class,
            \App\Listeners\ProcessAuditEvent::class
        );

        \App\Models\Module::observe(\App\Observers\ModuleObserver::class);

        if (!\Illuminate\Support\Facades\Cache::has('saas_module_schema_version')) {
            \Illuminate\Support\Facades\Cache::forever('saas_module_schema_version', time());
        }

        $catalogEvents = [
            \App\Events\CatalogEntityCreated::class,
            \App\Events\CatalogEntityUpdated::class,
            \App\Events\CatalogEntityArchived::class,
            \App\Events\CatalogEntityRestored::class,
            \App\Events\CatalogForceDeleteAttempted::class,
            \App\Events\CatalogForceDeleted::class,
            \App\Events\CatalogActivated::class,
            \App\Events\CatalogDeactivated::class,
        ];

        foreach ($catalogEvents as $eventClass) {
            \Illuminate\Support\Facades\Event::listen(
                $eventClass,
                \App\Listeners\LogCatalogAudit::class
            );
        }

        // Production Rate Limiting
        \Illuminate\Support\Facades\RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
            $user = $request->user();
            if (!$user) {
                return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->ip());
            }

            // Doctor/Staff: 100 per minute
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(100)->by($user->id);
        });

        \Illuminate\Support\Facades\RateLimiter::for('billing', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Slow Query Logging (>100ms)
        \Illuminate\Support\Facades\DB::listen(function ($query) {
            if ($query->time > 100) {
                \Illuminate\Support\Facades\Log::warning('Slow query detected', [
                    'sql'      => $query->sql,
                    'bindings' => $query->bindings,
                    'time'     => $query->time,
                ]);
            }
        });
    }
}
