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
        Schema::table('specialties', function (Blueprint $table) {
            $table->unsignedBigInteger('default_plan_id')->nullable()->after('name');

            // Explicit Index Name
            // Ensures safer database migrations and avoids auto-generated name conflicts
            $table->index('default_plan_id', 'specialties_default_plan_id_index');

            // Explicit Foreign Key Name
            // Ensures reliability during rollbacks and avoids truncation issues
            // ON DELETE RESTRICT: Critical for data integrity. 
            // We cannot allow a SubscriptionPlan to be deleted if it is set as a default for a Specialty.
            $table->foreign('default_plan_id', 'specialties_default_plan_id_fk')
                  ->references('id')
                  ->on('subscription_plans')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('specialties', function (Blueprint $table) {
            // Drop Foreign Key using explicit name
            $table->dropForeign('specialties_default_plan_id_fk');
            
            // Drop Index using explicit name
            $table->dropIndex('specialties_default_plan_id_index');
            
            // Drop the column
            $table->dropColumn('default_plan_id');
        });
    }
};
