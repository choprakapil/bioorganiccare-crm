<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LedgerEntry;
use App\Models\Inventory;
use App\Enums\InvoiceStatus;
use Illuminate\Support\Facades\DB;

class EnterpriseScalingSeeder extends Seeder
{
    public function run()
    {
        $doctor = User::where('role', 'doctor')->first();
        if (!$doctor) {
            echo "No doctor found. Run standard seeder first.\n";
            return;
        }

        $patient = Patient::where('doctor_id', $doctor->id)->first() ?: Patient::factory()->create(['doctor_id' => $doctor->id]);

        echo "Seeding 10,000 invoices for Doctor ID: {$doctor->id}...\n";

        $count = 10000;
        $batchSize = 500;

        for ($i = 0; $i < ($count / $batchSize); $i++) {
            DB::transaction(function () use ($doctor, $patient, $batchSize) {
                $invoices = [];
                for ($j = 0; $j < $batchSize; $j++) {
                    $total = rand(100, 5000);
                    $paid = rand(0, $total);
                    $status = $paid == $total ? InvoiceStatus::PAID : ($paid > 0 ? InvoiceStatus::PARTIAL : InvoiceStatus::UNPAID);
                    
                    $invoices[] = [
                        'doctor_id' => $doctor->id,
                        'patient_id' => $patient->id,
                        'total_amount' => $total,
                        'paid_amount' => $paid,
                        'balance_due' => $total - $paid,
                        'status' => $status,
                        'is_finalized' => true,
                        'created_at' => now()->subDays(rand(0, 90)),
                        'updated_at' => now(),
                    ];
                }
                DB::table('invoices')->insert($invoices);
                echo ".";
            });
        }

        echo "\nDone. 10,000 invoices seeded.\n";
    }
}
