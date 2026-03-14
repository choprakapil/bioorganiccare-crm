<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClinicReset extends Command
{
    protected $signature = 'clinic:reset {--doctor= : The ID of the doctor to reset}';
    protected $description = 'Safely reset all seeded financial and clinical data for a specific doctor.';

    public function handle()
    {
        $doctorId = $this->option('doctor');
        if (!$doctorId) {
            $this->error('Doctor ID is required.');
            return 1;
        }

        $this->info("Initiating reset for Doctor #{$doctorId}...");

        DB::transaction(function () use ($doctorId) {
            $deletedAllocations = DB::table('invoice_item_batch_allocations')
                ->whereIn('invoice_item_id', function($query) use ($doctorId) {
                    $query->select('id')->from('invoice_items')
                        ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                        ->where('invoices.doctor_id', $doctorId);
                })->delete();
            $this->line("Deleted {$deletedAllocations} Batch Allocations.");

            $deletedItems = DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->where('invoices.doctor_id', $doctorId)
                ->delete();
            $this->line("Deleted {$deletedItems} Invoice Items.");

            $deletedLedger = DB::table('ledger_entries')->where('doctor_id', $doctorId)->delete();
            $this->line("Deleted {$deletedLedger} Ledger Entries.");

            $deletedTreatments = DB::table('treatments')->where('doctor_id', $doctorId)->delete();
            $this->line("Deleted {$deletedTreatments} Treatments.");

            $deletedExpenses = DB::table('expenses')->where('doctor_id', $doctorId)->delete();
            $this->line("Deleted {$deletedExpenses} Expenses.");

            $deletedBatches = DB::table('inventory_batches')->where('doctor_id', $doctorId)->delete();
            $this->line("Deleted {$deletedBatches} Inventory Batches.");

            $deletedInventory = DB::table('inventory')->where('doctor_id', $doctorId)->delete();
            $this->line("Deleted {$deletedInventory} Inventory Items.");

            $deletedInvoices = DB::table('invoices')->where('doctor_id', $doctorId)->delete();
            $this->line("Deleted {$deletedInvoices} Invoices.");

            $deletedPatients = DB::table('patients')->where('doctor_id', $doctorId)->delete();
            $this->line("Deleted {$deletedPatients} Patients.");
        });

        // Clear Cache
        \App\Services\FinancialCacheService::forgetDoctorFinanceCache($doctorId);

        $this->info("Reset complete for Doctor #{$doctorId}.");
        return 0;
    }
}
