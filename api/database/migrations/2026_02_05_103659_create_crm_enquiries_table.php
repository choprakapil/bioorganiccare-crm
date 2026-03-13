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
        Schema::create('crm_enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('clinic_name');
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->string('city');
            $table->string('practice_type')->nullable();
            $table->text('message')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser_name')->nullable();
            $table->string('os_name')->nullable();
            $table->string('device_type')->nullable();
            $table->text('referrer')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_enquiries');
    }
};
