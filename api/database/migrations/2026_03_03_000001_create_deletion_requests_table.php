<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deletion_requests', function (Blueprint $table) {
            $table->id();

            $table->string('entity_type')->index();
            $table->unsignedBigInteger('entity_id')->index();

            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected', 'executed'])->default('pending')->index();

            $table->json('cascade_preview_json');
            $table->text('reason')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('executed_at')->nullable();

            $table->timestamps();

            $table->foreign('requested_by')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();

            $table->foreign('approved_by')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deletion_requests');
    }
};
