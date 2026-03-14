<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fetch all existing plans
        $plans = DB::table('subscription_plans')->get();

        foreach ($plans as $plan) {

            $oldFeatures = json_decode($plan->features, true) ?? [];

            // Preserve existing module toggles
            $modules = [
                'patients' => $oldFeatures['patients'] ?? true,
                'billing' => $oldFeatures['billing'] ?? true,
                'expenses' => $oldFeatures['expenses'] ?? false,
                'pharmacy' => $oldFeatures['inventory'] ?? false,
                'growth_insights' => true,
            ];

            $limits = [
                'max_staff' => $oldFeatures['max_staff'] ?? 2
            ];

            $newFeatures = [
                'modules' => $modules,
                'limits' => $limits
            ];

            DB::table('subscription_plans')
                ->where('id', $plan->id)
                ->update([
                    'features' => json_encode($newFeatures)
                ]);
        }

        // Update specialties with base module capabilities
        $specialties = DB::table('specialties')->get();

        foreach ($specialties as $specialty) {

            $features = [
                'patient_registry' => true,
                'billing' => true,
                'expenses' => true,
                'pharmacy' => $specialty->has_teeth_chart ? true : false,
                'growth_insights' => true,
            ];

            DB::table('specialties')
                ->where('id', $specialty->id)
                ->update([
                    'features' => json_encode($features)
                ]);
        }
    }

    public function down(): void
    {
        // Rollback not implemented for safety.
    }
};