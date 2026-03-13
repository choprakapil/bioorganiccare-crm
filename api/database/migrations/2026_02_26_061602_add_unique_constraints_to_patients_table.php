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
        Schema::table('patients', function (Blueprint $table) {
            // Composite unique index for doctor_id and phone
            $table->unique(['doctor_id', 'phone'], 'patients_doctor_phone_unique');
            
            // Index for doctor_id and name for searchable fields
            $table->index(['doctor_id', 'name'], 'patients_doctor_name_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropUnique('patients_doctor_phone_unique');
            $table->dropIndex('patients_doctor_name_index');
        });
    }
};
