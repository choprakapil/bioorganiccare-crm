<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubscriptionPlan;
use App\Models\Specialty;
use App\Models\User;

echo "--- STARTING PLAN RESTRUCTURE ---\n";

// Phase 2: Duplicate Plans per Specialty
$oldPlans = SubscriptionPlan::whereNull('specialty_id')->get();
$specialties = Specialty::all();

foreach ($specialties as $specialty) {
    foreach ($oldPlans as $plan) {
        // Check if already duplicated to avoid duplicates on re-run
        $exists = SubscriptionPlan::where('name', $plan->name)
            ->where('specialty_id', $specialty->id)
            ->exists();
            
        if (!$exists) {
            SubscriptionPlan::create([
                'name' => $plan->name,
                'price' => $plan->price,
                'max_patients' => $plan->max_patients,
                'max_appointments_monthly' => $plan->max_appointments_monthly,
                'features' => $plan->features, // JSON cast handled by model
                'specialty_id' => $specialty->id
            ]);
            echo "Created duplicate {$plan->name} for {$specialty->name}\n";
        }
    }
}

// Phase 3: Re-link Doctors to new plans
foreach (User::where('role', 'doctor')->get() as $doctor) {
    if (!$doctor->plan_id || !$doctor->specialty_id) continue;

    $currentPlan = SubscriptionPlan::find($doctor->plan_id);
    
    // Only update if current plan is a global plan (no specialty_id)
    if ($currentPlan && is_null($currentPlan->specialty_id)) {
        $newPlan = SubscriptionPlan::where('name', $currentPlan->name)
            ->where('specialty_id', $doctor->specialty_id)
            ->first();

        if ($newPlan) {
            $doctor->plan_id = $newPlan->id;
            $doctor->save();
            echo "Re-linked Doctor {$doctor->name} to {$newPlan->name} (Specialty: {$doctor->specialty->name})\n";
        }
    }
}

// Phase 3.5: Cleanup non-doctor assignments (e.g. Super Admin)
User::whereNotIn('role', ['doctor', 'staff'])->update(['plan_id' => null]);

// Phase 4: Remove Old Global Plans
$deleted = SubscriptionPlan::whereNull('specialty_id')->delete();
echo "Removed {$deleted} old global plans.\n";

echo "--- RESTRUCTURE COMPLETE ---\n";
