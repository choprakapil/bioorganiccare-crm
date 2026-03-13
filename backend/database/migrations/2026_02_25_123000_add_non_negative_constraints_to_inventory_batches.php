<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add non-negative constraints to inventory_batches
        // Using raw SQL as Laravel Blueprint doesn't have a direct 'check' method that is database-agnostic
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE inventory_batches ADD CONSTRAINT chk_quantity_remaining_non_negative CHECK (quantity_remaining >= 0)');
            DB::statement('ALTER TABLE inventory_batches ADD CONSTRAINT chk_original_quantity_non_negative CHECK (original_quantity >= 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE inventory_batches DROP CHECK chk_quantity_remaining_non_negative');
            DB::statement('ALTER TABLE inventory_batches DROP CHECK chk_original_quantity_non_negative');
        }
    }
};
