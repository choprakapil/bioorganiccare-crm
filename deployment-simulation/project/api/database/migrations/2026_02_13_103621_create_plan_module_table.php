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
        Schema::create('plan_module', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('subscription_plan_id')
                  ->constrained()
                  ->cascadeOnDelete();
        
            $table->foreignId('module_id')
                  ->constrained()
                  ->cascadeOnDelete();
        
            $table->boolean('enabled')->default(true);
        
            $table->timestamps();
        
            // Prevent duplicate entries
            $table->unique(['subscription_plan_id', 'module_id']);
        
            $table->index('enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_module');
    }
};
