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
            $table->string('role_type')->nullable()->after('role')->comment('assistant or receptionist for staff');
        });
        
        // Backfill existing staff
        \App\Models\User::where('role', 'staff')->update(['role_type' => 'assistant']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role_type');
        });
    }
};
