<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

$doctor = User::where('role', 'doctor')->first();
if (!$doctor) { echo "No doctor found.\n"; exit; }

$doctorId = $doctor->id;
$oldPlanId = $doctor->plan_id;

// Create a dummy plan
$newPlan = new SubscriptionPlan();
$newPlan->name = 'Diagnostic Plan';
$newPlan->price = 0;
$newPlan->features = [];
$newPlan->save();
$newPlanId = $newPlan->id;

echo "Doctor ID: $doctorId\n";
echo "Old Plan ID: $oldPlanId\n";

// Get token
$doctor->tokens()->delete();
$token = $doctor->createToken('test')->plainTextToken;

// Clear any existing cache to start clean for test
\Illuminate\Support\Facades\Cache::forget("doctor_modules_{$doctorId}");

// Hit /me BEFORE
$request1 = Request::create('/api/me', 'GET');
$request1->headers->set('Authorization', "Bearer $token");
$request1->headers->set('Accept', "application/json");
$response1 = app()->handle($request1);
$meBefore = json_decode($response1->getContent(), true);

echo "Modules BEFORE: " . implode(', ', $meBefore['enabled_modules'] ?? []) . "\n";

// UPDATE PLAN
$doctor->plan_id = $newPlanId;
$doctor->save();

echo "New Plan ID: $newPlanId\n";

// Hit /me AFTER
$request2 = Request::create('/api/me', 'GET');
$request2->headers->set('Authorization', "Bearer $token");
$request2->headers->set('Accept', "application/json");
$response2 = app()->handle($request2);
$meAfter = json_decode($response2->getContent(), true);

echo "Modules AFTER: " . implode(', ', $meAfter['enabled_modules'] ?? []) . "\n";
echo "Modules Changed Correctly: " . ((implode(',', $meBefore['enabled_modules']??[]) !== implode(',', $meAfter['enabled_modules']??[])) ? 'Yes' : 'No') . "\n";

