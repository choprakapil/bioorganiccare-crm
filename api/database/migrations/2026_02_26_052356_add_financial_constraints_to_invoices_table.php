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
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE invoices ADD CONSTRAINT chk_paid_amount_non_negative CHECK (paid_amount >= 0)');
            DB::statement('ALTER TABLE invoices ADD CONSTRAINT chk_balance_due_non_negative CHECK (balance_due >= 0)');
            DB::statement('ALTER TABLE invoices ADD CONSTRAINT chk_paid_amount_lte_total CHECK (paid_amount <= total_amount)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE invoices DROP CHECK chk_paid_amount_non_negative');
            DB::statement('ALTER TABLE invoices DROP CHECK chk_balance_due_non_negative');
            DB::statement('ALTER TABLE invoices DROP CHECK chk_paid_amount_lte_total');
        }
    }
};
