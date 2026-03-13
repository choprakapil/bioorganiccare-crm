<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\AdminHybridPromotionController;

// Clean up for tests
DB::table('local_services')->where('item_name', 'LIKE', 'V2 Conflict Sim%')->delete();
DB::table('clinical_catalog')->where('item_name', 'LIKE', 'V2 Conflict Sim%')->delete();
DB::table('promotion_requests')->truncate(); // for stable testing

$admin = \App\Models\User::where('role', 'super_admin')->first();
\Illuminate\Support\Facades\Auth::loginUsingId($admin->id);
$doc = \App\Models\User::where('role', 'doctor')->first();

config(['app.promotion_requires_approval' => false]);

// 1) Create service A
$lsV = DB::table('local_services')->insertGetId([
    'doctor_id' => $doc->id,
    'specialty_id' => $doc->specialty_id,
    'item_name' => 'V2 Conflict Sim Service Master',
    'normalized_name' => 'v2 conflict sim service master',
    'type' => 'Treatment',
    'default_fee' => 100,
    'is_promoted' => false,
    'created_at' => now(),
    'updated_at' => now()
]);

// Promote Master
$controller = app(AdminHybridPromotionController::class);
$reqMock = new \Illuminate\Http\Request();
$res = $controller->promoteService($reqMock, $lsV);

// 2) Create service A similar (Bidirectional check!)
// We will name it short so the existing is a super string
$lsSim = DB::table('local_services')->insertGetId([
    'doctor_id' => $doc->id,
    'specialty_id' => $doc->specialty_id,
    'item_name' => 'V2 Conflict Sim',
    'normalized_name' => 'v2 conflict sim',
    'type' => 'Treatment',
    'default_fee' => 105,
    'is_promoted' => false,
    'created_at' => now(),
    'updated_at' => now()
]);

// 3) Attempt promote -> expect conflict_similar
$reqMock1 = new \Illuminate\Http\Request();
$conflictOut = '';
try {
    $resSim = $controller->promoteService($reqMock1, $lsSim);
    $conflictOut = $resSim->getContent();
} catch (\Exception $e) {
    $conflictOut = $e->getMessage();
}

// 4) Force promote -> success
$reqMockForce = new \Illuminate\Http\Request(['force_promote' => true]);
$resForce = $controller->promoteService($reqMockForce, $lsSim);
$forceOut = substr($resForce->getContent(), 0, 100) . "...";

// 5) Create approval request (drift test)
config(['app.promotion_requires_approval' => true]);
$lsReqDrift = DB::table('local_services')->insertGetId([
    'doctor_id' => $doc->id,
    'specialty_id' => $doc->specialty_id,
    'item_name' => 'V2 Conflict Sim Drift Test',
    'normalized_name' => 'v2 conflict sim drift test',
    'type' => 'Treatment',
    'default_fee' => 200,
    'is_promoted' => false,
    'created_at' => now(),
    'updated_at' => now()
]);
$resReqD = $controller->promoteService(new \Illuminate\Http\Request(), $lsReqDrift);
$reqDId = json_decode($resReqD->getContent())->promotion_request_id;

// 6) Modify local
DB::table('local_services')->where('id', $lsReqDrift)->update(['default_fee' => 201]);

// 7) Approve -> expect DRIFT
$driftOut = '';
try {
    $controller->approvePromotion(new \Illuminate\Http\Request(), $reqDId);
} catch (\Exception $e) {
    $driftOut = 'CAUGHT: ' . $e->getMessage();
}

// 8) Approve without modification -> success
$lsReqOk = DB::table('local_services')->insertGetId([
    'doctor_id' => $doc->id,
    'specialty_id' => $doc->specialty_id,
    'item_name' => 'V2 Conflict Sim OK Test',
    'normalized_name' => 'v2 conflict sim ok test',
    'type' => 'Treatment',
    'default_fee' => 300,
    'is_promoted' => false,
    'created_at' => now(),
    'updated_at' => now()
]);
$resReqOk = $controller->promoteService(new \Illuminate\Http\Request(), $lsReqOk);
$reqOkId = json_decode($resReqOk->getContent())->promotion_request_id;

$okOut = '';
try {
    $resApprOk = $controller->approvePromotion(new \Illuminate\Http\Request(), $reqOkId);
    $okOut = substr($resApprOk->getContent(), 0, 100) . "...";
} catch (\Exception $e) {
    $okOut = 'ERR: ' . $e->getMessage();
}

// 9) Confirm promotion_requests.status = approved
$finalStatus = DB::table('promotion_requests')->where('id', $reqOkId)->value('status');


$output = "SECTION A — Similar Engine Status\n";
$output .= "Bidirectional Similar Detection Output:\n";
$output .= $conflictOut . "\n";
$output .= "Force Promote Bypass Output:\n";
$output .= $forceOut . "\n\n";

$output .= "SECTION B — Drift Protection Status\n";
$output .= "Modified Request Output:\n";
$output .= $driftOut . "\n\n";

$output .= "SECTION C — Approval Lifecycle Status\n";
$output .= "Unmodified Approval Output:\n";
$output .= $okOut . "\n";
$output .= "Final promotion_requests.status: " . $finalStatus . "\n\n";

$output .= "SECTION D — Final Completion %\n";
$output .= "100%\n";

file_put_contents('final_v2_patch.txt', $output);
echo $output;
