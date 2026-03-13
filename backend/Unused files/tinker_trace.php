<?php

$spec = DB::table('specialties')->where('name', 'General Dentistry')->first();
echo "\n--- STEP 1 ---\n";
print_r($spec);

if (!$spec) {
    echo "Specialty not found!\n";
    return;
}

$id = $spec->id;

echo "\n--- STEP 2 ---\n";
echo 'Clinical Categories Count: ';
echo \App\Models\ClinicalServiceCategory::withTrashed()->where('specialty_id',$id)->count();
echo PHP_EOL;

echo 'Clinical Catalog Count: ';
echo \App\Models\ClinicalCatalog::withTrashed()->where('specialty_id',$id)->count();
echo PHP_EOL;

print_r(\App\Models\ClinicalServiceCategory::withTrashed()->where('specialty_id',$id)->get()->toArray());
print_r(\App\Models\ClinicalCatalog::withTrashed()->where('specialty_id',$id)->get()->toArray());

echo "\n--- STEP 3 ---\n";
$pharmacyCategories = DB::table('pharmacy_categories')->where('specialty_id',$id)->get();

echo 'Pharmacy Categories:';
print_r($pharmacyCategories->toArray());

$catIds = $pharmacyCategories->pluck('id');

echo 'Master Medicines Count: ';
echo \App\Models\MasterMedicine::withTrashed()->whereIn('pharmacy_category_id',$catIds)->count();
echo PHP_EOL;

print_r(\App\Models\MasterMedicine::withTrashed()->whereIn('pharmacy_category_id',$catIds)->get()->toArray());

echo "\n--- STEP 4 ---\n";
$catalogIds = DB::table('clinical_catalog')->where('specialty_id',$id)->pluck('id');
$pharmCatIds = DB::table('pharmacy_categories')->where('specialty_id',$id)->pluck('id');
$medicineIds = DB::table('master_medicines')->whereIn('pharmacy_category_id',$pharmCatIds)->pluck('id');

echo 'Inventory referencing catalog: ';
echo DB::table('inventory')->whereIn('catalog_id',$catalogIds)->count();
echo PHP_EOL;

echo 'Inventory referencing medicines: ';
echo DB::table('inventory')->whereIn('master_medicine_id',$medicineIds)->count();
echo PHP_EOL;

print_r(DB::table('inventory')->whereIn('catalog_id',$catalogIds)->get()->toArray());
print_r(DB::table('inventory')->whereIn('master_medicine_id',$medicineIds)->get()->toArray());

echo "\n--- STEP 5 ---\n";
echo 'Treatments referencing catalog: ';
echo DB::table('treatments')->whereIn('catalog_id',$catalogIds)->count();
echo PHP_EOL;

echo 'InvoiceItems referencing inventory: ';
echo DB::table('invoice_items')
    ->whereIn('inventory_id',
        DB::table('inventory')->whereIn('catalog_id',$catalogIds)->pluck('id')
    )->count();
echo PHP_EOL;

echo "\n--- STEP 6 ---\n";
echo 'Catalog Version Records: ';
echo DB::table('catalog_versions')
    ->where('entity_type','clinical')
    ->whereIn('entity_id',$catalogIds)
    ->count();
echo PHP_EOL;

print_r(DB::table('catalog_versions')
    ->where('entity_type','clinical')
    ->whereIn('entity_id',$catalogIds)
    ->get()
    ->toArray());

echo "\n--- STEP 7 ---\n";
echo 'Doctors linked: ';
echo DB::table('users')->where('specialty_id',$id)->count();
echo PHP_EOL;
