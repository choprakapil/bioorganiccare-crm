<?php
use Illuminate\Support\Facades\DB;

try {
    /*
    Step 1: Remove plan_module pivot data
    */
    DB::table('plan_module')->truncate();
    echo 'plan_module cleared'.PHP_EOL;

    /*
    Step 2: Unassign doctors from plans
    */
    \App\Models\User::whereNotNull('plan_id')->update(['plan_id' => null]);
    echo 'Doctors unassigned from plans'.PHP_EOL;

    /*
    Step 3: Delete all subscription plans
    */
    \App\Models\SubscriptionPlan::truncate();
    echo 'All subscription plans deleted'.PHP_EOL;
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
