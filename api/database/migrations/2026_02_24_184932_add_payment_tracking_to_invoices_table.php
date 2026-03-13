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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('paid_amount', 10, 2)->default(0)->after('total_amount');
            $table->decimal('balance_due', 10, 2)->default(0)->after('paid_amount');
        });

        // Step 2: Backfill existing data safely
        DB::table('invoices')->where('status', 'Paid')->update([
            'paid_amount' => DB::raw('total_amount'),
            'balance_due' => 0
        ]);

        DB::table('invoices')->where('status', 'Unpaid')->update([
            'paid_amount' => 0,
            'balance_due' => DB::raw('total_amount')
        ]);

        DB::table('invoices')->where('status', 'Cancelled')->update([
            'paid_amount' => 0,
            'balance_due' => 0
        ]);
        
        // Final sanity check for Partial (though none exist)
        // We leave them as default (0, 0) or handle them if they appear
        DB::table('invoices')->where('status', 'Partial')->update([
            'balance_due' => DB::raw('total_amount')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'balance_due']);
        });
    }
};
