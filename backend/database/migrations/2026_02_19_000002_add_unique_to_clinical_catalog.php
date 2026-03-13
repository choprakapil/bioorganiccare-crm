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
        Schema::table('clinical_catalog', function (Blueprint $table) {
            $table->unique(['specialty_id', 'normalized_name'], 'clinical_catalog_unique_specialty_normalized');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinical_catalog', function (Blueprint $table) {
            $table->dropUnique('clinical_catalog_unique_specialty_normalized');
        });
    }
};
