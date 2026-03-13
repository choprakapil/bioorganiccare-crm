<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * ONE-TIME DATA MIGRATION
 *
 * Backfills missing initial inventory_batches rows for inventory items
 * that have stock > 0 but no corresponding batch record.
 *
 * Root cause: InventoryController::store() was setting inventory.stock
 * without creating an InventoryBatch row. This has been fixed in the
 * controller, but pre-existing rows need backfilling.
 *
 * This migration:
 *  - Finds all inventory rows with stock > 0 and no batch
 *  - Creates an 'initial' batch for each
 *  - Runs inside a DB transaction for atomicity
 *  - Logs each created batch to the console
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Find inventory items missing batches
        $orphans = DB::table('inventory as i')
            ->leftJoin('inventory_batches as b', 'b.inventory_id', '=', 'i.id')
            ->whereNull('b.id')
            ->where('i.stock', '>', 0)
            ->whereNull('i.deleted_at')
            ->select('i.id', 'i.doctor_id', 'i.item_name', 'i.stock', 'i.purchase_cost')
            ->get();

        if ($orphans->isEmpty()) {
            echo "  ✓ No inventory items missing batches. Nothing to backfill.\n";
            return;
        }

        echo "  Found {$orphans->count()} inventory item(s) missing initial batches.\n";

        // Step 2: Wrap in transaction for atomicity
        DB::transaction(function () use ($orphans) {
            foreach ($orphans as $item) {
                $batchId = DB::table('inventory_batches')->insertGetId([
                    'inventory_id'       => $item->id,
                    'doctor_id'          => $item->doctor_id,
                    'original_quantity'  => $item->stock,
                    'quantity_remaining' => $item->stock,
                    'unit_cost'          => $item->purchase_cost,
                    'batch_type'         => 'initial',
                    'purchase_reference' => Str::uuid()->toString(),
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);

                echo "  → Backfilled batch #{$batchId} for inventory #{$item->id}"
                   . " \"{$item->item_name}\" (qty: {$item->stock}, cost: {$item->purchase_cost})\n";
            }
        });

        echo "  ✓ Backfill complete. {$orphans->count()} batch(es) created.\n";
    }

    public function down(): void
    {
        // Remove only the backfilled 'initial' batches created by this migration.
        // We identify them by batch_type = 'initial' AND no other batches exist
        // for those inventory items from before this migration ran.
        // Safe approach: delete all 'initial' type batches that have no
        // invoice_item_batch_allocations referencing them (i.e., unused).
        DB::table('inventory_batches')
            ->where('batch_type', 'initial')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('invoice_item_batch_allocations')
                    ->whereColumn('invoice_item_batch_allocations.inventory_batch_id', 'inventory_batches.id');
            })
            ->delete();

        echo "  ✓ Reverted backfilled initial batches (unused ones only).\n";
    }
};
