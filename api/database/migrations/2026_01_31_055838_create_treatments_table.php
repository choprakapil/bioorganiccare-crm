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
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('catalog_id')->nullable()->constrained('clinical_catalog')->onDelete('set null');
            $table->string('procedure_name'); // Denormalized for snapshots
            $table->string('teeth')->nullable(); // For Dental
            $table->text('notes')->nullable();
            $table->enum('status', ['Proposed', 'Completed'])->default('Proposed');
            $table->decimal('fee', 10, 2)->default(0);
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
