<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClinicalCatalog;
use App\Models\Specialty;

class ExtraDentalItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dental = Specialty::where('name', 'Dental')->first();

        if (!$dental) return;

        $items = [
            // RCT
            ['name' => 'RCT - ACP Done', 'type' => 'Treatment', 'fee' => 1500],
            ['name' => 'RCT - BMP Done', 'type' => 'Treatment', 'fee' => 1500],
            ['name' => 'RCT - Obturation Done', 'type' => 'Treatment', 'fee' => 1500],
            
            // Extraction
            ['name' => 'Simple Extraction', 'type' => 'Treatment', 'fee' => 800],
            ['name' => 'Surgical Extraction', 'type' => 'Treatment', 'fee' => 2500],
            
            // Fillings
            ['name' => 'Temporary Filling', 'type' => 'Treatment', 'fee' => 500],
            ['name' => 'GIC Filling', 'type' => 'Treatment', 'fee' => 800],
            ['name' => 'Composite Filling', 'type' => 'Treatment', 'fee' => 1200],

            // Crown Prep
            ['name' => 'Crown Cutting', 'type' => 'Treatment', 'fee' => 1000],
            ['name' => 'Impression', 'type' => 'Treatment', 'fee' => 500],
            ['name' => 'Luting', 'type' => 'Treatment', 'fee' => 500],
        ];

        foreach ($items as $item) {
            ClinicalCatalog::firstOrCreate(
                [
                    'specialty_id' => $dental->id,
                    'item_name' => $item['name']
                ],
                [
                    'type' => $item['type'],
                    'default_fee' => $item['fee']
                ]
            );
        }
    }
}
