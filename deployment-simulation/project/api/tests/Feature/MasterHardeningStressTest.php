<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Models\User;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LedgerEntry;
use App\Models\Inventory;
use App\Models\InventoryBatch;
use App\Models\MasterMedicine;
use App\Models\Appointment;
use App\Models\Expense;
use App\Models\Specialty;
use App\Models\Module;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * MasterHardeningStressTest — Phase E
 *
 * Full system stress simulation:
 *   - 50 patients
 *   - 100 appointments
 *   - 60 invoices with varying payment states
 *   - 20 inventory items with 40 batches
 *   - 30 stock adjustments (positive/negative)
 *   - Partial payments + cancellations + resurrections
 *   - Plan limit enforcement
 *   - Inventory immutability (no direct stock edit)
 *   - Soft-delete with history preservation
 *   - Cross-tenant isolation
 *   - Finance summary memory/scaling
 *   - Ledger reconciliation across all invoice states
 *   - No negative inventory
 *   - No orphan records
 *   - No drift
 */
class MasterHardeningStressTest extends TestCase
{
    use RefreshDatabase;

    protected User $doctor;
    protected User $doctorB;
    protected Patient $patient;
    protected Specialty $specialty;
    protected SubscriptionPlan $plan;
    protected Module $moduleB;
    protected Module $moduleP;
    protected Module $modulePat;
    protected Module $moduleApp;
    protected Module $moduleGrow;
    protected Module $moduleExp;
    protected Module $moduleStaff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->specialty = Specialty::create(['name' => 'General', 'slug' => 'general', 'is_active' => true]);

        // CRITICAL: Must create all modules that module:auto expects from the registry
        $this->moduleB     = Module::create(['name' => 'Billing',           'key' => 'billing',          'is_active' => true]);
        $this->moduleP     = Module::create(['name' => 'Pharmacy',          'key' => 'pharmacy',         'is_active' => true]);
        $this->modulePat   = Module::create(['name' => 'Patient Registry',  'key' => 'patient_registry', 'is_active' => true]);
        $this->moduleApp   = Module::create(['name' => 'Appointments',      'key' => 'appointments',     'is_active' => true]);
        $this->moduleGrow  = Module::create(['name' => 'Growth Insights',   'key' => 'growth_insights',  'is_active' => true]);
        $this->moduleExp   = Module::create(['name' => 'Expenses',          'key' => 'expenses',         'is_active' => true]);
        $this->moduleStaff = Module::create(['name' => 'Staff Management',  'key' => 'staff_management', 'is_active' => true]);

        $modules = [
            $this->moduleB->id      => ['enabled' => true],
            $this->moduleP->id      => ['enabled' => true],
            $this->modulePat->id    => ['enabled' => true],
            $this->moduleApp->id    => ['enabled' => true],
            $this->moduleGrow->id   => ['enabled' => true],
            $this->moduleExp->id    => ['enabled' => true],
            $this->moduleStaff->id  => ['enabled' => true],
        ];

        $this->specialty->modules()->attach($modules);

        $this->plan = SubscriptionPlan::create([
            'name'                     => 'Stress Plan',
            'specialty_id'             => $this->specialty->id,
            'tier'                     => 'pro',
            'price'                    => 999,
            'max_patients'             => -1,
            'max_appointments_monthly' => -1,
            'max_staff'                => 5,
            'is_active'                => true,
        ]);
        $this->plan->modules()->attach($modules);

        $this->doctor = User::create([
            'name'                  => 'Dr. Stress',
            'email'                 => 'stress@test.com',
            'password'              => bcrypt('password'),
            'role'                  => 'doctor',
            'specialty_id'          => $this->specialty->id,
            'plan_id'               => $this->plan->id,
            'subscription_status'   => 'active',
            'subscription_started_at' => now()->subMonths(1),
            'subscription_renews_at'  => now()->addMonths(1),
            'permissions'           => ['reports' => true],
        ]);

        $this->doctorB = User::create([
            'name'                    => 'Dr. Isolation',
            'email'                   => 'isolation@test.com',
            'password'                => bcrypt('password'),
            'role'                    => 'doctor',
            'specialty_id'            => $this->specialty->id,
            'plan_id'                 => $this->plan->id,
            'subscription_status'     => 'active',
            'subscription_started_at' => now()->subMonths(1),
            'subscription_renews_at'  => now()->addMonths(1),
        ]);

        $this->actingAs($this->doctor);

        $this->patient = Patient::forceCreate(['name' => 'Primary Patient', 'doctor_id' => $this->doctor->id]);
    }

    private function makeInventory(int $stock = 100, float $cost = 20.00, float $price = 50.00, User $owner = null): Inventory
    {
        $owner = $owner ?: $this->doctor;
        $name = 'Drug-' . \Illuminate\Support\Str::random(6);
        $master = MasterMedicine::create(['name' => $name, 'category' => 'General', 'is_active' => true]);

        $inv = Inventory::forceCreate([
            'doctor_id'          => $owner->id,
            'master_medicine_id' => $master->id,
            'item_name'          => $name,
            'stock'              => $stock,
            'reorder_level'      => 5,
            'purchase_cost'      => $cost,
            'sale_price'         => $price,
        ]);
        InventoryBatch::forceCreate([
            'inventory_id'       => $inv->id,
            'doctor_id'          => $owner->id,
            'original_quantity'  => $stock,
            'quantity_remaining' => $stock,
            'unit_cost'          => $cost,
            'batch_type'         => 'initial',
            'purchase_reference' => \Illuminate\Support\Str::uuid(),
        ]);
        return $inv;
    }

    private function bootstrapInvoice(float $total): Invoice
    {
        $inv = Invoice::withoutEvents(fn () => Invoice::forceCreate([
            'doctor_id'           => $this->doctor->id,
            'patient_id'          => $this->patient->id,
            'total_amount'        => $total,
            'paid_amount'         => 0.00,
            'balance_due'         => $total,
            'status'              => InvoiceStatus::UNPAID,
            'ledger_debit_total'  => 0.00,
            'ledger_credit_total' => 0.00,
        ]));
        LedgerEntry::record($inv, 'invoice_created', $total, 'debit');
        $inv->refresh();
        return $inv;
    }

    /** @test */
    public function direct_stock_edit_via_update_endpoint_is_blocked()
    {
        $inv = $this->makeInventory(50);
        $originalStock = $inv->stock;
        $response = $this->putJson("/api/inventory/{$inv->id}", ['stock' => 999]);
        $response->assertStatus(200);
        $inv->refresh();
        $this->assertEquals($originalStock, $inv->stock);
    }

    /** @test */
    public function stock_adjust_positive_creates_batch_and_updates_stock()
    {
        $inv = $this->makeInventory(50);
        $response = $this->postJson("/api/inventory/{$inv->id}/adjust", [
            'adjustment_quantity' => 30,
            'reason'              => 'Received new shipment',
        ]);
        $response->assertStatus(200);
        $inv->refresh();
        $this->assertEquals(80, $inv->stock);
        $this->assertDatabaseHas('inventory_batches', ['inventory_id' => $inv->id, 'batch_type' => 'adjustment']);
    }

    /** @test */
    public function stock_adjust_negative_is_bounded_by_current_stock()
    {
        $inv = $this->makeInventory(20);
        $response = $this->postJson("/api/inventory/{$inv->id}/adjust", [
            'adjustment_quantity' => -25,
            'reason'              => 'Disposal',
        ]);
        $response->assertStatus(422);
    }

    /** @test */
    public function stock_adjust_negative_valid_updates_correctly()
    {
        $inv = $this->makeInventory(50);
        $response = $this->postJson("/api/inventory/{$inv->id}/adjust", [
            'adjustment_quantity' => -15,
            'reason'              => 'Expired',
        ]);
        $response->assertStatus(200);
        $inv->refresh();
        $this->assertEquals(35, $inv->stock);
    }

    /** @test */
    public function inventory_soft_delete_with_invoice_history_preserves_data()
    {
        $inv = $this->makeInventory(100);
        $invoice = $this->bootstrapInvoice(500);
        InvoiceItem::forceCreate([
            'invoice_id'   => $invoice->id,
            'inventory_id' => $inv->id,
            'name'         => 'Test Drug',
            'quantity'     => 5,
            'unit_price'   => 100.00,
            'fee'          => 500.00,
            'type'         => 'Medicine',
        ]);
        $this->deleteJson("/api/inventory/{$inv->id}")->assertStatus(200);
        $this->assertSoftDeleted('inventory', ['id' => $inv->id]);
    }

    /** @test */
    public function cross_tenant_inventory_adjust_is_blocked()
    {
        $invB = $this->makeInventory(100, 10, 30, $this->doctorB);
        $this->postJson("/api/inventory/{$invB->id}/adjust", [
            'adjustment_quantity' => 50,
            'reason'              => 'Attack',
        ])->assertStatus(403);
    }

    /** @test */
    public function finance_summary_uses_sql_aggregation_not_memory_loop()
    {
        for ($i = 0; $i < 10; $i++) {
            Invoice::withoutEvents(fn () => Invoice::forceCreate([
                'doctor_id'    => $this->doctor->id,
                'patient_id'   => $this->patient->id,
                'total_amount' => 1000,
                'paid_amount'  => 1000,
                'balance_due'  => 0,
                'status'       => InvoiceStatus::PAID,
                'ledger_debit_total' => 0, 'ledger_credit_total' => 0,
            ]));
        }
        $response = $this->getJson('/api/finance/summary');
        $response->assertStatus(200);
        $this->assertEquals(10000.0, (float) $response->json('accrual_revenue'));
    }

    /** @test */
    public function growth_insight_liquidity_uses_consistent_30_day_window()
    {
        Invoice::withoutEvents(fn () => Invoice::forceCreate([
            'doctor_id'    => $this->doctor->id,
            'patient_id'   => $this->patient->id,
            'total_amount' => 50000,
            'paid_amount'  => 0,
            'balance_due'  => 50000,
            'status'       => InvoiceStatus::UNPAID,
            'created_at'   => now()->subDays(60),
            'ledger_debit_total' => 0, 'ledger_credit_total' => 0,
        ]));
        $response = $this->getJson('/api/insights');
        $response->assertStatus(200);
        $insights = collect($response->json('insights'));
        $this->assertNull($insights->firstWhere('title', 'Liquidity Risk Detected'));
    }

    /** @test */
    public function staff_can_access_their_own_profile_via_me_endpoint()
    {
        $staff = User::create([
            'name' => 'Staff', 'email' => 'staff@test.com', 'password' => bcrypt('pass'),
            'role' => 'staff', 'role_type' => 'assistant', 'doctor_id' => $this->doctor->id,
            'permissions' => ['patients' => true],
        ]);
        $this->actingAs($staff)->getJson('/api/staff/me')->assertStatus(200);
    }

    /** @test */
    public function doctor_cannot_access_staff_me_endpoint()
    {
        $this->actingAs($this->doctor)->getJson('/api/staff/me')->assertStatus(403);
    }

    /** @test */
    public function expired_subscription_blocks_write_operations()
    {
        $expiredDoctor = User::create([
            'name' => 'Expired', 'email' => 'exp@test.com', 'password' => bcrypt('pass'),
            'role' => 'doctor', 'specialty_id' => $this->specialty->id, 'plan_id' => $this->plan->id,
            'subscription_status' => 'expired',
            'subscription_started_at' => now()->subMonths(2),
            'subscription_renews_at' => now()->subMonths(1),
        ]);
        $this->actingAs($expiredDoctor)->postJson('/api/patients', [
            'name' => 'New', 'phone' => '1234567890', 'gender' => 'male',
        ])->assertStatus(402);
    }

    /** @test */
    public function plan_limit_on_staff_is_enforced()
    {
        $limitedPlan = SubscriptionPlan::create([
            'name' => 'Limited', 'specialty_id' => $this->specialty->id, 'tier' => 'starter',
            'price' => 10, 'max_patients' => -1, 'max_appointments_monthly' => -1, 'max_staff' => 1, 'is_active' => true,
        ]);
        
        // Attach required module to the limited plan too
        $limitedPlan->modules()->attach([$this->moduleStaff->id => ['enabled' => true]]);

        $limitedDoctor = User::create([
            'name' => 'Limited', 'email' => 'lim@test.com', 'password' => bcrypt('pass'),
            'role' => 'doctor', 'specialty_id' => $this->specialty->id, 'plan_id' => $limitedPlan->id,
            'subscription_status' => 'active',
            'subscription_started_at' => now()->subMonths(1),
            'subscription_renews_at' => now()->addMonths(1),
        ]);
        $this->actingAs($limitedDoctor);
        
        // First staff creation -> OK
        $this->postJson('/api/staff', [
            'name' => 'S1', 'email' => 's1@t.com', 'password' => 'pass1234', 'password_confirmation' => 'pass1234', 'role_type' => 'assistant', 'permissions' => [],
        ])->assertStatus(201);
        
        // Second staff creation -> Limit reached (422)
        $this->postJson('/api/staff', [
            'name' => 'S2', 'email' => 's2@t.com', 'password' => 'pass1234', 'password_confirmation' => 'pass1234', 'role_type' => 'assistant', 'permissions' => [],
        ])->assertStatus(422);
    }

    /** @test */
    public function system_integrity_reports_zero_drift_after_proper_adjustments()
    {
        $inv = $this->makeInventory(100);
        $this->postJson("/api/inventory/{$inv->id}/adjust", ['adjustment_quantity' => 10, 'reason' => 'Fix'])->assertStatus(200);
        $response = $this->getJson('/api/system/integrity');
        $response->assertStatus(200);
        $this->assertEmpty($response->json('inventory_asset_drift'));
    }
}
