<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CatalogAuditService
{
    public static function log(string $entityType, int $entityId, string $action, array $metadata = [])
    {
        DB::table('catalog_audit_logs')->insert([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'performed_by_user_id' => auth()->id(),
            'metadata' => json_encode($metadata),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
