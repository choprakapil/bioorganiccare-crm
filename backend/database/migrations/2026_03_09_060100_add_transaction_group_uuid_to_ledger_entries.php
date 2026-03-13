<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Adds transaction_group_uuid to ledger_entries for grouping
 * related entries within the same accounting event.
 *
 * Also backfills existing entries with unique UUIDs.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add the column
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->uuid('transaction_group_uuid')->after('invoice_id')->index();
        });

        // Step 2: Backfill existing rows with unique UUIDs
        // (each existing entry is its own standalone group)
        $entries = DB::table('ledger_entries')->select('id')->get();
        foreach ($entries as $entry) {
            DB::table('ledger_entries')
                ->where('id', $entry->id)
                ->update(['transaction_group_uuid' => Str::uuid()->toString()]);
        }

        echo "  → Backfilled {$entries->count()} ledger entries with transaction_group_uuid.\n";
    }

    public function down(): void
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropIndex(['transaction_group_uuid']);
            $table->dropColumn('transaction_group_uuid');
        });
    }
};
