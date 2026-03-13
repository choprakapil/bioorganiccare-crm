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
        Schema::table('treatments', function (Blueprint $table) {
            $table->unsignedBigInteger('inventory_id')->nullable()->after('catalog_id');
            $table->foreign('inventory_id')->references('id')->on('inventory')->nullOnDelete();
            
            $table->integer('quantity')->default(1)->after('fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->dropForeign(['inventory_id']);
            $table->dropColumn('inventory_id');
            $table->dropColumn('quantity');
        });
    }
};
