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
        // 1. Audit Invoices
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            }
            // Ensure fast lookup by doctor + patient
            $table->index(['doctor_id', 'patient_id'], 'idx_invoices_doctor_patient');
        });

        // 2. Audit AuditLogs (Scaling for 100k+ logs)
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['user_id', 'action', 'created_at'], 'idx_audit_logs_scaling');
        });

        // 3. Audit Specialties
        Schema::table('specialties', function (Blueprint $table) {
            // Already unique? Let's be sure.
            try {
                $table->unique('slug');
            } catch (\Exception $e) {}
        });

        // 4. Audit Subscription Plans
        Schema::table('subscription_plans', function (Blueprint $table) {
            // Uniqueness per specialty + tier
            $table->unique(['specialty_id', 'tier'], 'unique_specialty_tier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropIndex('idx_invoices_doctor_patient');
            $table->dropColumn('uuid');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_logs_scaling');
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropUnique('unique_specialty_tier');
        });
    }
};
