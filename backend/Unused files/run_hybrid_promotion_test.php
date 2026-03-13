<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\AdminHybridPromotionController;

// Auth admin
$admin = \App\Models\User::firstOrCreate(
    ['email' => 'admin@admin.com'],
    [
        'name' => 'Admin User',
        'password' => bcrypt('password'),
        'role' => 'super_admin',
        'is_active' => true
    ]
);
\Illuminate\Support\Facades\Auth::login($admin);

$controller = app(AdminHybridPromotionController::class);

echo "1) Promote Hybrid Service 001\n";
$serviceLocal = DB::table('local_services')->where('normalized_name', 'hybrid service 001')->first();
if ($serviceLocal) {
    $res = $controller->promoteService($serviceLocal->id);
    echo "Promoted.\n";
} else {
    echo "Local service not found for promotion.\n";
}

echo "2) Promote Hybrid Test Med 001\n";
$medLocal = DB::table('local_medicines')->where('normalized_name', 'hybrid test med 001')->first();
if ($medLocal) {
    $res = $controller->promoteMedicine($medLocal->id);
    echo "Promoted.\n";
} else {
    echo "Local medicine not found for promotion.\n";
}

echo "\n--- VERIFICATION ---\n";

echo "SERVICE CHECK:\n";
echo "SELECT * FROM clinical_catalog WHERE item_name = 'Hybrid Service 001';\n";
print_r(DB::select("SELECT * FROM clinical_catalog WHERE item_name = 'Hybrid Service 001'"));

echo "\nSELECT * FROM local_services WHERE normalized_name = 'hybrid service 001';\n";
$ls = DB::select("SELECT * FROM local_services WHERE normalized_name = 'hybrid service 001'");
print_r($ls);

echo "\nMEDICINE CHECK:\n";
echo "SELECT * FROM master_medicines WHERE normalized_name = 'hybrid test med 001';\n";
print_r(DB::select("SELECT * FROM master_medicines WHERE normalized_name = 'hybrid test med 001'"));

echo "\nSELECT master_medicine_id FROM inventory WHERE LOWER(item_name) = 'hybrid test med 001';\n";
$inv = DB::select("SELECT master_medicine_id FROM inventory WHERE LOWER(item_name) = 'hybrid test med 001'");
print_r($inv);

echo "\nSELECT * FROM local_medicines WHERE normalized_name = 'hybrid test med 001';\n";
$lm = DB::select("SELECT * FROM local_medicines WHERE normalized_name = 'hybrid test med 001'");
print_r($lm);

