<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ENTERPRISE LIFECYCLE HARDENING — Phase 1
 *
 * Problem: DB-level ON DELETE CASCADE on specialty_id bypasses Laravel's SoftDeletes.
 * When a specialty is force-deleted at DB level, child rows are hard-deleted without
 * firing Eloquent events, without setting deleted_at, and without respecting observers.
 *
 * Solution Strategy (per table):
 *
 * clinical_catalog         → RESTRICT  (services have financial history in treatments/invoices)
 * clinical_service_categories → RESTRICT (categories own services — graph integrity required)
 * pharmacy_categories      → RESTRICT  (categories own medicines — graph integrity required)
 * master_medicines         → RESTRICT  (medicines have inventory/treatment financial links)
 * service_submissions      → SET NULL  (submissions are community contributions; orphaning is safe)
 *
 * RESTRICT chosen where:
 *   - The child has financial or clinical history attached to it
 *   - OR the child itself has SoftDeletes and should be individually archived
 *
 * SET NULL chosen where:
 *   - The child is reference/contextual data with no financial consequence
 *   - AND the specialty_id is already or can be nullable
 *
 * After this migration, ALL cascade logic is handled by Specialty::boot() model events.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. clinical_catalog ──────────────────────────────────────────────────
        Schema::table('clinical_catalog', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->foreign('specialty_id')
                  ->references('id')->on('specialties')
                  ->restrictOnDelete();
        });

        // ── 2. clinical_service_categories ───────────────────────────────────────
        Schema::table('clinical_service_categories', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->foreign('specialty_id')
                  ->references('id')->on('specialties')
                  ->restrictOnDelete();
        });

        // ── 3. pharmacy_categories ───────────────────────────────────────────────
        Schema::table('pharmacy_categories', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->foreign('specialty_id')
                  ->references('id')->on('specialties')
                  ->restrictOnDelete();
        });

        // ── 4. master_medicines ──────────────────────────────────────────────────
        // specialty_id is nullable here — RESTRICT still works (DB only enforces
        // constraint when the value is non-null)
        Schema::table('master_medicines', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->foreign('specialty_id')
                  ->references('id')->on('specialties')
                  ->restrictOnDelete();
        });

        // ── 5. service_submissions ───────────────────────────────────────────────
        // Submissions are audit-trail reference data; preserving them as orphaned
        // with a null specialty_id is safe and historically valuable.
        Schema::table('service_submissions', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            // Make nullable to support SET NULL
            $table->unsignedBigInteger('specialty_id')->nullable()->change();
            $table->foreign('specialty_id')
                  ->references('id')->on('specialties')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // ── 1. clinical_catalog ──────────────────────────────────────────────────
        Schema::table('clinical_catalog', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->foreign('specialty_id')
                  ->references('id')->on('specialties')
                  ->onDelete('cascade');
        });

        // ── 2. clinical_service_categories ───────────────────────────────────────
        Schema::table('clinical_service_categories', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->foreign('specialty_id')
                  ->references('id')->on('specialties')
                  ->onDelete('cascade');
        });

        // ── 3. pharmacy_categories ───────────────────────────────────────────────
        Schema::table('pharmacy_categories', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->foreign('specialty_id')
                  ->references('id')->on('specialties')
                  ->onDelete('cascade');
        });

        // ── 4. master_medicines ──────────────────────────────────────────────────
        Schema::table('master_medicines', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->foreign('specialty_id')
                  ->references('id')->on('specialties')
                  ->onDelete('cascade');
        });

        // ── 5. service_submissions ───────────────────────────────────────────────
        Schema::table('service_submissions', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->unsignedBigInteger('specialty_id')->nullable(false)->change();
            $table->foreign('specialty_id')
                  ->references('id')->on('specialties')
                  ->onDelete('cascade');
        });
    }
};
