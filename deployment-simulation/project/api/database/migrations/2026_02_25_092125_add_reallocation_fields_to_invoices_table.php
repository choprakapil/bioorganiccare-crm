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
            $table->boolean('requires_reallocation')->default(false)->after('status');
            $table->uuid('reallocation_token')->nullable()->after('requires_reallocation');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE invoices MODIFY status ENUM(
                'Unpaid',
                'Paid',
                'Partial',
                'Cancelled',
                'ReallocationRequired'
            ) NOT NULL DEFAULT 'Unpaid'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['requires_reallocation', 'reallocation_token']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE invoices MODIFY status ENUM(
                'Unpaid',
                'Paid',
                'Partial',
                'Cancelled'
            ) NOT NULL DEFAULT 'Unpaid'");
        }
    }
};
