<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateSpecialtyModules extends Command
{
    protected $signature = 'migrate:specialty-modules';
    protected $description = 'Migrate JSON features to specialty_module pivot table';

    public function handle()
    {
        $this->info('Migrating Specialty JSON Features...');
        
        $specialties = \App\Models\Specialty::all();
        $modules = \App\Models\Module::all()->keyBy('key');

        foreach ($specialties as $spec) {
            $features = $spec->features ?? [];
            if (is_string($features)) $features = json_decode($features, true);
            
            $this->info("Processing Specialty: {$spec->name}");

            foreach ($features as $key => $isEnabled) {
                // Map 'inventory' to 'pharmacy' if needed, or ignore
                if ($key === 'inventory') $key = 'pharmacy';
                
                if (isset($modules[$key])) {
                    $moduleId = $modules[$key]->id;
                    
                    // Upsert into pivot
                    \DB::table('specialty_module')->updateOrInsert(
                        ['specialty_id' => $spec->id, 'module_id' => $moduleId],
                        ['enabled' => (bool)$isEnabled, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }
        
        $this->info('Specialty Migration Complete.');
    }
}
