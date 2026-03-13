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
        // Align DB keys with Codebase/Seeder keys
        $map = [
            'clinical-catalog' => 'clinical_services',
            'inventory'        => 'pharmacy',
            'patients'         => 'patient_registry',
            'insights'         => 'growth_insights',
        ];

        foreach ($map as $old => $new) {
            DB::table('modules')->where('key', $old)->update(['key' => $new]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $map = [
            'clinical_services' => 'clinical-catalog',
            'pharmacy'          => 'inventory',
            'patient_registry'  => 'patients',
            'growth_insights'   => 'insights',
        ];

        foreach ($map as $current => $old) {
            DB::table('modules')->where('key', $current)->update(['key' => $old]);
        }
    }
};
