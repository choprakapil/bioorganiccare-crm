<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceAuditLog extends Model
{
    // Append-only table — no updated_at, no soft deletes
    public $timestamps = false;

    protected $fillable = [
        'submission_id',
        'action',
        'performed_by_user_id',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function submission(): BelongsTo
    {
        return $this->belongsTo(ServiceSubmission::class, 'submission_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }

    // ── Factory helper ─────────────────────────────────────────────────────────

    /**
     * Write a single audit log entry.
     * Called everywhere — single source of truth for log creation.
     */
    public static function record(
        int $submissionId,
        string $action,
        int $performedByUserId,
        ?string $notes = null
    ): self {
        return self::create([
            'submission_id'        => $submissionId,
            'action'               => $action,
            'performed_by_user_id' => $performedByUserId,
            'notes'                => $notes,
            'created_at'           => now(),
        ]);
    }
}
