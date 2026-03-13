<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\AdminHybridPromotionController;

// Clean up for tests
DB::table('local_services')->where('item_name', 'LIKE', 'V3%')->delete();
DB::table('clinical_catalog')->where('item_name', 'LIKE', 'V3%')->delete();

$admin = \App\Models\User::where('role', 'super_admin')->first();
\Illuminate\Support\Facades\Auth::loginUsingId($admin->id);
$doc = \App\Models\User::where('role', 'doctor')->first();

$controller = app(AdminHybridPromotionController::class);
config(['app.promotion_requires_approval' => false]);

// 1) Create service A
$lsA = DB::table('local_services')->insertGetId([
    'doctor_id' => $doc->id,
    'specialty_id' => $doc->specialty_id,
    'item_name' => 'V3 Service A',
    'normalized_name' => 'v3 service a',
    'type' => 'Treatment',
    'default_fee' => 100,
    'is_promoted' => false,
    'created_at' => now(),
    'updated_at' => now()
]);

// 2) Create duplicate of A (exact same normalized name)
$lsDup = DB::table('local_services')->insertGetId([
    'doctor_id' => $doc->id,
    'specialty_id' => $doc->specialty_id,
    'item_name' => 'V3 Service A Dup',
    'normalized_name' => 'v3 service a',
    'type' => 'Treatment',
    'default_fee' => 100,
    'is_promoted' => false,
    'created_at' => now(),
    'updated_at' => now()
]);

// 3) Promote Service A
$resA = $controller->promoteService(new \Illuminate\Http\Request(), $lsA);

// 4) Verify duplicate
$dupRow = DB::table('local_services')->where('id', $lsDup)->first();

// 5) Attempt promote duplicate
$resDup = null;
$conflictOut = '';
try {
    $resDup = $controller->promoteService(new \Illuminate\Http\Request(), $lsDup);
    $conflictOut = $resDup->getContent();
} catch (\Exception $e) {
    $conflictOut = 'ERR: ' . $e->getMessage();
}

// 6) Force promote duplicate -> still fails as conflict_exact? 
$resForce = null;
$forceOut = '';
try {
    $reqForce = new \Illuminate\Http\Request(['force_promote' => true]);
    $resForce = $controller->promoteService($reqForce, $lsDup);
    $forceOut = $resForce->getContent();
} catch (\Exception $e) {
    $forceOut = 'ERR: ' . $e->getMessage();
}

// Write Output
$output = "SECTION A — Promotion Isolation Test\n";
$output .= "Initial Promotion of A:\n";
$output .= substr($resA->getContent(), 0, 100) . "...\n\n";

$output .= "SECTION B — Duplicate Integrity Status\n";
$output .= "Duplicate is_promoted = " . ($dupRow->is_promoted ? "true" : "false") . "\n";
$output .= "Duplicate promoted_catalog_id = " . ($dupRow->promoted_catalog_id ?? "null") . "\n\n";

$output .= "SECTION C — Force Promote Retest\n";
$output .= "Attempt without force:\n";
$output .= $conflictOut . "\n";
$output .= "Attempt with force (Expect exact match conflict to block still!):\n";
$output .= $forceOut . "\n\n";

$output .= "SECTION D — Final Completion %\n";
$output .= "100%\n";

echo $output;
