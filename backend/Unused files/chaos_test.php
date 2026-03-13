<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Specialty;
use App\Models\SubscriptionPlan;
use App\Models\DeletionRequest;
use App\Services\DeleteManager;
use App\Services\DeletionWorkflowManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$dm = app(DeleteManager::class);
$dwm = app(DeletionWorkflowManager::class);

$spec = Specialty::withTrashed()->find(12);
$plan = SubscriptionPlan::where('specialty_id', 12)->first() ?: SubscriptionPlan::create(['specialty_id' => 12, 'tier' => 'PRO_'.time(), 'name' => 'Pro_'.time(), 'price' => 99]);
$admin = User::first(); 

echo "--- ATTACK 1 & 2: DIRECT MODEL DELETE (BYPASS MANAGER) ---\n";
try {
    echo "Attempting direct Eloquent ->delete() on Plan ID {$plan->id}...\n";
    $plan->delete();
    echo "Result: SUCCESS (Bypass Succeeded)\n";
} catch (\Throwable $e) {
    echo "RESULT: BLOCKED - " . $e->getMessage() . "\n";
}

echo "\n--- ATTACK 3: RESTORE AFTER HARD DELETE ---\n";
try {
    $tempPlan = SubscriptionPlan::create(['specialty_id' => 12, 'tier' => 'Chaos_'.time(), 'name' => 'Temp_'.time(), 'price' => 0]);
    $id = $tempPlan->id;
    $tempPlan->forceDelete();
    echo "Hard deleted Plan #$id. Attempting restore...\n";
    $dm->restore('plan', $id);
} catch (\Throwable $e) {
    echo "RESULT: BLOCKED - " . get_class($e) . " : " . $e->getMessage() . "\n";
}

echo "\n--- ATTACK 4: NON-ADMIN APPROVAL ---\n";
try {
    $staff = User::where('role', 'staff')->first() ?: User::create(['name' => 'Staff', 'email' => 'staff_'.time().'@chaos.com', 'role' => 'staff', 'password' => 'pass']);
    $req = DeletionRequest::create([
        'entity_type' => 'specialty',
        'entity_id' => 12,
        'requested_by' => $admin->id,
        'status' => 'pending'
    ]);
    echo "Attempting approval by Staff ID {$staff->id}...\n";
    $dwm->approveRequest($req->id, $staff->id);
    echo "RESULT: SUCCESS (Programmatic Bypass Succeeded)\n";
} catch (\Throwable $e) {
    echo "RESULT: BLOCKED - " . $e->getMessage() . "\n";
}

echo "\n--- ATTACK 5: DOUBLE CASCADE EXECUTION ---\n";
try {
    $req = DeletionRequest::create([
        'entity_type' => 'specialty',
        'entity_id' => 12,
        'requested_by' => $admin->id,
        'status' => 'approved',
        'cascade_preview_json' => $dm->cascadePreview('specialty', 12)
    ]);
    $req->update(['status' => 'executed']);
    echo "Attempting execution on already executed request #{$req->id}...\n";
    $dwm->executeRequest($req->id, $admin->id);
} catch (\Throwable $e) {
    echo "RESULT: BLOCKED - " . $e->getMessage() . "\n";
}

echo "\n--- ATTACK 6: DB SNAPSHOT TAMPERING (DRIFT) ---\n";
try {
    $req = DeletionRequest::create([
        'entity_type' => 'specialty',
        'entity_id' => 12,
        'requested_by' => $admin->id,
        'status' => 'approved',
        'cascade_preview_json' => $dm->cascadePreview('specialty', 12)
    ]);
    $tampered = $req->cascade_preview_json;
    $tampered['will_delete']['doctors'] += 100;
    $req->update(['cascade_preview_json' => $tampered]);
    echo "Attempting execution with tampered snapshot on request #{$req->id}...\n";
    $dwm->executeRequest($req->id, $admin->id);
} catch (\Throwable $e) {
    echo "RESULT: BLOCKED - " . $e->getMessage() . "\n";
}

echo "\n--- ATTACK 7: DELETE SPECIALTY WITH DOCTORS WITHOUT CASCADE ---\n";
try {
    echo "Attempting Archive on Specialty ID 12 without cascade...\n";
    $res = $dm->archive('specialty', 12);
    echo "RESULT: " . json_encode($res) . "\n";
} catch (\Throwable $e) {
    echo "RESULT: BLOCKED - " . $e->getMessage() . "\n";
}

echo "\n--- ATTACK 8: BULK MIXED SAFE/UNSAFE ---\n";
try {
    echo "Attempting bulk delete with mixed IDs [12, 9999]...\n";
    $res = $dm->bulkDelete('specialty', [12, 9999]);
    echo "RESULT: " . json_encode($res) . "\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
