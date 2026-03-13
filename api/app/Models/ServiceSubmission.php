<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceSubmission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'specialty_id',
        'submitted_by_user_id',
        'original_name',
        'normalized_name',
        'proposed_type_id',
        'proposed_default_fee',
        'status',
        'rejection_reason',
        'reviewed_by_user_id',
        'reviewed_at',
    ];

    protected $casts = [
        'proposed_default_fee' => 'decimal:2',
        'reviewed_at'          => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function proposedType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class, 'proposed_type_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ServiceAuditLog::class, 'submission_id');
    }

    // ── Status helpers ─────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
