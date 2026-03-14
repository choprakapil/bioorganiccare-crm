<?php

namespace App\Listeners;

use App\Events\AuditEvent;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessAuditEvent implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(AuditEvent $event): void
    {
        // Use the existing AuditLog model to persist the data
        AuditLog::create([
            'user_id' => $event->userId,
            'action' => $event->action,
            'description' => $event->message,
            'metadata' => $event->metadata,
        ]);
    }
}
