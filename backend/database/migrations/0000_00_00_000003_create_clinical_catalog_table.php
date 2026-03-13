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
        Schema::create('clinical_catalog', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialty_id')->constrained('specialties')->onDelete('cascade');
            $table->string('item_name');
            $table->enum('type', ['Treatment', 'Medicine']);
            $table->decimal('default_fee', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_catalog');
    }
};
