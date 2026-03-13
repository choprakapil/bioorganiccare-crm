<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\DeleteManager;
use Illuminate\Support\Facades\DB;

$manager = app(DeleteManager::class);

echo "--- TEST 1: Delete Promoted Service ---\n";
try {
    // Find promoted service from DB
    $res = $manager->forceDelete('service', 31); 
    echo "FAILED: allowed deletion\n";
    print_r($res);
} catch (\Exception $e) {
    echo "SUCCESS: Caught error: " . $e->getMessage() . "\n";
}

echo "\n--- TEST 2: Delete Promoted Medicine ---\n";
try {
    $res = $manager->forceDelete('medicine', 14); 
    echo "FAILED: allowed deletion\n";
    print_r($res);
} catch (\Exception $e) {
    echo "SUCCESS: Caught error: " . $e->getMessage() . "\n";
}

echo "\n--- TEST 3: Delete local_service ---\n";
try {
    $manager->delete('local_service', 1);
} catch (\Exception $e) {
    // maybe already soft deleted
}
$ls_global = DB::table('clinical_catalog')->where('id', 31)->first();
$ls_local = DB::table('local_services')->where('id', 1)->first();
echo "deleted_at on local_service: " . ($ls_local->deleted_at ?? 'NULL') . "\n";
echo "Global service still exists: " . ($ls_global ? 'YES' : 'NO') . "\n";

echo "\n--- TEST 4: Delete local_medicine ---\n";
try {
    $manager->delete('local_medicine', 2);
} catch (\Exception $e) {
    // maybe already soft deleted
}
$lm_global = DB::table('master_medicines')->where('id', 14)->first();
$lm_local = DB::table('local_medicines')->where('id', 2)->first();
echo "deleted_at on local_medicine: " . ($lm_local->deleted_at ?? 'NULL') . "\n";
echo "Global medicine still exists: " . ($lm_global ? 'YES' : 'NO') . "\n";
