<?php

namespace App\Listeners;

use Illuminate\Support\Facades\DB;
use ReflectionClass;

class LogCatalogAudit
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $action = (new ReflectionClass($event))->getShortName();

        DB::table('catalog_audit_logs')->insert([
            'entity_type' => $event->entityType,
            'entity_id' => $event->entityId,
            'action' => $action,
            'performed_by_user_id' => auth()->id() ?? null,
            'metadata' => json_encode($event->metadata),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
