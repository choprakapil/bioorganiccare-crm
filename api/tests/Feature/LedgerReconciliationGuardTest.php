<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerReconciliationGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->doctor = User::create([
            'name' => 'Dr. Auditor',
            'email' => 'auditor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor'
        ]);

        $this->actingAs($this->doctor);

        $this->patient = Patient::create([
            'name' => 'Charlie Doe',
        ]);
    }

    /** @test */
    public function it_allows_saving_when_ledger_matches()
    {
        $invoice = Invoice::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
            'balance_due' => 1000.00,
            'status' => InvoiceStatus::UNPAID
        ]);

        // Create matching ledger entry
        LedgerEntry::record($invoice, 'invoice_created', 1000.00, 'debit');

        // Should not throw exception
        $invoice->update(['status' => InvoiceStatus::PARTIAL]);
        $this->assertEquals(InvoiceStatus::PARTIAL, $invoice->refresh()->status);
    }

    /** @test */
    public function it_throws_exception_when_ledger_mismatches()
    {
        $invoice = Invoice::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
            'balance_due' => 1000.00,
            'status' => InvoiceStatus::UNPAID
        ]);

        // Create MISMATCHED ledger entry (e.g. 900 instead of 1000)
        LedgerEntry::record($invoice, 'invoice_created', 900.00, 'debit');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Ledger Reconciliation Error");

        // Saving should trigger the guard
        $invoice->save();
    }

    /** @test */
    public function it_detects_mismatch_after_partial_payment()
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

        // Apply payment update but FORGET to record in ledger
        $invoice->paid_amount = 400.00;
        $invoice->balance_due = 600.00;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Ledger Reconciliation Error");
        
        $invoice->save();
    }
}
