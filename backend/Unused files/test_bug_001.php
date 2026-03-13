<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$catalog = \App\Models\ClinicalCatalog::first();
if ($catalog) {
    echo "First Global Catalog ID: " . $catalog->id . "\n";
    $exists = \App\Models\ClinicalCatalog::where('id', 'global_' . $catalog->id)->exists();
    echo "Does 'global_{$catalog->id}' exist? " . ($exists ? 'Yes' : 'No') . "\n";
    
    // Test validation bypass logic
    $rawId = str_replace('global_', '', 'global_' . $catalog->id);
    echo "Raw ID from str_replace: " . $rawId . "\n";
} else {
    echo "No global catalog found.\n";
}
