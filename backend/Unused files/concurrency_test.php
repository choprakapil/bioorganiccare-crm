<?php

use App\Models\Inventory;
use App\Models\InventoryBatch;
use App\Models\Treatment;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Auth;

$inventory = Inventory::first();
if (!$inventory) { echo "NO_INV\n"; return; }

$doctorId = $inventory->doctor_id;
Auth::loginUsingId($doctorId);

$patient = \App\Models\Patient::where('doctor_id',$doctorId)->first();
if (!$patient) { echo "NO_PATIENT\n"; return; }

// Ensure enough stock for 20 deductions
if ($inventory->stock < 100) {
    InventoryBatch::create([
        'inventory_id' => $inventory->id,
        'doctor_id' => $doctorId,
        'original_quantity' => 200,
        'quantity_remaining' => 200,
        'unit_cost' => 5,
        'purchase_reference' => 'CONC_STOCK_' . uniqid()
    ]);
    $inventory->increment('stock', 200);
}

$processes = [];

for ($i=0; $i<20; $i++) {
    $processes[] = function() use ($inventory, $patient, $doctorId) {
        try {

            $t = Treatment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctorId,
                'inventory_id' => $inventory->id,
                'procedure_name' => 'CONCURRENCY',
                'status' => 'Completed',
                'fee' => 5,
                'quantity' => 1
            ]);

            (new InvoiceService())->createFromTreatments([
                'doctor_id' => $doctorId,
                'patient_id' => $patient->id,
                'treatment_ids' => [$t->id],
                'status' => 'Unpaid'
            ]);

        } catch (\Throwable $e) {
            echo "ERR: ".$e->getMessage()."\n";
        }
    };
}

foreach ($processes as $p) {
    $p();
}

echo "CONCURRENCY_DONE\n";
