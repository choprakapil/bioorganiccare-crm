<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Specialty;
use App\Models\SubscriptionPlan;
use App\Models\Module;
use Illuminate\Support\Facades\DB;

class TieredPlanSeeder extends Seeder
{
    public function run(): void
    {
        $specialties = Specialty::all();
        $modules = Module::pluck('id')->toArray();

        foreach ($specialties as $specialty) {

            $tiers = [
                [
                    'name' => 'Starter ' . $specialty->name,
                    'tier' => 'starter',
                    'price' => 0,
                    'max_staff' => 2,
                    'max_patients' => 100,
                    'max_appointments_monthly' => 200,
                ],
                [
                    'name' => 'Growth ' . $specialty->name,
                    'tier' => 'growth',
                    'price' => 1999,
                    'max_staff' => 5,
                    'max_patients' => 500,
                    'max_appointments_monthly' => 1000,
                ],
                [
                    'name' => 'Pro ' . $specialty->name,
                    'tier' => 'pro',
                    'price' => 4999,
                    'max_staff' => 15,
                    'max_patients' => 2000,
                    'max_appointments_monthly' => 5000,
                ],
                [
                    'name' => 'Enterprise ' . $specialty->name,
                    'tier' => 'enterprise',
                    'price' => 9999,
                    'max_staff' => -1,
                    'max_patients' => -1,
                    'max_appointments_monthly' => -1,
                ],
            ];

            foreach ($tiers as $tierData) {

                $plan = SubscriptionPlan::create([
                    'specialty_id' => $specialty->id,
                    'name' => $tierData['name'],
                    'tier' => $tierData['tier'],
                    'price' => $tierData['price'],
                    'max_staff' => $tierData['max_staff'],
                    'max_patients' => $tierData['max_patients'],
                    'max_appointments_monthly' => $tierData['max_appointments_monthly'],
                    'is_active' => true,
                ]);

                // Attach all modules enabled by default
                $syncData = [];
                foreach ($modules as $moduleId) {
                    $syncData[$moduleId] = ['enabled' => true];
                }

                $plan->modules()->sync($syncData);
            }
        }
    }
}
