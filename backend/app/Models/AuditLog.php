<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'metadata',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log($action, $description = null, $metadata = null, $userId = null)
    {
        // Add request context to metadata
        $metadata = array_merge((array)$metadata, [
            '_context' => [
                'ip' => request()->ip(),
                'ua' => request()->userAgent(),
            ]
        ]);

        return \App\Events\AuditEvent::dispatch($action, $description ?? '', $metadata, $userId);
    }
}
