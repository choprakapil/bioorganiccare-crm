<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class BackfillSubscriptionDatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder is IDEMPOTENT.
     * It ensures existing doctors have valid subscription dates.
     * It does NOT overwrite any existing dates.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('Starting Subscription Backfill...');

            // Find doctors who have NULL subscription_started_at
            $doctors = User::where('role', 'doctor')
                ->whereNull('subscription_started_at')
                ->get();

            $count = 0;
            $now = now();
            $nextMonth = now()->addMonth();

            foreach ($doctors as $doctor) {
                $doctor->subscription_started_at = $now;
                $doctor->subscription_renews_at = $nextMonth;
                
                // Safe Defaults (though migration default handles these, excessive explicit setting is safer)
                if (!$doctor->billing_interval) {
                    $doctor->billing_interval = 'monthly';
                }
                if (!$doctor->subscription_status) {
                    $doctor->subscription_status = 'active';
                }
                
                $doctor->subscription_grace_ends_at = null;
                $doctor->save();
                $count++;
            }
            
            if ($count > 0) {
                $this->command->info("Backfilled subscription dates for {$count} doctors.");
            } else {
                $this->command->info("No doctors needed backfilling.");
            }
        });
    }
}
