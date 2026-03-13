<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->unsignedBigInteger('master_medicine_id')->nullable()->change();
        });
        
        $fks = Schema::getForeignKeys('inventory');
        $hasFk = false;
        foreach ($fks as $fk) {
            if (in_array('master_medicine_id', $fk['columns'])) {
                $hasFk = true;
                break;
            }
        }
        
        if (!$hasFk) {
            Schema::table('inventory', function (Blueprint $table) {
                $table->foreign('master_medicine_id')
                      ->references('id')
                      ->on('master_medicines')
                      ->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropForeign(['master_medicine_id']);
            $table->unsignedBigInteger('master_medicine_id')->nullable(false)->change();
            $table->foreign('master_medicine_id')
                  ->references('id')
                  ->on('master_medicines')
                  ->onDelete('cascade');
        });
    }
};
