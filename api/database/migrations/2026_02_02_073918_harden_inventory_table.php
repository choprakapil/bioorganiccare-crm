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
        // 1. Safety Check: Verify no NULL values exist
        $nullCount = DB::table('inventory')->whereNull('master_medicine_id')->count();
        if ($nullCount > 0) {
            throw new \Exception("Cannot harden inventory table: Found $nullCount records with NULL master_medicine_id. Please clean data first.");
        }

        Schema::table('inventory', function (Blueprint $table) {
            // 2. Make master_medicine_id NOT NULL
            // Note: FK 'inventory_master_medicine_id_foreign' already exists from previous migration.
            // We just need to enforce strict data integrity.
            $table->unsignedBigInteger('master_medicine_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->unsignedBigInteger('master_medicine_id')->nullable()->change();
        });
    }
};
