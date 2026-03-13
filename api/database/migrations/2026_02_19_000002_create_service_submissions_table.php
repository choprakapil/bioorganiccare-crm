<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('specialty_id')
                  ->constrained('specialties')
                  ->onDelete('cascade');

            $table->foreignId('submitted_by_user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->string('original_name');
            $table->string('normalized_name');

            $table->foreignId('proposed_type_id')
                  ->nullable()
                  ->constrained('service_types')
                  ->onDelete('set null');

            $table->decimal('proposed_default_fee', 10, 2);

            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('pending');

            $table->text('rejection_reason')->nullable();

            $table->foreignId('reviewed_by_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index for fast admin queue lookups
            $table->index(['status', 'specialty_id']);
            $table->index('submitted_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_submissions');
    }
};
