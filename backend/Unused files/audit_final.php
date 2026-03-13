<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Specialty;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "========== SPECIALTY DEFAULT PLAN ==========\n";
$specialties = Specialty::all();
foreach ($specialties as $specialty) {
    echo "Specialty: {$specialty->name} | Default Plan ID: " . ($specialty->default_plan_id ?? 'NULL') . "\n";
}

echo "\n========== PLAN MODULE COUNTS ==========\n";
$plans = SubscriptionPlan::all();
foreach ($plans as $plan) {
    $count = DB::table('plan_module')->where('plan_id', $plan->id)->count();
    echo "Plan: {$plan->name} | Modules: {$count}\n";
}

echo "\n========== SPECIALTY MODULE COUNTS ==========\n";
foreach ($specialties as $specialty) {
    $count = DB::table('specialty_module')->where('specialty_id', $specialty->id)->count();
    echo "Specialty: {$specialty->name} | Modules: {$count}\n";
}

echo "\n========== DOCTOR VALIDATION ==========\n";
$doctor = User::where('role','doctor')->first();
if ($doctor) {
    echo "Doctor Plan: " . ($doctor->plan->name ?? 'NONE') . "\n";
    echo "Doctor Specialty: " . ($doctor->specialty->name ?? 'NONE') . "\n";
    echo "Enabled Modules Count: " . count($doctor->enabled_modules) . "\n";
    echo "Enabled Modules: " . implode(', ', $doctor->enabled_modules) . "\n";
} else {
    echo "No doctor found.\n";
}
