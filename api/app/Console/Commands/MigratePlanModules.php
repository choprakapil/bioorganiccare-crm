<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigratePlanModules extends Command
{
    protected $signature = 'migrate:plan-modules';
    protected $description = 'Migrate Plan JSON features to plan_module pivot table';

    public function handle()
    {
        $this->info('Migrating Plan JSON Features...');
        
        $plans = \App\Models\SubscriptionPlan::all();
        $modules = \App\Models\Module::all()->keyBy('key');

        foreach ($plans as $plan) {
            $features = $plan->features['modules'] ?? [];
            if (is_string($features)) $features = json_decode($features, true);
            
            $this->info("Processing Plan: {$plan->name}");

            foreach ($features as $key => $isEnabled) {
                if ($key === 'inventory') $key = 'pharmacy';
                
                if (isset($modules[$key])) {
                    $moduleId = $modules[$key]->id;
                    
                    \DB::table('plan_module')->updateOrInsert(
                        ['subscription_plan_id' => $plan->id, 'module_id' => $moduleId],
                        ['enabled' => (bool)$isEnabled, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }
    
        $this->info('Plan Migration Complete.');
    }
}
