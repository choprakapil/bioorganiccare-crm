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
        Schema::table('specialties', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->json('features')->nullable()->after('slug');
            $table->json('capabilities')->nullable()->after('features');
            $table->boolean('is_active')->default(true)->after('capabilities');
        });

        // Seed Default Dental Config
        DB::table('specialties')->where('name', 'Dental')->update([
            'slug' => 'dental',
            'features' => json_encode([
                'patient_registry' => true,
                'clinical_services' => true,
                'pharmacy' => true,
                'billing' => true,
                'expenses' => true,
                'appointments' => true,
                'growth_insights' => true,
            ]),
            'capabilities' => json_encode([
                'supports_teeth_chart' => true,
                'supports_procedures' => true,
                'supports_medicines' => true
            ]),
            'is_active' => true
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('specialties', function (Blueprint $table) {
            $table->dropColumn(['slug', 'features', 'capabilities', 'is_active']);
        });
    }
};
