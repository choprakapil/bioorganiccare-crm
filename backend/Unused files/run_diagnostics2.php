<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

$admin = User::where('role', 'super_admin')->first();
if (!$admin) { echo "No admin found.\n"; exit; }

$doctor = User::where('role', 'doctor')->first();
if (!$doctor) { echo "No doctor found.\n"; exit; }

$doctorId = $doctor->id;
$oldPlanId = $doctor->plan_id;

$newPlan = new SubscriptionPlan();
$newPlan->name = 'Diagnostic Plan v2';
$newPlan->price = 0;
$newPlan->features = [];
$newPlan->save();
$newPlanId = $newPlan->id;

// Get token
$admin->tokens()->delete();
$token = $admin->createToken('test')->plainTextToken;

// Hit AdminSubscriptionController@updatePlan
$request = Request::create("/api/admin/subscriptions/{$doctorId}?plan_id={$newPlanId}", 'PATCH', ['plan_id' => $newPlanId]);
$request->headers->set('Authorization', "Bearer $token");
$request->headers->set('Accept', "application/json");

// Execute the request explicitly to trigger the controller
$response = app()->handle($request);
echo "Response status: " . $response->getStatusCode() . "\n";
