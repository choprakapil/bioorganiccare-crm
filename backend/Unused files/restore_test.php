<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Specialty;
use App\Services\DeleteManager;

$dm = app(DeleteManager::class);

echo "--- RESTORE VALIDATION TEST (SPECIALTY) ---\n";
try {
    $suffix = time();
    $spec = Specialty::create(['name' => 'Spec_'.$suffix, 'slug' => 'slug_'.$suffix]);
    $id = $spec->id;
    echo "Created Specialty #$id.\n";
    
    echo "Archiving via DeleteManager...\n";
    $res = $dm->archive('specialty', $id);
    echo "Archive Result: " . json_encode($res) . "\n";
    
    echo "Restoring via DeleteManager...\n";
    $res = $dm->restore('specialty', $id);
    echo "Restore Result: " . json_encode($res) . "\n";

    echo "Verifying specialty exists...\n";
    $exists = Specialty::where('id', $id)->exists();
    echo "Exists: " . ($exists ? "YES" : "NO") . "\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
