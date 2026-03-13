<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Inventory;
use App\Models\InventoryBatch;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function randy($min, $max)
    {
        return rand($min, $max);
    }

    public function run(): void
    {
        $doctor = User::where('role', 'doctor')->first();
        if (!$doctor) {
            $this->command->error("No doctor found. Factory reset successful but could not seed demo data.");
            return;
        }

        $specialtyId = DB::table('specialties')->first()->id ?? 1;

        $patientsData = [
            ['name' => 'John Test', 'phone' => '1112223330'],
            ['name' => 'Mary Sample', 'phone' => '1112223331'],
            ['name' => 'Robert Demo', 'phone' => '1112223332'],
            ['name' => 'Alice Demo', 'phone' => '1112223333'],
            ['name' => 'James Demo', 'phone' => '1112223334'],
            ['name' => 'William Demo', 'phone' => '1112223335'],
            ['name' => 'Olivia Demo', 'phone' => '1112223336'],
            ['name' => 'Sophia Demo', 'phone' => '1112223337'],
            ['name' => 'Benjamin Demo', 'phone' => '1112223338'],
            ['name' => 'Emily Demo', 'phone' => '1112223339']
        ];

        $patientIds = [];
        foreach ($patientsData as $p) {
            $patient = Patient::create([
                'doctor_id' => $doctor->id,
                'name' => $p['name'],
                'phone' => $p['phone'],
                'age' => rand(20, 60),
                'gender' => rand(0, 1) ? 'Male' : 'Female',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $patientIds[] = $patient->id;
        }

        $medicinesData = ['Paracetamol', 'Amoxicillin', 'Ibuprofen', 'Aspirin', 'Vitamin C'];
        $medicineIds = [];
        
        foreach ($medicinesData as $med) {
            $masterId = DB::table('master_medicines')->insertGetId([
                'specialty_id' => $specialtyId,
                'name' => $med,
                'normalized_name' => strtolower($med),
                'default_purchase_price' => rand(10, 50),
                'default_selling_price' => rand(60, 100),
                'category' => 'Demo',
                'unit' => 'Tablet',
                'is_active' => true,
                'created_by_user_id' => $doctor->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $purchaseCost = rand(10, 50);
            
            $invId = DB::table('inventory')->insertGetId([
                'doctor_id' => $doctor->id,
                'master_medicine_id' => $masterId,
                'item_name' => $med,
                'stock' => 50,
                'reorder_level' => 10,
                'purchase_cost' => $purchaseCost,
                'sale_price' => rand(60, 100),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            DB::table('inventory_batches')->insert([
                'inventory_id' => $invId,
                'doctor_id' => $doctor->id,
                'original_quantity' => 50,
                'quantity_remaining' => 50,
                'unit_cost' => $purchaseCost,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $medicineIds[] = ['inv_id' => $invId, 'name' => $med, 'cost' => $purchaseCost];
        }

        for ($i = 0; $i < 20; $i++) {
            Treatment::create([
                'doctor_id' => $doctor->id,
                'patient_id' => $patientIds[array_rand($patientIds)],
                'procedure_name' => 'Demo Checkup ' . ($i+1),
                'fee' => rand(100, 500),
                'status' => 'Completed',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        for ($i = 0; $i < 10; $i++) {
            $invTotal = rand(200, 1000);
            $invoice = Invoice::create([
                'doctor_id' => $doctor->id,
                'patient_id' => $patientIds[array_rand($patientIds)],
                'total_amount' => $invTotal,
                'paid_amount' => $invTotal,
                'status' => 'Paid',
                'payment_method' => 'Cash',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'name' => 'Demo Procedure',
                'type' => 'Procedure',
                'quantity' => 1,
                'unit_price' => $invTotal,
                'fee' => $invTotal,
                'created_at' => now()
            ]);
        }
    }
}
