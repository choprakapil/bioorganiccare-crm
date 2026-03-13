<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== TABLE CHECK ===\n";
echo "Has clinical_catalog: " . (Schema::hasTable('clinical_catalog') ? 'Yes' : 'No') . "\n";
echo "Has local_services: " . (Schema::hasTable('local_services') ? 'Yes' : 'No') . "\n";

echo "\n=== CLINICAL CATALOG ITEMS ===\n";
$catalog = DB::select("SELECT id, item_name, type FROM clinical_catalog LIMIT 5");
foreach ($catalog as $item) {
    echo "Catalog ID: {$item->id} | Name: {$item->item_name} | Type: {$item->type}\n";
}

if (Schema::hasTable('local_services')) {
    echo "\n=== LOCAL SERVICES ITEMS ===\n";
    $locals = DB::select("SELECT id, item_name, type, doctor_id FROM local_services LIMIT 5");
    foreach ($locals as $item) {
        echo "Local ID: {$item->id} | Name: {$item->item_name} | Type: {$item->type}\n";
    }
}
