<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('submission_id')
                  ->constrained('service_submissions')
                  ->onDelete('cascade');

            $table->enum('action', ['submitted', 'approved', 'rejected', 'deleted']);

            $table->foreignId('performed_by_user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->text('notes')->nullable();

            // Audit logs are append-only — no updated_at, no soft delete
            $table->timestamp('created_at')->useCurrent();

            // Index for per-submission history reads
            $table->index(['submission_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_audit_logs');
    }
};
