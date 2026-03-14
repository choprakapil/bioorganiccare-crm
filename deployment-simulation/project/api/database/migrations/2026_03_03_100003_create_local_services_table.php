<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create("local_services", function (Blueprint $table) {
            $table->id();
            $table->foreignId("doctor_id")->constrained("users")->onDelete("cascade");
            $table->foreignId("specialty_id")->constrained("specialties")->onDelete("cascade");
            $table->string("item_name");
            $table->string("normalized_name");
            $table->string("type");
            $table->decimal("default_fee", 10, 2);
            $table->boolean("is_promoted")->default(false);
            $table->foreignId("promoted_catalog_id")->nullable()->constrained("clinical_catalog")->onDelete("set null");
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void {
        Schema::dropIfExists("local_services");
    }
};
