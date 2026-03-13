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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_amount', 10, 2)->default(0);
            if (config('database.default') === 'sqlite') {
                $table->string('status')->default('Unpaid');
            } else {
                $table->enum('status', ['Unpaid', 'Paid', 'Partial', 'Cancelled'])->default('Unpaid');
            }
            $table->string('payment_method')->nullable();
            $table->boolean('is_finalized')->default(false); // Once true, cannot edit
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
