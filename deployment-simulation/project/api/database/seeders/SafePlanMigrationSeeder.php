<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Specialty;
use App\Models\SubscriptionPlan;
use App\Models\User;

class SafePlanMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder is IDEMPOTENT.
     * It ensures every Specialty has a Default Basic Plan.
     * It backfills any Doctors with NULL plans.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('Starting Safe Plan Migration...');

            $specialties = Specialty::select('id', 'name', 'default_plan_id')->get();
            $specialtyMap = $specialties->keyBy('id'); // Preload map for backfill

            foreach ($specialties as $specialty) {
                // Ensure plan exists and update default_plan_id in memory/DB
                $plan = $this->ensureBasicPlanForSpecialty($specialty);
            }

            // Using the map (which now has updated default_plan_id from the loop above because objects are passed by reference)
            $this->backfillDoctorPlans($specialtyMap);
            
            $this->command->info('Safe Plan Migration Completed Successfully.');
        });
    }

    /**
     * Ensure a Basic Plan exists for the specialty and set it as default.
     */
    private function ensureBasicPlanForSpecialty(Specialty $specialty)
    {
        $planName = "Basic " . $specialty->name;
        
        $attributes = ['name' => $planName];
        $values = []; // Initialize empty

        // Schema Safety Checks
        if (Schema::hasColumn('subscription_plans', 'price')) {
            $values['price'] = 0;
        }
        if (Schema::hasColumn('subscription_plans', 'max_staff')) {
            $values['max_staff'] = 2;
        }
        if (Schema::hasColumn('subscription_plans', 'max_patients')) {
            $values['max_patients'] = 100;
        }
        if (Schema::hasColumn('subscription_plans', 'max_appointments_monthly')) {
            $values['max_appointments_monthly'] = 200;
        }
        
        // Strict Match Logic
        if (Schema::hasColumn('subscription_plans', 'specialty_id')) {
            $attributes['specialty_id'] = $specialty->id;
        }

        // 1. Find or Create the Basic Plan
        $plan = SubscriptionPlan::firstOrCreate(
            $attributes, 
            $values
        );

        // 2. Sync Modules (Copy from Specialty Pivot to Plan Pivot)
        $this->syncPlanModulesFromSpecialty($specialty, $plan);

        // 3. Set as Default for Specialty
        if ($specialty->default_plan_id !== $plan->id) {
            $specialty->default_plan_id = $plan->id;
            $specialty->save();
            $this->command->info("Set default plan for {$specialty->name} to {$plan->name}");
        }

        return $plan;
    }

    /**
     * Copy active modules from Specialty -> Plan
     */
    private function syncPlanModulesFromSpecialty(Specialty $specialty, SubscriptionPlan $plan)
    {
        // Get all modules enabled for this specialty
        $specialtyModules = DB::table('specialty_module')
            ->where('specialty_id', $specialty->id)
            ->where('enabled', true)
            ->get();

        foreach ($specialtyModules as $sModule) {
            // Race-Condition Safe: insertOrIgnore
            // Requires unique constraint on (plan_id, module_id) which exists in migration.
            DB::table('plan_module')->insertOrIgnore([
                'plan_id' => $plan->id,
                'module_id' => $sModule->module_id,
                'enabled' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Find Doctors with NULL plan_id and assign their Specialty's default.
     */
    private function backfillDoctorPlans($specialtyMap)
    {
        $doctorsWithoutPlan = User::where('role', 'doctor')
            ->whereNull('plan_id')
            ->whereNotNull('specialty_id')
            ->select('id', 'specialty_id', 'plan_id') 
            ->get();

        $count = 0;

        foreach ($doctorsWithoutPlan as $doctor) {
            $specialty = $specialtyMap->get($doctor->specialty_id);

            // Verify the object has the default_plan_id set
            if ($specialty && $specialty->default_plan_id) {
                $doctor->plan_id = $specialty->default_plan_id;
                $doctor->save();
                $count++;
            } else {
                $this->command->warn("Doctor ID {$doctor->id} skipped: Specialty {$doctor->specialty_id} has no default plan.");
            }
        }

        if ($count > 0) {
            $this->command->info("Backfilled plan_id for {$count} doctors.");
        } else {
            $this->command->info("No doctors found needing backfill.");
        }
    }
}
