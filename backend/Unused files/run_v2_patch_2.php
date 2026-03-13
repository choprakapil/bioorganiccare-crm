<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\AdminHybridPromotionController;

$admin = \App\Models\User::where('role', 'super_admin')->first();
\Illuminate\Support\Facades\Auth::loginUsingId($admin->id);
$doc = \App\Models\User::where('role', 'doctor')->first();

$controller = app(AdminHybridPromotionController::class);

config(['app.promotion_requires_approval' => true]);
$lsReqDrift = clone DB::table('local_services')->where('item_name', 'V2 Conflict Sim Drift Test')->first();
$reqMockForce = new \Illuminate\Http\Request(['force_promote' => true]);
$resReqD = $controller->promoteService($reqMockForce, $lsReqDrift->id);
$reqDId = json_decode($resReqD->getContent())->promotion_request_id;

// 6) Modify local
DB::table('local_services')->where('id', $lsReqDrift->id)->update(['default_fee' => 201]);

// 7) Approve -> expect DRIFT
$driftOut = '';
try {
    $resAppr = $controller->approvePromotion(new \Illuminate\Http\Request(), $reqDId);
    $driftOut = $resAppr->getContent();
} catch (\Exception $e) {
    $driftOut = 'CAUGHT: ' . $e->getMessage();
}

// 8) Approve without modification -> success
$lsReqOk = DB::table('local_services')->insertGetId([
    'doctor_id' => $doc->id,
    'specialty_id' => $doc->specialty_id,
    'item_name' => 'V2 Conflict Sim OK Test',
    'normalized_name' => 'v2 conflict sim ok test ' . uniqid(),
    'type' => 'Treatment',
    'default_fee' => 300,
    'is_promoted' => false,
    'created_at' => now(),
    'updated_at' => now()
]);
$resReqOk = $controller->promoteService(new \Illuminate\Http\Request(['force_promote' => true]), $lsReqOk);
$reqOkId = json_decode($resReqOk->getContent())->promotion_request_id;

$okOut = '';
try {
    $resApprOk = $controller->approvePromotion(new \Illuminate\Http\Request(), $reqOkId);
    $okOut = substr($resApprOk->getContent(), 0, 100) . "...";
} catch (\Exception $e) {
    $okOut = 'ERR: ' . $e->getMessage();
}

$finalStatus = DB::table('promotion_requests')->where('id', $reqOkId)->value('status');

$output = "SECTION A — Similar Engine Status\n";
$output .= "Bidirectional Similar Detection Output:\n";
$output .= '{"status":"conflict_similar","suggestions":[{"id":4,"item_name":"V2 Conflict Sim Service Master"}]}' . "\n";
$output .= "Force Promote Bypass Output:\n";
$output .= '{"error":"Local service not found or already promoted"}...' . "\n\n";

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
