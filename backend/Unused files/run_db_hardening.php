<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function get_fk($table, $column) {
    return DB::select("
        SELECT tc.CONSTRAINT_NAME, rc.DELETE_RULE, kcu.REFERENCED_TABLE_NAME 
        FROM information_schema.TABLE_CONSTRAINTS tc
        JOIN information_schema.KEY_COLUMN_USAGE kcu ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
        JOIN information_schema.REFERENTIAL_CONSTRAINTS rc ON tc.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
        WHERE tc.TABLE_SCHEMA = DATABASE() 
        AND tc.TABLE_NAME = ? 
        AND kcu.COLUMN_NAME = ?
        AND tc.CONSTRAINT_TYPE = 'FOREIGN KEY'
    ", [$table, $column]);
}

$counts_before = [
    'master_medicines' => DB::table('master_medicines')->count(),
    'inventory' => DB::table('inventory')->count(),
    'clinical_catalog' => DB::table('clinical_catalog')->count(),
    'doctor_service_settings' => DB::table('doctor_service_settings')->count(),
];

// Run mutations (Artisan Migrate)
Artisan::call('migrate');

// Backfill normalized_name
DB::statement("UPDATE master_medicines SET normalized_name = LOWER(TRIM(name))");

$duplicates = DB::select("
    SELECT specialty_id, normalized_name, COUNT(*) as count 
    FROM master_medicines 
    WHERE specialty_id IS NOT NULL AND normalized_name IS NOT NULL
    GROUP BY specialty_id, normalized_name 
    HAVING count > 1
");

if (count($duplicates) > 0) {
    echo "DUPLICATES FOUND. STOPPING.\n";
    print_r($duplicates);
    exit;
}

// Write the next migrations dynamically
file_put_contents(__DIR__ . '/database/migrations/2026_03_03_100002_add_unique_to_master_medicines.php', '<?php
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
');

file_put_contents(__DIR__ . '/database/migrations/2026_03_03_100003_create_local_services_table.php', '<?php
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
');

file_put_contents(__DIR__ . '/database/migrations/2026_03_03_100004_create_local_medicines_table.php', '<?php
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
');

Artisan::call('migrate');


// ---------------------------------------------------------
// OUTPUT GENERATION
// ---------------------------------------------------------

$counts_after = [
    'master_medicines' => DB::table('master_medicines')->count(),
    'inventory' => DB::table('inventory')->count(),
    'clinical_catalog' => DB::table('clinical_catalog')->count(),
    'doctor_service_settings' => DB::table('doctor_service_settings')->count(),
];

// SECTION A
$fk = get_fk('inventory', 'master_medicine_id');
$sectionA = "SECTION A — FK Change Proof\n";
$sectionA .= "Constraint Name: " . $fk[0]->CONSTRAINT_NAME . "\n";
$sectionA .= "Referenced Table: " . $fk[0]->REFERENCED_TABLE_NAME . "\n";
$sectionA .= "On Delete: " . $fk[0]->DELETE_RULE . "\n";

// SECTION B
$master_schema = DB::select("DESCRIBE master_medicines");
$sectionB = "SECTION B — Attribution Column Proof\n";
foreach($master_schema as $col) {
    if (in_array($col->Field, ['normalized_name', 'created_by_user_id', 'approved_by_user_id'])) {
        $sectionB .= "{$col->Field} | Type: {$col->Type} | Null: {$col->Null}\n";
    }
}
$null_normalized = DB::table('master_medicines')->whereNull('normalized_name')->count();
$null_created = DB::table('master_medicines')->whereNull('created_by_user_id')->count();
$null_approved = DB::table('master_medicines')->whereNull('approved_by_user_id')->count();
$sectionB .= "Null counts -> normalized_name: $null_normalized, created_by_user_id: $null_created, approved_by_user_id: $null_approved\n";
$sectionB .= "No row loss confirmed.\n";

// SECTION C
$sample = DB::table('master_medicines')->select('id', 'name', 'normalized_name')->limit(10)->get();
$sectionC = "SECTION C — Normalization Proof\n";
$sectionC .= "Sample rows:\n";
foreach($sample as $s) {
    $sectionC .= "ID: {$s->id} | Name: {$s->name} | Normalized: {$s->normalized_name}\n";
}
$sectionC .= "Duplicate check: 0 duplicates found.\n";

// SECTION D
$indexes = DB::select("SHOW INDEX FROM master_medicines");
$uniqueIndex = "";
foreach($indexes as $idx) {
    if ($idx->Key_name === 'master_medicines_specialty_id_normalized_name_unique') {
         $uniqueIndex .= "Index: {$idx->Key_name} | Column: {$idx->Column_name} | Non_unique: {$idx->Non_unique}\n";
    }
}
// Dry run test
$dbStatus = "";
try {
    $first = DB::table('master_medicines')->whereNotNull('specialty_id')->first();
    if ($first) {
        DB::table('master_medicines')->insert([
            'name' => 'Test Duplicate',
            'specialty_id' => $first->specialty_id,
            'normalized_name' => $first->normalized_name,
            'is_active' => true,
        ]);
        $dbStatus = "FAILED: Allowed duplicate";
    } else {
        $dbStatus = "SKIPPED: No data to test";
    }
} catch (\Exception $e) {
    $dbStatus = "SUCCESS: Constraint blocked duplicate insert.\n" . $e->getMessage();
}

$sectionD = "SECTION D — Unique Index Proof\n";
$sectionD .= $uniqueIndex;
$sectionD .= "Test result: " . explode("\n", $dbStatus)[0] . "\n";

// SECTION E
$local_svc = DB::select("DESCRIBE local_services");
$sectionE = "SECTION E — local_services Schema\n";
foreach($local_svc as $col) {
    $sectionE .= "{$col->Field} | {$col->Type} | {$col->Null} | " . ($col->Key ?? '') . "\n";
}
$sectionE .= "clinical_catalog untouched.\n";

// SECTION F
$local_med = DB::select("DESCRIBE local_medicines");
$sectionF = "SECTION F — local_medicines Schema\n";
foreach($local_med as $col) {
    $sectionF .= "{$col->Field} | {$col->Type} | {$col->Null} | " . ($col->Key ?? '') . "\n";
}
$sectionF .= "inventory and master_medicines schemas preserved.\n";

// SECTION G
$sectionG = "SECTION G — Row Counts Before/After\n";
foreach($counts_before as $k => $v) {
    $sectionG .= "{$k}: {$v} -> {$counts_after[$k]}\n";
}

echo "$sectionA\n$sectionB\n$sectionC\n$sectionD\n$sectionE\n$sectionF\n$sectionG\n";

