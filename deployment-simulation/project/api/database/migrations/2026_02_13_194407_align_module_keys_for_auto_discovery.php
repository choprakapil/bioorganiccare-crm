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
        // Align module keys to match Resource names for default auto-discovery
        DB::table('modules')->where('key', 'patient_registry')->update(['key' => 'patients']);
        DB::table('modules')->where('key', 'clinical_services')->update(['key' => 'clinical-catalog']);
        DB::table('modules')->where('key', 'pharmacy')->update(['key' => 'inventory']);
        DB::table('modules')->where('key', 'growth_insights')->update(['key' => 'insights']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('modules')->where('key', 'patients')->update(['key' => 'patient_registry']);
        DB::table('modules')->where('key', 'clinical-catalog')->update(['key' => 'clinical_services']);
        DB::table('modules')->where('key', 'inventory')->update(['key' => 'pharmacy']);
        DB::table('modules')->where('key', 'insights')->update(['key' => 'growth_insights']);
    }
};
