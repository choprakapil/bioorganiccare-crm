<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuditEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public string $message;
    public ?array $metadata;
    public ?int $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(string $action, string $message, ?array $metadata = null, ?int $userId = null)
    {
        $this->action = $action;
        $this->message = $message;
        $this->metadata = $metadata;
        $this->userId = $userId ?: \Illuminate\Support\Facades\Auth::id();
    }
}
