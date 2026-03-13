<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Master Medicines (Source of Truth)
        Schema::create('master_medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable(); // e.g., Tablet, Syrup
            $table->string('unit')->nullable(); // e.g., Strip, Bottle
            $table->decimal('default_purchase_price', 10, 2)->nullable();
            $table->decimal('default_selling_price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Medicine Suggestions (Governance)
        Schema::create('medicine_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('unit')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        // 3. Update Inventory Table
        Schema::table('inventory', function (Blueprint $table) {
            $table->foreignId('master_medicine_id')->nullable()->after('catalog_id')->constrained('master_medicines')->onDelete('cascade');
        });

        // 4. DATA MIGRATION (Zero Data Loss)
        // We must inspect all existing inventory items and create master records for them
        $inventoryItems = \DB::table('inventory')->get();

        foreach ($inventoryItems as $item) {
            // Check if master medicine already exists by name
            $masterId = \DB::table('master_medicines')->where('name', $item->item_name)->value('id');

            if (!$masterId) {
                // Create new master medicine
                $masterId = \DB::table('master_medicines')->insertGetId([
                    'name' => $item->item_name,
                    'category' => 'General', // Default
                    'unit' => 'Unit', // Default
                    'default_purchase_price' => $item->purchase_cost,
                    'default_selling_price' => $item->sale_price,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Link inventory to master
            \DB::table('inventory')
                ->where('id', $item->id)
                ->update(['master_medicine_id' => $masterId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropForeign(['master_medicine_id']);
            $table->dropColumn('master_medicine_id');
        });

        Schema::dropIfExists('medicine_suggestions');
        Schema::dropIfExists('master_medicines');
    }
};
