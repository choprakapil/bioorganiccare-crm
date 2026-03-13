<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table("master_medicines", function (Blueprint $table) {
            $table->unique(["specialty_id", "normalized_name"]);
        });
    }
    public function down(): void {
        Schema::table("master_medicines", function (Blueprint $table) {
            $table->dropUnique(["specialty_id", "normalized_name"]);
        });
    }
};
