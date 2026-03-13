<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create("local_medicines", function (Blueprint $table) {
            $table->id();
            $table->foreignId("doctor_id")->constrained("users")->onDelete("cascade");
            $table->foreignId("specialty_id")->constrained("specialties")->onDelete("cascade");
            $table->string("item_name");
            $table->string("normalized_name");
            $table->decimal("buy_price", 10, 2)->nullable();
            $table->decimal("sell_price", 10, 2)->nullable();
            $table->boolean("is_promoted")->default(false);
            $table->foreignId("promoted_master_id")->nullable()->constrained("master_medicines")->onDelete("set null");
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void {
        Schema::dropIfExists("local_medicines");
    }
};
