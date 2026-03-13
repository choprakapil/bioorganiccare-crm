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
        Schema::create('pharmacy_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialty_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Add foreign key to master_medicines
        Schema::table('master_medicines', function (Blueprint $table) {
            $table->foreignId('pharmacy_category_id')->nullable()->after('category')->constrained('pharmacy_categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_medicines', function (Blueprint $table) {
            $table->dropForeign(['pharmacy_category_id']);
            $table->dropColumn('pharmacy_category_id');
        });

        Schema::dropIfExists('pharmacy_categories');
    }
};
