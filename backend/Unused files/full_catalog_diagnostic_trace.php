<?php
echo "\n============================\n";
echo "FULL CATALOG DIAGNOSTIC TRACE\n";
echo "============================\n";

/* 1️⃣ ALL SPECIALTIES */
echo "\n==== ALL SPECIALTIES ====\n";
$specs = DB::table('specialties')->get();
foreach ($specs as $s) {
    print_r($s);
}
echo "Total Specialties: " . $specs->count() . "\n";


/* 2️⃣ ALL CLINICAL SERVICE CATEGORIES */
echo "\n==== ALL CLINICAL SERVICE CATEGORIES (RAW) ====\n";
$cats = DB::table('clinical_service_categories')->get();
foreach ($cats as $c) {
    print_r($c);
}
echo "Total Categories: " . $cats->count() . "\n";


/* 3️⃣ ACTIVE & NON-DELETED CATEGORIES */
echo "\n==== ACTIVE & NON-DELETED CATEGORIES ====\n";
$activeCats = DB::table('clinical_service_categories')
    ->where('is_active', 1)
    ->whereNull('deleted_at')
    ->get();
foreach ($activeCats as $c) {
    print_r($c);
}
echo "Active Visible Categories: " . $activeCats->count() . "\n";


/* 4️⃣ CATEGORY COUNT BY SPECIALTY */
echo "\n==== CATEGORY COUNT BY SPECIALTY ====\n";
$grouped = DB::table('clinical_service_categories')
    ->select('specialty_id', DB::raw('count(*) as total'))
    ->groupBy('specialty_id')
    ->get();
foreach ($grouped as $g) {
    print_r($g);
}


/* 5️⃣ ALL CLINICAL CATALOG ITEMS */
echo "\n==== ALL CLINICAL CATALOG ITEMS (RAW) ====\n";
$items = DB::table('clinical_catalog')->get();
foreach ($items as $i) {
    print_r($i);
}
echo "Total Catalog Items: " . $items->count() . "\n";


/* 6️⃣ ACTIVE & NON-DELETED CATALOG ITEMS */
echo "\n==== ACTIVE & NON-DELETED CATALOG ITEMS ====\n";
$activeItems = DB::table('clinical_catalog')
    ->whereNull('deleted_at')
    ->get();
foreach ($activeItems as $i) {
    print_r($i);
}
echo "Active Catalog Items: " . $activeItems->count() . "\n";


/* 7️⃣ CHECK FOR ORPHAN CATALOG ITEMS */
echo "\n==== ORPHAN CATALOG ITEMS (NO CATEGORY FOUND) ====\n";
$orphans = DB::table('clinical_catalog as cc')
    ->leftJoin('clinical_service_categories as c', 'cc.category_id', '=', 'c.id')
    ->whereNull('c.id')
    ->select('cc.*')
    ->get();
foreach ($orphans as $o) {
    print_r($o);
}
echo "Orphan Items: " . $orphans->count() . "\n";


echo "\n============================\n";
echo "END OF DIAGNOSTIC TRACE\n";
echo "============================\n";
