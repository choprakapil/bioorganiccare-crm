<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerEntryIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->doctor = User::create([
            'name' => 'Dr. Ledger',
            'email' => 'ledger@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor'
        ]);

        $this->actingAs($this->doctor);

        $this->patient = Patient::create([
            'name' => 'Alice Doe',
        ]);
    }

    /** @test */
    public function ledger_tracks_invoice_lifecycle()
    {
        // 1. Create Invoice
        $invoice = Invoice::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
            'balance_due' => 1000.00,
            'status' => InvoiceStatus::UNPAID
        ]);

        // Manually record creation since we bypass the service in this direct model create
        LedgerEntry::record($invoice, 'invoice_created', 1000.00, 'debit');

        $this->assertEquals(1000.00, LedgerEntry::where('invoice_id', $invoice->id)->where('direction', 'debit')->sum('amount'));

        // 2. Apply Payment
        LedgerEntry::record($invoice, 'payment_applied', 400.00, 'credit');
        $invoice->update(['paid_amount' => 400.00, 'balance_due' => 600.00]);

        // Validate: Sum(payments) = paid_amount
        $sumPayments = LedgerEntry::where('invoice_id', $invoice->id)
            ->where('type', 'payment_applied')
            ->where('direction', 'credit')
            ->sum('amount');
        
        $this->assertEquals((float)$invoice->paid_amount, (float)$sumPayments, "Sum(payments) must match paid_amount");

        // Validate: standard accounting balance
        $debits = LedgerEntry::where('invoice_id', $invoice->id)->where('direction', 'debit')->sum('amount');
        $credits = LedgerEntry::where('invoice_id', $invoice->id)->where('direction', 'credit')->sum('amount');
        
        $this->assertEquals((float)$invoice->balance_due, (float)($debits - $credits), "Net ledger balance must match balance_due");
    }

    /** @test */
    public function ledger_entries_are_immutable()
    {
        $invoice = Invoice::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'total_amount' => 100
        ]);

        $entry = LedgerEntry::record($invoice, 'invoice_created', 100, 'debit');

        $this->expectException(\Exception::class);
        $entry->update(['amount' => 200]);
    }

    /** @test */
    public function ledger_entries_cannot_be_deleted()
    {
        $invoice = Invoice::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'total_amount' => 100
        ]);

        $entry = LedgerEntry::record($invoice, 'invoice_created', 100, 'debit');

        $this->expectException(\Exception::class);
        $entry->delete();
    }
}
