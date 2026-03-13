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
        Schema::table('users', function (Blueprint $table) {
            $table->string('clinic_name')->nullable()->after('name');
            $table->string('clinic_logo')->nullable()->after('clinic_name');
            $table->string('brand_color')->default('#4f46e5')->after('clinic_logo'); // Default Aura Primary
            $table->string('brand_secondary_color')->default('#f8fafc')->after('brand_color');
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->integer('max_patients')->default(-1)->after('price'); // -1 for unlimited
            $table->integer('max_appointments_monthly')->default(-1)->after('max_patients');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['clinic_name', 'clinic_logo', 'brand_color', 'brand_secondary_color']);
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['max_patients', 'max_appointments_monthly']);
        });
    }
};
