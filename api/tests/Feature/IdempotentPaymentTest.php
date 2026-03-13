<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\Specialty;
use App\Models\Module;
use App\Models\SubscriptionPlan;
use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;

class IdempotentPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->specialty = Specialty::create([
            'name' => 'General Dentistry',
            'slug' => 'general-dentistry',
            'is_active' => true
        ]);

        $this->module = Module::create([
            'name' => 'Billing',
            'key' => 'billing',
            'is_active' => true
        ]);

        $this->specialty->modules()->attach($this->module->id, ['enabled' => true]);

        $this->plan = SubscriptionPlan::create([
            'name' => 'Pro Plan',
            'specialty_id' => $this->specialty->id,
            'tier' => 'pro',
            'price' => 1000,
            'is_active' => true
        ]);

        $this->plan->modules()->attach($this->module->id, ['enabled' => true]);
        
        $this->doctor = User::create([
            'name' => 'Dr. Safe',
            'email' => 'safe@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'specialty_id' => $this->specialty->id,
            'plan_id' => $this->plan->id
        ]);

        $this->actingAs($this->doctor);

        $this->patient = Patient::create([
            'name' => 'Bob Doe',
        ]);
    }

    /** @test */
    public function apply_payment_is_idempotent()
    {
        // Create invoice bypassing events (no ledger yet), then seed the
        // opening invoice_created debit — matching production InvoiceService flow.
        $invoice = Invoice::withoutEvents(fn () => Invoice::forceCreate([
            'doctor_id'           => $this->doctor->id,
            'patient_id'          => $this->patient->id,
            'total_amount'        => 1000.00,
            'paid_amount'         => 0.00,
            'balance_due'         => 1000.00,
            'status'              => InvoiceStatus::UNPAID,
            'ledger_debit_total'  => 0.00,
            'ledger_credit_total' => 0.00,
        ]));

        // Seed opening debit so reconcileLedger fires correctly
        LedgerEntry::record($invoice, 'invoice_created', 1000.00, 'debit');
        $invoice->refresh();

        $idempotencyKey = (string) Str::uuid();
        $payload = [
            'payment_amount' => 500.00,
            'payment_method' => 'Cash',
            'idempotency_key' => $idempotencyKey
        ];

        // First call
        $response1 = $this->postJson("/api/invoices/{$invoice->id}/apply-payment", $payload);
        $response1->assertStatus(200);

        $invoice->refresh();
        $this->assertEquals(500.00, (float)$invoice->paid_amount);
        $this->assertEquals(1, LedgerEntry::where('invoice_id', $invoice->id)->where('type', 'payment_applied')->count());

        // Second call with same key
        $response2 = $this->postJson("/api/invoices/{$invoice->id}/apply-payment", $payload);
        $response2->assertStatus(200);

        $invoice->refresh();
        $this->assertEquals(500.00, (float)$invoice->paid_amount, "Paid amount should not have increased on duplicate call");
        $this->assertEquals(1, LedgerEntry::where('invoice_id', $invoice->id)->where('type', 'payment_applied')->count(), "No duplicate ledger entry should be created");
    }

    /** @test */
    public function apply_payment_fails_without_key()
    {
        $invoice = Invoice::withoutEvents(fn () => Invoice::forceCreate([
            'doctor_id'           => $this->doctor->id,
            'patient_id'          => $this->patient->id,
            'total_amount'        => 1000.00,
            'paid_amount'         => 0.00,
            'balance_due'         => 1000.00,
            'status'              => InvoiceStatus::UNPAID,
            'ledger_debit_total'  => 0.00,
            'ledger_credit_total' => 0.00,
        ]));
        LedgerEntry::record($invoice, 'invoice_created', 1000.00, 'debit');
        $invoice->refresh();

        $response = $this->postJson("/api/invoices/{$invoice->id}/apply-payment", [
            'payment_amount' => 100.00
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['idempotency_key']);
    }
}
