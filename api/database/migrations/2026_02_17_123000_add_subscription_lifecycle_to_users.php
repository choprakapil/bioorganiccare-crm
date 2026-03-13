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
        Schema::table('users', function (Blueprint $table) {
            // Subscription Lifecycle Columns
            $table->timestamp('subscription_started_at')->nullable()->index();
            $table->timestamp('subscription_renews_at')->nullable()->index();
            $table->timestamp('subscription_grace_ends_at')->nullable();
            
            // Billing Configuration (Enum with Defaults)
            $table->enum('billing_interval', ['monthly', 'yearly'])->default('monthly');
            
            // Lifecycle Status (Enum with Defaults)
            $table->enum('subscription_status', ['active', 'past_due', 'expired', 'cancelled', 'lifetime'])
                  ->default('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_started_at',
                'subscription_renews_at',
                'subscription_grace_ends_at',
                'billing_interval',
                'subscription_status'
            ]);
        });
    }
};
