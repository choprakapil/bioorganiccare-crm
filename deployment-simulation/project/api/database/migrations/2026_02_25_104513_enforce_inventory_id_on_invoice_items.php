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
            DB::statement('ALTER TABLE invoice_items MODIFY inventory_id BIGINT UNSIGNED NULL');

            DB::statement('ALTER TABLE invoice_items 
                ADD CONSTRAINT fk_invoice_items_inventory 
                FOREIGN KEY (inventory_id) 
                REFERENCES inventory(id) 
                ON DELETE RESTRICT');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE invoice_items DROP FOREIGN KEY fk_invoice_items_inventory');
            DB::statement('ALTER TABLE invoice_items MODIFY inventory_id BIGINT UNSIGNED NULL');
        }
    }
};
