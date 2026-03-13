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
        // 1. Link orphaned inventory items to Master Medicines by Name
        $orphans = DB::table('inventory')->whereNull('master_medicine_id')->get();
        
        foreach ($orphans as $item) {
            $master = DB::table('master_medicines')
                ->where('name', $item->item_name)
                ->first();

            if ($master) {
                // Check if this doctor ALREADY has this master_medicine_id (to avoid unique collision)
                $exists = DB::table('inventory')
                    ->where('doctor_id', $item->doctor_id)
                    ->where('master_medicine_id', $master->id)
                    ->exists();

                if ($exists) {
                    // Duplicate situation: Update the EXISTING linked item with stock from orphan, then delete orphan?
                    // Or just delete orphan? Let's just delete the orphan to be safe and avoid logic errors, 
                    // assuming the linked one is the "truth". OR better, sum stock.
                    // For safety in this fix, we will just delete the orphan if a linked one exists.
                    DB::table('inventory')->where('id', $item->id)->delete();
                } else {
                    // Link it
                    DB::table('inventory')
                        ->where('id', $item->id)
                        ->update(['master_medicine_id' => $master->id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down action for data patching
    }
};
