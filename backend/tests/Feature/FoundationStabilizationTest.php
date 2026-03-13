<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LedgerEntry;
use App\Models\Module;
use App\Models\Patient;
use App\Models\Specialty;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FoundationStabilizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $doctor;
    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Cache::flush();

        $specialty = Specialty::create(['name' => 'General', 'slug' => 'gen', 'is_active' => true]);
        $module = Module::create(['name' => 'Patients', 'key' => 'patient_registry', 'is_active' => true]);
        $specialty->modules()->attach($module->id, ['enabled' => true]);

        $plan = SubscriptionPlan::create([
            'name' => 'Pro', 'specialty_id' => $specialty->id, 'tier' => 'pro',
            'is_active' => true, 'max_patients' => -1
        ]);

        $this->doctor = User::create([
            'name' => 'Dr. Owner', 'email' => 'owner@test.com', 'password' => bcrypt('pass'),
            'role' => 'doctor', 'specialty_id' => $specialty->id, 'plan_id' => $plan->id,
            'subscription_status' => 'active', 'subscription_started_at' => now(), 'subscription_renews_at' => now()->addYear()
        ]);

        // Authenticate before creating seeded data so BelongsToTenancy trait fires
        $this->actingAs($this->doctor);

        $this->patient = Patient::create([
            'name' => 'Test Patient',
            'phone' => '9999999999',
        ]);
    }

    // ──────────────────────────────────────────
    // PHASE 1 — LEDGER / INVOICE IMMUTABILITY
    // ──────────────────────────────────────────

    /** @test */
    public function deleting_invoice_with_ledger_entries_is_blocked()
    {
        $invoice = Invoice::create([
            'doctor_id'        => $this->doctor->id,
            'patient_id'       => $this->patient->id,
            'total_amount'     => 500,
            'paid_amount'      => 500,
            'balance_due'      => 0,
            'status'           => 'Paid',
            'ledger_debit_total'  => 500,
            'ledger_credit_total' => 500,
        ]);

        // Create a ledger entry via the guarded record() method
        LedgerEntry::record($invoice, 'invoice_created', 500, 'debit', []);
        LedgerEntry::record($invoice, 'payment_applied', 500, 'credit', []);

        $ledgerCount = LedgerEntry::where('invoice_id', $invoice->id)->count();
        $this->assertEquals(2, $ledgerCount, 'Ledger entries should exist before deletion attempt.');

        // Attempt to delete invoice — should be blocked by DB RESTRICT constraint
        $this->expectException(\Illuminate\Database\QueryException::class);
        $invoice->delete();
    }

    /** @test */
    public function ledger_entries_remain_intact_after_failed_invoice_deletion()
    {
        $invoice = Invoice::create([
            'doctor_id'        => $this->doctor->id,
            'patient_id'       => $this->patient->id,
            'total_amount'     => 200,
            'paid_amount'      => 200,
            'balance_due'      => 0,
            'status'           => 'Paid',
            'ledger_debit_total'  => 200,
            'ledger_credit_total' => 200,
        ]);

        LedgerEntry::record($invoice, 'invoice_created', 200, 'debit', []);
        LedgerEntry::record($invoice, 'payment_applied', 200, 'credit', []);

        try {
            $invoice->delete();
        } catch (\Exception $e) {
            // Expected
        }

        // Entries must still be there
        $this->assertEquals(2, LedgerEntry::where('invoice_id', $invoice->id)->count());
        // Invoice must still exist
        $this->assertNotNull(Invoice::find($invoice->id));
    }

    // ──────────────────────────────────────────
    // PHASE 2 — GLOBAL PAGINATION
    // ──────────────────────────────────────────

    /** @test */
    public function invoice_index_is_paginated_and_items_not_loaded()
    {
        for ($i = 1; $i <= 25; $i++) {
            Invoice::create([
                'doctor_id' => $this->doctor->id,
                'patient_id' => $this->patient->id,
                'total_amount' => $i * 100,
                'paid_amount'  => 0,
                'balance_due'  => $i * 100,
                'status'       => 'Unpaid',
            ]);
        }

        $this->actingAs($this->doctor);
        $response = $this->withoutMiddleware()->getJson('/api/invoices');

        $response->assertStatus(200);
        // Enterprise Scaling: Cursor Pagination Response Structure
        $response->assertJsonStructure(['data', 'path', 'per_page', 'next_cursor']);
        $this->assertCount(20, $response->json('data'));

        // Assert items are NOT pre-loaded
        $firstInvoice = $response->json('data.0');
        $this->assertArrayNotHasKey('items', $firstInvoice);
    }

    /** @test */
    public function appointment_index_is_paginated()
    {
        for ($i = 1; $i <= 25; $i++) {
            \App\Models\Appointment::create([
                'doctor_id'        => $this->doctor->id,
                'patient_id'       => $this->patient->id,
                'appointment_date' => now()->addDays($i),
                'status'           => 'Scheduled',
            ]);
        }

        $this->actingAs($this->doctor);
        $response = $this->withoutMiddleware()->getJson('/api/appointments');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'current_page', 'last_page', 'total']);
        $this->assertCount(20, $response->json('data'));
        $this->assertEquals(25, $response->json('total'));
    }

    /** @test */
    public function inventory_index_is_paginated()
    {
        // Create a master medicine to satisfy the NOT NULL constraint
        $master = \App\Models\MasterMedicine::create([
            'name'                  => 'Master Drug',
            'is_active'             => true,
            'category'              => 'General',
            'unit'                  => 'Unit',
            'default_purchase_price'=> 50,
            'default_selling_price' => 80,
        ]);

        for ($i = 1; $i <= 25; $i++) {
            $master = \App\Models\MasterMedicine::create([
                'name'                   => "Drug {$i}",
                'is_active'              => true,
                'category'               => 'General',
                'unit'                   => 'Unit',
                'default_purchase_price' => 50,
                'default_selling_price'  => 80,
            ]);
            \App\Models\Inventory::create([
                'item_name'          => "Item {$i}",
                'master_medicine_id' => $master->id,
                'stock'              => 10,
                'reorder_level'      => 5,
                'purchase_cost'      => 50,
                'sale_price'         => 80,
            ]);
        }

        $response = $this->withoutMiddleware()->getJson('/api/inventory');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'current_page', 'last_page', 'total']);
        $this->assertCount(20, $response->json('data'));
        $this->assertEquals(25, $response->json('total'));
    }

    /** @test */
    public function expense_index_is_paginated()
    {
        for ($i = 1; $i <= 25; $i++) {
            \App\Models\Expense::create([
                'doctor_id'    => $this->doctor->id,
                'category'     => 'Supplies',
                'amount'       => $i * 10,
                'expense_date' => now()->subDays($i),
            ]);
        }

        $this->actingAs($this->doctor);
        $response = $this->withoutMiddleware()->getJson('/api/expenses');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'current_page', 'last_page', 'total']);
        $this->assertCount(20, $response->json('data'));
        $this->assertEquals(25, $response->json('total'));
    }

    // ──────────────────────────────────────────
    // PHASE 3 — SOFT DELETE EXPOSURE AUDIT
    // ──────────────────────────────────────────

    /** @test */
    public function soft_deleted_patients_not_returned_in_index()
    {
        $this->actingAs($this->doctor);

        $active  = Patient::create(['doctor_id' => $this->doctor->id, 'name' => 'Active', 'phone' => '1111111111']);
        $deleted = Patient::create(['doctor_id' => $this->doctor->id, 'name' => 'Deleted', 'phone' => '2222222222']);
        $deleted->delete(); // soft delete

        $response = $this->withoutMiddleware()->getJson('/api/patients');
        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name')->toArray();
        $this->assertContains('Active', $names);
        $this->assertNotContains('Deleted', $names);
    }

    /** @test */
    public function full_endpoint_does_not_expose_soft_deleted_patient()
    {
        $this->actingAs($this->doctor);

        $deleted = Patient::create(['doctor_id' => $this->doctor->id, 'name' => 'Ghost', 'phone' => '3333333333']);
        $deletedId = $deleted->id;
        $deleted->delete(); // soft delete

        $response = $this->withoutMiddleware()->getJson("/api/patients/{$deletedId}/full");

        // Should be 404 because soft-deleted patients are excluded from standard queries
        $response->assertStatus(404);
    }

    /** @test */
    public function restore_route_is_scoped_to_current_doctor()
    {
        $specialty2 = Specialty::create(['name' => 'Other', 'slug' => 'other', 'is_active' => true]);
        $otherDoctor = User::create([
            'name' => 'Other Dr.', 'email' => 'other@test.com', 'password' => bcrypt('pass'),
            'role' => 'doctor', 'specialty_id' => $specialty2->id,
        ]);
        // Briefly auth as otherDoctor to create their patient via trait
        $this->actingAs($otherDoctor);
        $foreign = Patient::create(['name' => 'Foreign', 'phone' => '4444444444']);
        $foreign->delete();

        // Switch back to this->doctor and attempt to restore foreign patient
        $this->actingAs($this->doctor);
        // Skip module/subscription middleware but retain auth (controller does manual scoping check)
        $response = $this->withoutMiddleware([
            \App\Http\Middleware\EnforcePlanLimits::class,
            \App\Http\Middleware\EnsureSubscriptionActive::class,
            \App\Http\Middleware\CheckStaffPermissions::class,
            \App\Http\Middleware\CheckRole::class,
        ])->postJson("/api/patients/{$foreign->id}/restore");

        // 404 is the correct secure response: the scoped query makes
        // another doctor's patient invisible, same as not existing.
        $response->assertStatus(404);
    }
}
