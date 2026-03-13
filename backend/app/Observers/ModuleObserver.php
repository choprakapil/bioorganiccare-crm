<?php

namespace App\Observers;

use App\Models\Module;
use Illuminate\Support\Facades\Cache;

class ModuleObserver
{
    /**
     * Handle the Module "created" event.
     */
    public function created(Module $module): void
    {
        $this->bustSchemaCache();
    }

    /**
     * Handle the Module "updated" event.
     */
    public function updated(Module $module): void
    {
        $this->bustSchemaCache();
    }

    /**
     * Handle the Module "deleted" event.
     */
    public function deleted(Module $module): void
    {
        $this->bustSchemaCache();
    }

    /**
     * Bumps the global epoch to invalidate dynamically mapped Tenancy caches.
     */
    private function bustSchemaCache(): void
    {
        Cache::forever('saas_module_schema_version', time());
    }
}
