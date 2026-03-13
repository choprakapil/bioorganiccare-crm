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
        $indexesInvoices = Schema::getIndexes('invoices');
        if (!collect($indexesInvoices)->pluck('name')->contains('idx_invoices_enterprise_covering')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->index(
                    ['doctor_id', 'status', 'created_at', 'total_amount', 'paid_amount', 'balance_due'], 
                    'idx_invoices_enterprise_covering'
                );
            });
        }

        $indexesLedger = Schema::getIndexes('ledger_entries');
        if (!collect($indexesLedger)->pluck('name')->contains('idx_ledger_enterprise_covering')) {
            Schema::table('ledger_entries', function (Blueprint $table) {
                $table->index(
                    ['invoice_id', 'type', 'amount', 'created_at'], 
                    'idx_ledger_enterprise_covering'
                );
            });
        }

        $indexesItems = Schema::getIndexes('invoice_items');
        if (!collect($indexesItems)->pluck('name')->contains('idx_items_enterprise_cogs_covering')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->index(
                    ['invoice_id', 'type', 'quantity', 'unit_cost'], 
                    'idx_items_enterprise_cogs_covering'
                );
            });
        }

        $indexesAudit = Schema::getIndexes('audit_logs');
        if (!collect($indexesAudit)->pluck('name')->contains('idx_audit_logs_cursor_pagination')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index(['created_at', 'id'], 'idx_audit_logs_cursor_pagination');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('idx_invoices_enterprise_covering');
        });
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropIndex('idx_ledger_enterprise_covering');
        });
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropIndex('idx_items_enterprise_cogs_covering');
        });
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_logs_cursor_pagination');
        });
    }
};
