<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinical_catalog', function (Blueprint $table) {
            // Normalization support — nullable so existing rows are unaffected
            $table->string('normalized_name')->nullable()->after('item_name');

            // Attribution columns — nullable, safe for existing rows
            $table->foreignId('created_by_user_id')
                  ->nullable()
                  ->after('category_id')
                  ->constrained('users')
                  ->onDelete('set null');

            $table->foreignId('approved_by_user_id')
                  ->nullable()
                  ->after('created_by_user_id')
                  ->constrained('users')
                  ->onDelete('set null');

            $table->timestamp('approved_at')->nullable()->after('approved_by_user_id');

            // Soft delete — existing data unaffected (deleted_at = NULL means active)
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('clinical_catalog', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropForeign(['approved_by_user_id']);
            $table->dropColumn([
                'normalized_name',
                'created_by_user_id',
                'approved_by_user_id',
                'approved_at',
                'deleted_at',
            ]);
        });
    }
};
