<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            // 'replenishment' (default) | 'adjustment' | 'initial'
            $table->string('batch_type')->default('replenishment')->after('purchase_reference');
            // Human-readable reason — required for adjustments
            $table->string('adjustment_reason')->nullable()->after('batch_type');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->dropColumn(['batch_type', 'adjustment_reason']);
        });
    }
};
