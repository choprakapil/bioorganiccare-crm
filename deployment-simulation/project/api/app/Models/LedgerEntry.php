<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LedgerEntry extends Model
{
    use \App\Traits\BelongsToTenancy;

    protected $fillable = [
        'invoice_id',
        'transaction_group_uuid',
        'type',
        'idempotency_key',
        'amount',
        'direction',
        'meta',
        'created_by'
    ];

    protected $casts = [
        'meta' => 'array',
        'amount' => 'decimal:2',
    ];

    protected static $allowCreation = false;

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!self::$allowCreation) {
                throw new \Exception("LedgerEntry must be created via record() to ensure snapshot integrity.");
            }

            if (!$model->created_by && Auth::check()) {
                $model->created_by = Auth::id();
            }
        });

        // IMMUTABILITY ENFORCEMENT
        static::updating(function ($model) {
            throw new \Exception("Ledger entries are immutable and cannot be updated.");
        });

        static::deleting(function ($model) {
            throw new \Exception("Ledger entries are immutable and cannot be deleted.");
        });
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Event types that must only occur once per invoice.
     * These get auto-generated deterministic idempotency keys.
     */
    private static array $singleOccurrenceEvents = [
        'invoice_created',
        'cancellation',
        'resurrection',
        'reallocation',
    ];

    /**
     * Record a new ledger entry.
     *
     * For single-occurrence events (invoice_created, cancellation, etc.),
     * a deterministic idempotency key is auto-generated if none is provided.
     * This ensures the UNIQUE(invoice_id, idempotency_key) constraint
     * prevents duplicate entries at the database level.
     *
     * @param Invoice     $invoice              The invoice this entry belongs to
     * @param string      $type                 Event type (invoice_created, payment_applied, etc.)
     * @param float       $amount               Monetary amount
     * @param string      $direction            'debit' or 'credit'
     * @param array       $meta                 Optional metadata
     * @param string|null $idempotencyKey        Caller-provided idempotency key
     * @param string|null $transactionGroupUuid  Group UUID for paired entries; auto-generated if null
     */
    public static function record(
        Invoice $invoice,
        string $type,
        float $amount,
        string $direction,
        array $meta = [],
        ?string $idempotencyKey = null,
        ?string $transactionGroupUuid = null
    ) {
        // Auto-generate deterministic key for single-occurrence events.
        if ($idempotencyKey === null) {
            $idempotencyKey = in_array($type, self::$singleOccurrenceEvents, true)
                ? "evt_{$type}_{$direction}"
                : Str::uuid()->toString();
        }

        // Auto-generate transaction group UUID if not provided.
        // Callers creating paired entries (debit+credit) should pass the SAME UUID.
        $transactionGroupUuid = $transactionGroupUuid ?? Str::uuid()->toString();

        return \Illuminate\Support\Facades\DB::transaction(function () use ($invoice, $type, $amount, $direction, $meta, $idempotencyKey, $transactionGroupUuid) {
            self::$allowCreation = true;
            try {
                $entry = self::create([
                    'invoice_id' => $invoice->id,
                    'transaction_group_uuid' => $transactionGroupUuid,
                    'doctor_id' => $invoice->doctor_id,
                    'type' => $type,
                    'idempotency_key' => $idempotencyKey,
                    'amount' => $amount,
                    'direction' => $direction,
                    'meta' => $meta,
                ]);
            } finally {
                self::$allowCreation = false;
            }

            if ($direction === 'debit') {
                $invoice->increment('ledger_debit_total', $amount);
            } else {
                $invoice->increment('ledger_credit_total', $amount);
            }

            return $entry;
        });
    }
}

