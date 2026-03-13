<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinical_service_categories', function (Blueprint $table) {
            // Soft delete — existing data unaffected (deleted_at = NULL = active)
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('clinical_service_categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
