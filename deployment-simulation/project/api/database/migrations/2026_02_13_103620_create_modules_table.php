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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
        
            // unique system identifier (lowercase, snake_case)
            $table->string('key', 100)->unique();  
        
            // human readable label
            $table->string('name', 150);
        
            $table->text('description')->nullable();
        
            $table->boolean('is_active')->default(true);
        
            $table->timestamps();
        
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
