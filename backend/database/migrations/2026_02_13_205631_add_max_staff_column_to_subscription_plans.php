<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1️⃣ Add column (nullable first for safe migration)
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->integer('max_staff')->nullable()->after('price');
        });

        // 2️⃣ Backfill data from legacy JSON (features->limits->max_staff OR features->max_staff)
        $plans = DB::table('subscription_plans')->get();

        foreach ($plans as $plan) {

            $features = json_decode($plan->features ?? '{}', true);

            $maxStaff = null;

            if (isset($features['limits']['max_staff'])) {
                $maxStaff = $features['limits']['max_staff'];
            } elseif (isset($features['max_staff'])) {
                $maxStaff = $features['max_staff'];
            }

            if ($maxStaff !== null) {
                DB::table('subscription_plans')
                    ->where('id', $plan->id)
                    ->update([
                        'max_staff' => (int) $maxStaff
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('max_staff');
        });
    }
};