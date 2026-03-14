<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeletionRequest extends Model
{
    protected $fillable = [
        'entity_type',
        'entity_id',
        'requested_by',
        'approved_by',
        'status',
        'cascade_preview_json',
        'reason',
        'approved_at',
        'executed_at',
    ];

    protected $casts = [
        'cascade_preview_json' => 'array',
        'approved_at'          => 'datetime',
        'executed_at'          => 'datetime',
    ];

    // ── Valid statuses ──────────────────────────────────────────────────────

    public const STATUSES = ['pending', 'approved', 'rejected', 'executed'];

    public static function isValidStatus(string $status): bool
    {
        return in_array($status, self::STATUSES, true);
    }

    // ── Status helpers ──────────────────────────────────────────────────────

    public function isPending(): bool   { return $this->status === 'pending';  }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
    public function isExecuted(): bool  { return $this->status === 'executed'; }

    // ── Relationships ───────────────────────────────────────────────────────

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
