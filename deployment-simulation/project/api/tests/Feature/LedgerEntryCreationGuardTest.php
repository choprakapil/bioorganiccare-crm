<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerEntryCreationGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->doctor = User::create([
            'name' => 'Dr. Guard',
            'email' => 'guard@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor'
        ]);

        $this->actingAs($this->doctor);

        $this->patient = Patient::create([
            'name' => 'Guard Patient',
        ]);
    }

    /** @test */
    public function direct_ledger_creation_is_blocked()
    {
        $invoice = Invoice::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'total_amount' => 100,
            'paid_amount' => 0,
            'balance_due' => 100,
            'status' => InvoiceStatus::UNPAID
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("LedgerEntry must be created via record()");

        LedgerEntry::create([
            'invoice_id' => $invoice->id,
            'doctor_id' => $this->doctor->id,
            'type' => 'malicious_entry',
            'amount' => 1000,
            'direction' => 'credit'
        ]);
    }

    /** @test */
    public function record_method_is_allowed_to_create_entries()
    {
        $invoice = Invoice::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'total_amount' => 500,
            'paid_amount' => 0,
            'balance_due' => 500,
            'status' => InvoiceStatus::UNPAID
        ]);

        // This should pass
        $entry = LedgerEntry::record($invoice, 'invoice_created', 500, 'debit');

        $this->assertDatabaseHas('ledger_entries', [
            'id' => $entry->id,
            'amount' => 500,
            'type' => 'invoice_created'
        ]);

        $invoice->refresh();
        $this->assertEquals(500, (float)$invoice->ledger_debit_total);
    }
}
