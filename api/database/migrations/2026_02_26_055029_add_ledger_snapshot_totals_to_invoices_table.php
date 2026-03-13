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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('ledger_debit_total', 15, 2)->default(0)->after('balance_due');
            $table->decimal('ledger_credit_total', 15, 2)->default(0)->after('ledger_debit_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['ledger_debit_total', 'ledger_credit_total']);
        });
    }
};
