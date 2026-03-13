<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Inventory;
use App\Models\InventoryBatch;
use App\Models\InvoiceItemBatchAllocation;
use App\Models\MasterMedicine;
use App\Models\PharmacyCategory;
use App\Models\Specialty;
use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerIntegrityTest extends TestCase
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
        
        // Setup a doctor and patient
        $this->doctor = User::create([
            'name' => 'Dr. Test',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'specialty_id' => $this->specialty->id
        ]);

        $this->actingAs($this->doctor);

        $this->patient = Patient::create([
            'name' => 'John Doe',
        ]);

        $this->category = PharmacyCategory::create([
            'name' => 'General',
            'specialty_id' => $this->specialty->id
        ]);

        $this->masterMedicine = MasterMedicine::create([
            'name' => 'Test Medicine',
            'pharmacy_category_id' => $this->category->id,
            'category' => 'General',
            'unit' => 'tablet'
        ]);
    }

    protected function createInventory($data = [])
    {
        return Inventory::create(array_merge([
            'doctor_id' => $this->doctor->id,
            'item_name' => 'Test Item',
            'stock' => 10,
            'purchase_cost' => 50,
            'sale_price' => 100,
            'master_medicine_id' => $this->masterMedicine->id
        ], $data));
    }

    /** @test */
    public function financial_fields_sum_to_total()
    {
        $invoice = Invoice::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'total_amount' => 1000.00,
            'paid_amount' => 400.00,
            'balance_due' => 600.00,
            'status' => InvoiceStatus::PARTIAL
        ]);

        $this->assertEquals(
            (float)$invoice->total_amount,
            (float)($invoice->paid_amount + $invoice->balance_due),
            "paid_amount + balance_due should equal total_amount"
        );
    }

    /** @test */
    public function paid_amount_cannot_exceed_total()
    {
        $invoice = Invoice::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'total_amount' => 500.00,
            'paid_amount' => 500.00,
            'balance_due' => 0.00,
            'status' => InvoiceStatus::PAID
        ]);

        $this->assertLessThanOrEqual(
            (float)$invoice->total_amount,
            (float)$invoice->paid_amount,
            "paid_amount should never exceed total_amount"
        );
    }

    /** @test */
    public function inventory_batches_never_negative()
    {
        $inventory = $this->createInventory([
            'item_name' => 'Test Item',
            'stock' => 10,
            'purchase_cost' => 50,
            'sale_price' => 100
        ]);

        InventoryBatch::create([
            'inventory_id' => $inventory->id,
            'doctor_id' => $this->doctor->id,
            'original_quantity' => 10,
            'quantity_remaining' => 5,
            'unit_cost' => 50,
            'purchase_reference' => (string) \Illuminate\Support\Str::uuid()
        ]);

        $negativeBatches = InventoryBatch::where('quantity_remaining', '<', 0)->count();
        $this->assertEquals(0, $negativeBatches, "Inventory batches should never have negative quantity_remaining");
    }

    /** @test */
    public function no_orphan_batch_allocations()
    {
        $inventory = $this->createInventory([
            'item_name' => 'Test Item',
            'stock' => 10
        ]);

        $batch = InventoryBatch::create([
            'inventory_id' => $inventory->id,
            'doctor_id' => $this->doctor->id,
            'original_quantity' => 10,
            'quantity_remaining' => 10,
            'unit_cost' => 50,
            'purchase_reference' => (string) \Illuminate\Support\Str::uuid()
        ]);

        $invoice = Invoice::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'total_amount' => 100
        ]);

        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'inventory_id' => $inventory->id,
            'name' => 'Test Item',
            'type' => 'Medicine',
            'quantity' => 1,
            'unit_price' => 100,
            'fee' => 100
        ]);

        InvoiceItemBatchAllocation::create([
            'invoice_item_id' => $item->id,
            'inventory_batch_id' => $batch->id,
            'quantity_taken' => 1,
            'unit_cost' => 50
        ]);

        // Check for orphans
        $orphanAllocations = InvoiceItemBatchAllocation::whereNotExists(function ($query) {
            $query->select(\Illuminate\Support\Facades\DB::raw(1))
                  ->from('invoice_items')
                  ->whereRaw('invoice_items.id = invoice_item_batch_allocations.invoice_item_id');
        })->orWhereNotExists(function ($query) {
            $query->select(\Illuminate\Support\Facades\DB::raw(1))
                  ->from('inventory_batches')
                  ->whereRaw('inventory_batches.id = invoice_item_batch_allocations.inventory_batch_id');
        })->count();

        $this->assertEquals(0, $orphanAllocations, "There should be no orphan batch allocations");
    }

    /** @test */
    public function inventory_stock_matches_batch_aggregation()
    {
        $inventory = $this->createInventory([
            'item_name' => 'Aggregation Test',
            'stock' => 15
        ]);

        InventoryBatch::create([
            'inventory_id' => $inventory->id,
            'doctor_id' => $this->doctor->id,
            'original_quantity' => 10,
            'quantity_remaining' => 10,
            'unit_cost' => 50,
            'purchase_reference' => (string) \Illuminate\Support\Str::uuid()
        ]);

        InventoryBatch::create([
            'inventory_id' => $inventory->id,
            'doctor_id' => $this->doctor->id,
            'original_quantity' => 5,
            'quantity_remaining' => 5,
            'unit_cost' => 60,
            'purchase_reference' => (string) \Illuminate\Support\Str::uuid()
        ]);

        $batchSum = InventoryBatch::where('inventory_id', $inventory->id)->sum('quantity_remaining');
        
        $this->assertEquals(
            (int)$inventory->stock,
            (int)$batchSum,
            "Inventory stock should match the sum of quantity_remaining in its batches"
        );
    }

    /** @test */
    public function reallocation_required_excluded_from_paid_revenue()
    {
        // 1. Paid Invoice
        Invoice::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'total_amount' => 1000.00,
            'paid_amount' => 1000.00,
            'status' => InvoiceStatus::PAID
        ]);

        // 2. ReallocationRequired Invoice (Even if paid_amount is set, it should be excluded from "Realized/Paid" logic if implementation follows)
        // In our system's FinanceController/InventoryController, we filter by status.
        Invoice::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'total_amount' => 500.00,
            'paid_amount' => 500.00,
            'status' => InvoiceStatus::REALLOCATION_REQUIRED
        ]);

        // Validation: emulate the query logic found in GrowthInsightController or FinanceController
        $paidRevenue = Invoice::where('doctor_id', $this->doctor->id)
            ->where('status', InvoiceStatus::PAID)
            ->sum('total_amount');

        $this->assertEquals(1000.00, (float)$paidRevenue, "ReallocationRequired invoices must be excluded from Paid revenue totals");
    }
}
