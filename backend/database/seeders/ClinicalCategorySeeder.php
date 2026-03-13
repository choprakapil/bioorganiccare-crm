<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClinicalCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dental = \App\Models\Specialty::where('name', 'Dental')->orWhere('slug', 'dental')->first();
        if (!$dental) {
            $this->command->warn("Dental specialty not found, skipping.");
            return;
        }

        $categories = [
            'General Chemistry / Consultation' => ['General', 0],
            'Endodontics (RCT)' => ['Root Canal', 10],
            'Restorative (Fillings)' => ['Filling', 20],
            'Prosthetics (Crowns/Bridges)' => ['Crown', 30],
            'Oral Surgery' => ['Extraction', 40],
            'Orthodontics' => ['Braces', 50],
            'Pharmacy' => ['Medicine', 99]
        ];

        $catMap = [];

        foreach ($categories as $name => $meta) {
            $cat = \App\Models\ClinicalServiceCategory::firstOrCreate(
                ['specialty_id' => $dental->id, 'name' => $name],
                ['sort_order' => $meta[1]]
            );
            $catMap[$name] = $cat->id;
        }

        // Auto-Map Existing Items
        $items = \App\Models\ClinicalCatalog::where('specialty_id', $dental->id)->get();
        
        foreach ($items as $item) {
            $categoryId = $catMap['General Chemistry / Consultation']; // Default

            if ($item->type === 'Medicine') {
                $categoryId = $catMap['Pharmacy'];
            } else {
                // Keyword matching for treatments
                $name = strtolower($item->item_name);
                if (str_contains($name, 'root canal') || str_contains($name, 'rct')) {
                    $categoryId = $catMap['Endodontics (RCT)'];
                } elseif (str_contains($name, 'filling') || str_contains($name, 'composite') || str_contains($name, 'gic')) {
                    $categoryId = $catMap['Restorative (Fillings)'];
                } elseif (str_contains($name, 'crown') || str_contains($name, 'bridge') || str_contains($name, 'veneer')) {
                    $categoryId = $catMap['Prosthetics (Crowns/Bridges)'];
                } elseif (str_contains($name, 'extraction') || str_contains($name, 'impaction')) {
                    $categoryId = $catMap['Oral Surgery'];
                } elseif (str_contains($name, 'braces') || str_contains($name, 'aligner')) {
                    $categoryId = $catMap['Orthodontics'];
                }
            }

            $item->category_id = $categoryId;
            $item->save();
        }
    }
}
