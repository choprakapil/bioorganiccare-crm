<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Models\User;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\Treatment;
use App\Models\Inventory;
use App\Models\InventoryBatch;
use App\Models\MasterMedicine;
use App\Models\Specialty;
use App\Models\Module;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CriticalHardeningTest
 *
 * Covers all four critical bugs patched in Phase A:
 *
 * CRITICAL-1: Cancellation double-credit fix
 * CRITICAL-2: Status→PAID missing payment_applied ledger entry fix
 * CRITICAL-3: unit_cost mass assignment gap fix
 * CRITICAL-4: approveReallocation cross-tenant tenancy guard fix
 */
class CriticalHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $doctor;
    protected Patient $patient;
    protected Specialty $specialty;
    protected SubscriptionPlan $plan;
    protected Module $module;

    protected function setUp(): void
    {
        parent::setUp();

        $this->specialty = Specialty::create([
            'name'      => 'General',
            'slug'      => 'general',
            'is_active' => true,
        ]);

        $this->module = Module::create([
            'name'      => 'Billing',
            'key'       => 'billing',
            'is_active' => true,
        ]);
        $this->specialty->modules()->attach($this->module->id, ['enabled' => true]);

        $this->plan = SubscriptionPlan::create([
            'name'         => 'Test Plan',
            'specialty_id' => $this->specialty->id,
            'tier'         => 'pro',
            'price'        => 999,
            'is_active'    => true,
        ]);
        $this->plan->modules()->attach($this->module->id, ['enabled' => true]);

        $this->doctor = User::create([
            'name'         => 'Dr. Alpha',
            'email'        => 'alpha@test.com',
            'password'     => bcrypt('password'),
            'role'         => 'doctor',
            'specialty_id' => $this->specialty->id,
            'plan_id'      => $this->plan->id,
        ]);

        $this->actingAs($this->doctor);

        $this->patient = Patient::create(['name' => 'Test Patient']);
    }

    // =========================================================================
    // Helper: create an invoice with ledger bootstrapped, bypassing events
    // =========================================================================

    /**
     * Create a bare invoice (bypassing reconcileLedger event) then immediately
     * seed the opening ledger debit via the proper LedgerEntry::record() path.
     */
    private function bootstrapInvoice(float $total): Invoice
    {
        // Create invoice without triggering reconcileLedger (no ledger yet)
        $invoice = Invoice::withoutEvents(fn () => Invoice::forceCreate([
            'patient_id'          => $this->patient->id,
            'doctor_id'           => $this->doctor->id,
            'total_amount'        => $total,
            'paid_amount'         => 0.00,
            'balance_due'         => $total,
            'status'              => InvoiceStatus::UNPAID,
            'ledger_debit_total'  => 0.00,
            'ledger_credit_total' => 0.00,
        ]));

        // Seed the opening debit via the controlled record() method
        LedgerEntry::record($invoice, 'invoice_created', $total, 'debit');
        $invoice->refresh();

        return $invoice;
    }

    // =========================================================================
    // CRITICAL-1: Cancellation must credit only the unpaid portion
    // =========================================================================

    /** @test */
    public function cancellation_credits_only_unpaid_balance_not_full_amount()
    {
        $invoice = $this->bootstrapInvoice(1000.00);

        // Apply partial payment of 400
        $this->postJson("/api/invoices/{$invoice->id}/apply-payment", [
            'payment_amount'  => 400.00,
            'payment_method'  => 'Cash',
            'idempotency_key' => 'pay-c1-' . $invoice->id,
        ])->assertStatus(200);

        $invoice->refresh();
        $this->assertEquals(400.00, (float) $invoice->paid_amount, 'Paid amount should be 400 after partial payment');
        $this->assertEquals(600.00, (float) $invoice->balance_due, 'Balance due should be 600');

        // Cancel the invoice
        $this->patchJson("/api/invoices/{$invoice->id}/status", [
            'status' => InvoiceStatus::CANCELLED,
        ])->assertStatus(200);

        $invoice->refresh();

        $debitTotal  = (float) $invoice->ledger_debit_total;
        $creditTotal = (float) $invoice->ledger_credit_total;

        // Cancelled invoice: balance_due = 0
        $this->assertEquals(0.00, (float) $invoice->balance_due, 'Cancelled invoice must have balance_due = 0');

        // debit = 1000 (invoice_created)
        // credit = 400 (payment_applied) + 600 (cancellation) = 1000
        $this->assertEquals(1000.00, $debitTotal,  'Debit total must be 1000');
        $this->assertEquals(1000.00, $creditTotal, 'Credit total must be 1000 (400 payment + 600 cancellation)');

        // The cancellation entry specifically must be 600, NOT 1000
        $cancEntry = LedgerEntry::where('invoice_id', $invoice->id)
            ->where('type', 'cancellation')
            ->first();

        $this->assertNotNull($cancEntry, 'A cancellation ledger entry must exist');
        $this->assertEquals(600.00, (float) $cancEntry->amount,
            'CRITICAL-1: Cancellation credit must be 600 (total - already_paid), not 1000');

        // Reconciliation: debit - credit == balance_due
        $this->assertEquals(
            round($debitTotal - $creditTotal, 2),
            round((float) $invoice->balance_due, 2),
            'Ledger must reconcile after partial-payment cancellation'
        );
    }

    /** @test */
    public function cancellation_of_unpaid_invoice_credits_full_amount()
    {
        // Unpaid invoice cancelled — full amount should be credited
        $invoice = $this->bootstrapInvoice(500.00);

        $this->patchJson("/api/invoices/{$invoice->id}/status", [
            'status' => InvoiceStatus::CANCELLED,
        ])->assertStatus(200);

        $invoice->refresh();

        $cancEntry = LedgerEntry::where('invoice_id', $invoice->id)
            ->where('type', 'cancellation')
            ->first();

        $this->assertNotNull($cancEntry);
        $this->assertEquals(500.00, (float) $cancEntry->amount,
            'Unpaid invoice cancellation must credit the full 500');
        $this->assertEquals(500.00, (float) $invoice->ledger_debit_total);
        $this->assertEquals(500.00, (float) $invoice->ledger_credit_total);
        $this->assertEquals(0.00,   round((float) $invoice->ledger_debit_total - (float) $invoice->ledger_credit_total, 2));
    }

    // =========================================================================
    // CRITICAL-2: Status→PAID must record payment_applied and reconcile
    // =========================================================================

    /** @test */
    public function status_toggle_to_paid_records_payment_applied_and_reconciles()
    {
        $invoice = $this->bootstrapInvoice(1000.00);

        // Toggle status to PAID directly (no applyPayment call)
        $this->patchJson("/api/invoices/{$invoice->id}/status", [
            'status' => InvoiceStatus::PAID,
        ])->assertStatus(200);

        $invoice->refresh();

        $debitTotal  = (float) $invoice->ledger_debit_total;
        $creditTotal = (float) $invoice->ledger_credit_total;

        $this->assertEquals(InvoiceStatus::PAID, $invoice->status);
        $this->assertEquals(1000.00, (float) $invoice->paid_amount);
        $this->assertEquals(0.00,    (float) $invoice->balance_due);

        // debit == credit == 1000
        $this->assertEquals(1000.00, $debitTotal,  'Debit total must be 1000');
        $this->assertEquals(1000.00, $creditTotal, 'CRITICAL-2: Credit must equal 1000 after status toggle to PAID');

        // A payment_applied entry must exist
        $payEntry = LedgerEntry::where('invoice_id', $invoice->id)
            ->where('type', 'payment_applied')
            ->first();
        $this->assertNotNull($payEntry, 'CRITICAL-2: payment_applied ledger entry must be created on status→PAID');
        $this->assertEquals(1000.00, (float) $payEntry->amount,
            'The payment_applied entry must cover the full balance (1000)');

        // Reconciliation
        $this->assertEquals(
            round($debitTotal - $creditTotal, 2),
            round((float) $invoice->balance_due, 2),
            'Ledger must reconcile after status toggle to PAID'
        );
    }

    /** @test */
    public function status_toggle_to_paid_after_partial_payment_reconciles()
    {
        $invoice = $this->bootstrapInvoice(1000.00);

        // Apply partial payment 600 via the endpoint
        $this->postJson("/api/invoices/{$invoice->id}/apply-payment", [
            'payment_amount'  => 600.00,
            'payment_method'  => 'Card',
            'idempotency_key' => 'pay-c2-partial-' . $invoice->id,
        ])->assertStatus(200);

        $invoice->refresh();
        $this->assertEquals(600.00, (float) $invoice->paid_amount);

        // Now toggle to PAID (remaining 400 should be credited)
        $this->patchJson("/api/invoices/{$invoice->id}/status", [
            'status' => InvoiceStatus::PAID,
        ])->assertStatus(200);

        $invoice->refresh();

        $debitTotal  = (float) $invoice->ledger_debit_total;
        $creditTotal = (float) $invoice->ledger_credit_total;

        $this->assertEquals(1000.00, $debitTotal,  'Debit total must be 1000');
        $this->assertEquals(1000.00, $creditTotal, 'Credit must be 1000 (600 partial + 400 from status toggle)');
        $this->assertEquals(0.00, (float) $invoice->balance_due);
        $this->assertEquals(
            round($debitTotal - $creditTotal, 2),
            round((float) $invoice->balance_due, 2),
            'Ledger must reconcile after partial payment + status toggle to PAID'
        );
    }

    // =========================================================================
    // CRITICAL-3: unit_cost propagates through Treatment → InvoiceItem
    // =========================================================================

    /** @test */
    public function treatment_unit_cost_is_persisted_and_propagates_to_invoice_item()
    {
        // Create master medicine + inventory with a batch
        $master = MasterMedicine::create([
            'name'                   => 'Amoxicillin 500mg',
            'is_active'              => true,
            'category'               => 'Antibiotic',
            'unit'                   => 'tablet',
            'default_purchase_price' => 50.00,
            'default_selling_price'  => 80.00,
        ]);

        $inventory = Inventory::forceCreate([
            'doctor_id'          => $this->doctor->id,
            'master_medicine_id' => $master->id,
            'item_name'          => $master->name,
            'stock'              => 100,
            'reorder_level'      => 10,
            'purchase_cost'      => 50.00,
            'sale_price'         => 80.00,
        ]);

        InventoryBatch::forceCreate([
            'inventory_id'       => $inventory->id,
            'doctor_id'          => $this->doctor->id,
            'original_quantity'  => 100,
            'quantity_remaining' => 100,
            'unit_cost'          => 50.00,
            'purchase_reference' => \Illuminate\Support\Str::uuid(),
        ]);

        // Create treatment with unit_cost = 50 (was silently dropped before this fix)
        $treatment = Treatment::create([
            'patient_id'     => $this->patient->id,
            'inventory_id'   => $inventory->id,
            'procedure_name' => 'Amoxicillin 500mg',
            'status'         => 'Completed',
            'fee'            => 80.00,
            'quantity'       => 2,
            'unit_cost'      => 50.00,
        ]);

        // CRITICAL-3: Verify unit_cost was persisted (not dropped by mass assignment)
        $this->assertEquals(50.00, (float) $treatment->fresh()->unit_cost,
            'CRITICAL-3: unit_cost must be persisted on Treatment — was silently dropped due to missing $fillable entry');

        // Generate invoice from this treatment
        $response = $this->postJson('/api/invoices', [
            'patient_id'    => $this->patient->id,
            'treatment_ids' => [$treatment->id],
        ])->assertStatus(201);

        $invoiceId = $response->json('id');
        $invoice   = Invoice::with('items')->find($invoiceId);

        // The Medicine invoice item should carry unit_cost = 50 (FIFO effective cost)
        $medicineItem = $invoice->items->where('type', 'Medicine')->first();
        $this->assertNotNull($medicineItem, 'A Medicine invoice item must exist');
        $this->assertGreaterThan(0.00, (float) $medicineItem->unit_cost,
            'CRITICAL-3: InvoiceItem.unit_cost must be > 0 for COGS to be non-zero');
        $this->assertEquals(50.00, (float) $medicineItem->unit_cost,
            'InvoiceItem.unit_cost must match the batch unit cost (50)');
    }

    // =========================================================================
    // CRITICAL-4: approveReallocation must enforce doctor_id ownership
    // =========================================================================

    /** @test */
    public function cross_tenant_reallocation_approval_is_blocked_with_403()
    {
        // Create Doctor B with their own patient + invoice
        $doctorB = User::create([
            'name'         => 'Dr. Beta',
            'email'        => 'beta@test.com',
            'password'     => bcrypt('password'),
            'role'         => 'doctor',
            'specialty_id' => $this->specialty->id,
            'plan_id'      => $this->plan->id,
        ]);

        // Create invoice belonging to doctorB (with status REALLOCATION_REQUIRED)
        $invoiceB = Invoice::withoutEvents(fn () => Invoice::forceCreate([
            'patient_id'            => $this->patient->id, // same patient is OK for test isolation
            'doctor_id'             => $doctorB->id,       // owned by Doctor B
            'total_amount'          => 500.00,
            'paid_amount'           => 0.00,
            'balance_due'           => 500.00,
            'status'                => InvoiceStatus::REALLOCATION_REQUIRED,
            'requires_reallocation' => true,
            'reallocation_token'    => \Illuminate\Support\Str::uuid(),
            'ledger_debit_total'    => 0.00,
            'ledger_credit_total'   => 0.00,
        ]));

        // Doctor A (currently authenticated) attempts to approve Doctor B's invoice
        // This must be rejected with 403
        $response = $this->postJson("/api/invoices/{$invoiceB->id}/approve-reallocation");

        $response->assertStatus(403);
        $this->assertEquals('Unauthorized', $response->json('message'),
            'CRITICAL-4: Cross-tenant reallocation approval must be blocked with 403');
    }
}
