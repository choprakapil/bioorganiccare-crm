<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, clean up duplicates for master_medicine_id
        // We keep the one with the highest ID (newest)
        $duplicates = DB::table('inventory')
            ->select('doctor_id', 'master_medicine_id')
            ->whereNotNull('master_medicine_id')
            ->groupBy('doctor_id', 'master_medicine_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $keep = DB::table('inventory')
                ->where('doctor_id', $duplicate->doctor_id)
                ->where('master_medicine_id', $duplicate->master_medicine_id)
                ->orderBy('id', 'desc')
                ->first();

            DB::table('inventory')
                ->where('doctor_id', $duplicate->doctor_id)
                ->where('master_medicine_id', $duplicate->master_medicine_id)
                ->where('id', '!=', $keep->id)
                ->delete();
        }

        Schema::table('inventory', function (Blueprint $table) {
            $table->unique(['doctor_id', 'master_medicine_id'], 'unique_doctor_master_medicine');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropUnique('unique_doctor_master_medicine');
        });
    }
};
