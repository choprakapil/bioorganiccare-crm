<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_medicines', function (Blueprint $table) {
            $table->string('normalized_name')->nullable()->after('name');
            $table->foreignId('created_by_user_id')->nullable()->after('specialty_id')->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by_user_id')->nullable()->after('created_by_user_id')->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('master_medicines', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropForeign(['approved_by_user_id']);
            $table->dropColumn(['normalized_name', 'created_by_user_id', 'approved_by_user_id']);
        });
    }
};
