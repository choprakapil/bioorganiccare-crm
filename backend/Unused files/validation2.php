<?php

$out = [];
$superAdmin = App\Models\User::where('role', 'super_admin')->first();
$approver = App\Models\User::where('id', '!=', $superAdmin->id)->first() ?? App\Models\User::factory()->create();

// Reset data
App\Models\DeletionRequest::truncate();

// 1. Dual toggle ON -> self-approval blocked
$out[] = "-----------------------------------------";
$out[] = "PHASE 1 — DUAL TOGGLE ON (SELF APPROVAL BLOCKED)";
$out[] = "-----------------------------------------";
app(App\Services\SystemSettingService::class)->set('dual_admin_approval_enabled', '1');
$req1 = app(App\Services\DeletionWorkflowManager::class)->createRequest('specialty', 12, $superAdmin->id);
try {
    app(App\Services\DeletionWorkflowManager::class)->approveRequest($req1->id, $superAdmin->id);
    $out[] = "FAIL: Self approval succeeded when it should be blocked.";
} catch (\Exception $e) {
    if (strpos($e->getMessage(), 'Dual approval is enabled.') !== false) {
        $out[] = "PASS: Self approval blocked with correct message.";
    } else {
        $out[] = "WARN: Exception was thrown but message doesn't match: " . $e->getMessage();
    }
}


// 2. Dual toggle OFF -> self-approval allowed
$out[] = "\n-----------------------------------------";
$out[] = "PHASE 2 — DUAL TOGGLE OFF (SELF APPROVAL ALLOWED)";
$out[] = "-----------------------------------------";
app(App\Services\SystemSettingService::class)->set('dual_admin_approval_enabled', '0');
try {
    app(App\Services\DeletionWorkflowManager::class)->approveRequest($req1->id, $superAdmin->id);
    $out[] = "PASS: Self approval succeeded.";
} catch (\Exception $e) {
    $out[] = "FAIL: Exception thrown: " . $e->getMessage();
}


// 3. Snapshot drift still works
$out[] = "\n-----------------------------------------";
$out[] = "PHASE 3 — SNAPSHOT DRIFT STILL WORKS";
$out[] = "-----------------------------------------";
try {
    // create drift
    app()->instance('deletion_context', true);
    $user = App\Models\User::create(['name'=>'b', 'email'=>uniqid().'@b.com', 'password'=>'123', 'role'=>'doctor', 'specialty_id'=>12]);
    app()->forgetInstance('deletion_context');

    app(App\Services\DeletionWorkflowManager::class)->executeRequest($req1->id, $superAdmin->id);
} catch (\Exception $e) {
    if (strpos($e->getMessage(), 'SNAPSHOT MISMATCH') !== false) {
        $out[] = "PASS: Drift detected.";
    } else {
        $out[] = "WARN: Different exception: " . $e->getMessage();
    }
}
// Clean up drift user
app()->instance('deletion_context', true);
$user->forceDelete();
app()->forgetInstance('deletion_context');


// 4. Duplicate prevention still works
$out[] = "\n-----------------------------------------";
$out[] = "PHASE 4 — DUPLICATE PREVENTION";
$out[] = "-----------------------------------------";
App\Models\DeletionRequest::truncate();
$req2 = app(App\Services\DeletionWorkflowManager::class)->createRequest('specialty', 12, $superAdmin->id);
try {
    app(App\Services\DeletionWorkflowManager::class)->createRequest('specialty', 12, $approver->id);
    $out[] = "FAIL: Duplicate request was allowed.";
} catch (\Exception $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        $out[] = "PASS: Duplicate request blocked.";
    } else {
        $out[] = "WARN: Different exception: " . $e->getMessage();
    }
}


// 5. Execution status transition unchanged
$out[] = "\n-----------------------------------------";
$out[] = "PHASE 5 — EXECUTION TRANSITION";
$out[] = "-----------------------------------------";
$req2 = app(App\Services\DeletionWorkflowManager::class)->approveRequest($req2->id, $approver->id);
try {
    $res = app(App\Services\DeletionWorkflowManager::class)->executeRequest($req2->id, $superAdmin->id);
    $req2->refresh();
    if ($req2->status === 'executed') {
        $out[] = "PASS: Request status transitioned to executed.";
    } else {
        $out[] = "FAIL: Status is " . $req2->status;
    }
} catch (\Exception $e) {
    $out[] = "FAIL: Execution failed: " . $e->getMessage();
}


// 6. ProtectedDeletion still blocks direct delete
$out[] = "\n-----------------------------------------";
$out[] = "PHASE 6 — PROTECTED DELETION (ORM HARDENING)";
$out[] = "-----------------------------------------";
app()->instance('deletion_context', true);
$spec = App\Models\Specialty::first() ?? App\Models\Specialty::forceCreate(['name' => 'Test Restore Spec']);
app()->forgetInstance('deletion_context');

try {
    $spec->delete();
    $out[] = "FAIL: Direct delete succeeded.";
} catch (\Exception $e) {
    if (strpos($e->getMessage(), 'Direct model deletion is forbidden') !== false) {
        $out[] = "PASS: Direct delete blocked.";
    } else {
        $out[] = "WARN: Different exception: " . $e->getMessage();
    }
}

echo implode("\n", $out) . "\n";
