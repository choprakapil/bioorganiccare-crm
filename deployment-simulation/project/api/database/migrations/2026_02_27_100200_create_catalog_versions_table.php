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
        Schema::create('catalog_versions', function (Blueprint $table) {
            $table->id();
            $table->enum('entity_type', ['clinical', 'pharmacy']);
            $table->unsignedBigInteger('entity_id');
            $table->unsignedInteger('version_number');
            $table->unsignedBigInteger('changed_by_user_id')->nullable();
            $table->json('old_payload');
            $table->json('new_payload');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_type', 'entity_id']);
            $table->index('version_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_versions');
    }
};
