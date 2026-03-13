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

echo "PHASE 5 — DEPENDENCY SUMMARY EXECUTION TEST\n";
try {
    $spec = Specialty::first();
    $doc  = User::where('role', 'doctor')->first();
    $plan = SubscriptionPlan::first();

    if ($spec) {
        echo "Specialty Summary [ID:{$spec->id}]:\n";
        print_r($dm->dependencySummary('specialty', $spec->id));
    }
} catch (\Throwable $e) {
    echo "ERROR PHASE 5: " . $e->getMessage() . "\n";
}

echo "\nPHASE 6 — GOVERNANCE REQUEST VALIDATION\n";
try {
    $user = User::first();
    Auth::login($user);
    $spec = Specialty::first();

    $request = DeletionRequest::create([
        'entity_type' => 'specialty',
        'entity_id' => $spec->id,
        'requested_by' => $user->id,
        'cascade_preview_json' => $dm->cascadePreview('specialty', $spec->id),
        'status' => 'pending'
    ]);
    echo "Record Created:\n";
    print_r($request->toArray());
} catch (\Throwable $e) {
    echo "ERROR PHASE 6: " . $e->getMessage() . "\n";
}

echo "\nPHASE 7 — DRIFT PROTECTION TEST\n";
try {
    $spec = Specialty::first();
    // 1. Create request with current state
    $currentSummary = $dm->cascadePreview('specialty', $spec->id);
    $req = DeletionRequest::create([
        'entity_type' => 'specialty',
        'entity_id' => $spec->id,
        'requested_by' => $user->id,
        'cascade_preview_json' => $currentSummary,
        'status' => 'pending'
    ]);
    
    // 2. Approve
    $req->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => $user->id]);
    echo "Approval Snapshot Stored:\n";
    echo json_encode($req->cascade_preview_json, JSON_PRETTY_PRINT) . "\n";

    // 3. INDUCE DRIFT (Manually modify the snapshot in DB to be different from reality)
    $fakeSnapshot = $req->cascade_preview_json;
    $fakeSnapshot['will_delete']['doctors'] += 10; // Change count
    $req->update(['cascade_preview_json' => $fakeSnapshot]);
    
    echo "Execution attempt result (with Induced Drift - Executor: " . $user->id . "):\n";
    try {
        $result = $dwm->executeRequest($req->id, $user->id);
        print_r($result);
    } catch (\Throwable $e) {
        echo "CATCH: " . $e->getMessage() . "\n";
    }
} catch (\Throwable $e) {
    echo "ERROR PHASE 7: " . $e->getMessage() . "\n";
}

echo "\nPHASE 8 — DATABASE IMMUTABILITY PROOF\n";
try {
    echo "Attempting DELETE FROM catalog_audit_logs:\n";
    try {
        DB::statement("DELETE FROM catalog_audit_logs LIMIT 1");
    } catch (\Throwable $e) {
        echo "SQL ERROR: " . $e->getMessage() . "\n";
    }
} catch (\Throwable $e) {
    echo "ERROR PHASE 8: " . $e->getMessage() . "\n";
}
