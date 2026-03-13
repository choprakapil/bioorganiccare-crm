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
        Schema::create('invoice_item_batch_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_item_id')->constrained('invoice_items')->onDelete('cascade');
            $table->foreignId('inventory_batch_id')->constrained('inventory_batches')->onDelete('cascade');
            $table->integer('quantity_taken');
            $table->decimal('unit_cost', 10, 4);
            $table->timestamps();

            // Indexes for faster lookups during audit/reversal
            $table->index('invoice_item_id');
            $table->index('inventory_batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_item_batch_allocations');
    }
};
