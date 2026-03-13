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
        Schema::table('plan_module', function (Blueprint $table) {
            $table->renameColumn('subscription_plan_id', 'plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_module', function (Blueprint $table) {
            $table->renameColumn('plan_id', 'subscription_plan_id');
        });
    }
};
