<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            'patient_registry',
            'clinical_services',
            'billing',
            'expenses',
            'pharmacy',
            'appointments',
            'growth_insights'
        ];

        foreach ($modules as $key) {
            \App\Models\Module::firstOrCreate(
                ['key' => strtolower($key)],
                [
                    'name' => ucwords(str_replace('_', ' ', $key)),
                    'description' => null,
                    'is_active' => true
                ]
            );
        }
    }
}
