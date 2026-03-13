<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

use App\Models\Traits\ProtectedDeletion;

class Invoice extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory, ProtectedDeletion;
    use \App\Traits\BelongsToTenancy;
    
    protected $appends = ['subtotal'];

    protected $fillable = [
        'uuid',
        'patient_id',
        'total_amount',
        'discount_amount',
        'paid_amount',
        'balance_due',
        'status',
        'payment_method',
        'is_finalized',
        'due_date'
    ];

    protected $casts = [
        'uuid' => 'string',
        'is_finalized' => 'boolean',
        'stock_reverted' => 'boolean',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'due_date' => 'date'
    ];

    public static function booted()
    {
        static::creating(function ($invoice) {
            $invoice->uuid = (string) \Illuminate\Support\Str::uuid();
        });

        static::saved(function ($invoice) {
            $invoice->reconcileLedger();
            
            // Dispatch background analytics refresh
            \App\Jobs\ComputeFinancialSummary::dispatch($invoice->doctor_id);
            \App\Jobs\ComputeGrowthInsights::dispatch($invoice->doctor_id);
        });
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function treatments()
    {
        return $this->hasMany(Treatment::class);
    }

    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class);
    }

    /**
     * Verify ledger consistency. 
     * Sum(debits) - Sum(credits) must match balance_due.
     */
    public function reconcileLedger()
    {
        // Only enforce if ledger entries exist to avoid blocking legacy imports
        if ($this->ledger_debit_total == 0 && $this->ledger_credit_total == 0 && !$this->ledgerEntries()->exists()) {
            return;
        }

        $debits = (float) $this->ledger_debit_total;
        $credits = (float) $this->ledger_credit_total;
        $expectedBalance = round($debits - $credits, 2);
        
        $actualBalance = round((float) $this->balance_due, 2);

        if (abs($expectedBalance - $actualBalance) > 0.001) {
            throw new \Exception("Ledger Reconciliation Error for Invoice #{$this->id}: Expected Balance {$expectedBalance}, Found {$actualBalance}. (Snapshot: D={$debits}, C={$credits})");
        }
    }

    protected function subtotal(): Attribute
    {
        return Attribute::make(
            get: fn () => (float) $this->total_amount + (float) $this->discount_amount,
        );
    }
}
