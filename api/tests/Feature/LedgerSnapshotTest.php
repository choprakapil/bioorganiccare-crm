<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerSnapshotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->doctor = User::create([
            'name' => 'Dr. Snapshot',
            'email' => 'snapshot@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor'
        ]);

        $this->actingAs($this->doctor);

        $this->patient = Patient::create([
            'name' => 'Snap Doe',
        ]);
    }

    /** @test */
    public function invoice_tracks_ledger_totals_via_snapshots()
    {
        $invoice = Invoice::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
            'balance_due' => 1000.00,
            'status' => InvoiceStatus::UNPAID
        ]);

        $this->assertEquals(0, $invoice->ledger_debit_total);
        $this->assertEquals(0, $invoice->ledger_credit_total);

        // 1. Record Debit (Creation)
        LedgerEntry::record($invoice, 'invoice_created', 1000.00, 'debit');

        $invoice->refresh();
        $this->assertEquals(1000.00, (float)$invoice->ledger_debit_total);
        $this->assertEquals(0, (float)$invoice->ledger_credit_total);

        // 2. Record Credit (Payment)
        LedgerEntry::record($invoice, 'payment_applied', 300.00, 'credit');
        
        // Before refresh, we should verify the increment happened
        $invoice->refresh();
        $this->assertEquals(1000.00, (float)$invoice->ledger_debit_total);
        $this->assertEquals(300.00, (float)$invoice->ledger_credit_total);

        // 3. Verify Reconciliation works with snapshots
        // Set balance to match snapshots logic: 1000 - 300 = 700
        $invoice->paid_amount = 300.00;
        $invoice->balance_due = 700.00;
        
        // This save() will trigger reconcileLedger() which uses snapshots
        $invoice->save(); 
        
        $this->assertTrue(true, "Reconciliation passed with snapshots");
    }

    /** @test */
    public function reconciliation_fails_if_snapshot_desynced()
    {
        $invoice = Invoice::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
            'balance_due' => 1000.00,
            'status' => InvoiceStatus::UNPAID
        ]);

        LedgerEntry::record($invoice, 'invoice_created', 1000.00, 'debit');

        // Manually corrupt snapshot
        $invoice->ledger_debit_total = 900.00; 
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Ledger Reconciliation Error");
        
        $invoice->save(); // Should fail because 900 - 0 != 1000 (balance_due)
    }
}
