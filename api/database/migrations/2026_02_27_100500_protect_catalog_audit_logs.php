<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared('
            CREATE TRIGGER prevent_catalog_audit_logs_update
            BEFORE UPDATE ON catalog_audit_logs
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000"
                SET MESSAGE_TEXT = "Updates are not allowed on the catalog_audit_logs table.";
            END
        ');

        DB::unprepared('
            CREATE TRIGGER prevent_catalog_audit_logs_delete
            BEFORE DELETE ON catalog_audit_logs
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000"
                SET MESSAGE_TEXT = "Deletions are not allowed on the catalog_audit_logs table.";
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_catalog_audit_logs_update');
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_catalog_audit_logs_delete');
    }
};
