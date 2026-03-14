<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure uuid column exists (it might from a previous half-implemented state)
        if (!Schema::hasColumn('invoices', 'uuid')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->after('id');
            });
        }

        // 2. Backfill existing records
        DB::table('invoices')->whereNull('uuid')->chunkById(100, function ($records) {
            foreach ($records as $record) {
                DB::table('invoices')
                    ->where('id', $record->id)
                    ->update(['uuid' => (string) Str::uuid()]);
            }
        });

        // 3. Add constraints
        Schema::table('invoices', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
            $table->unique('uuid', 'idx_invoices_uuid_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('idx_invoices_uuid_unique');
            $table->uuid('uuid')->nullable()->change();
        });
    }
};
