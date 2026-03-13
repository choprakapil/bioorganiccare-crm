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
        Schema::create('catalog_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('entity_type', ['clinical', 'pharmacy']);
            $table->unsignedBigInteger('entity_id');
            $table->string('action'); // created, updated, archived, restored, force_delete_attempt, force_deleted, activated, deactivated
            $table->unsignedBigInteger('performed_by_user_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
            $table->index('performed_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_audit_logs');
    }
};
