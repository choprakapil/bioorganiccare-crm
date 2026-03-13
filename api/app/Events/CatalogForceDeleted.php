<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CatalogForceDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $entityType;
    public int $entityId;
    public array $metadata;

    public function __construct(string $entityType, int $entityId, array $metadata = [])
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->metadata = $metadata;
    }
}
