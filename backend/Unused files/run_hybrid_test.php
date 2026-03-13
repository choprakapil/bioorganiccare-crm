<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\InventoryController;

// Create dummy doctor if not exists
$specId = \App\Models\Specialty::firstOrCreate(['name' => 'General'])->id;
$doctor = \App\Models\User::firstOrCreate(
    ['email' => 'testdoc@example.com'],
    [
        'name' => 'Test Doc',
        'password' => bcrypt('password'),
        'role' => 'doctor',
        'specialty_id' => $specId,
        'clinic_name' => 'Test Clinic',
        'phone' => '1234567890',
        'is_active' => true
    ]
);

class MockTenantContext extends \App\Support\Context\TenantContext {
    public ?\App\Models\User $doc;
    public function __construct(?\App\Models\User $doc) { $this->doc = $doc; }
    public function getClinicOwner(): ?\App\Models\User { return $this->doc; }
    public function getSpecialtyId(): ?int { return $this->doc->specialty_id; }
}

\Illuminate\Support\Facades\Auth::login($doctor);

$request = Request::create('http://localhost/api/inventory', 'POST', [
    'item_name' => 'Hybrid Test Med 001',
    'stock' => 10,
    'reorder_level' => 2,
    'purchase_cost' => 50,
    'sale_price' => 70,
]);
$request->setUserResolver(fn() => $doctor);

app()->singleton(\App\Support\Context\TenantContext::class, function () use ($doctor) {
    return new MockTenantContext($doctor);
});

// Run InventoryController
$controller = app(InventoryController::class);
$controller->store($request);

// Collect Results
$resMaster = DB::select("SELECT * FROM master_medicines WHERE normalized_name = 'hybrid test med 001'");
$resLocal = DB::select("SELECT * FROM local_medicines WHERE normalized_name = 'hybrid test med 001'");
$resInv = DB::select("SELECT item_name, master_medicine_id FROM inventory WHERE item_name = 'Hybrid Test Med 001'");

echo "1. Modified InventoryController code\n";
echo "(File modified via replace_file_content earlier)\n\n";

echo "2. Created test medicine: 'Hybrid Test Med 001'\n\n";

echo "3. Verify in master_medicines (should be 0 rows):\n";
print_r($resMaster);

echo "\n4. Verify in local_medicines (should be 1 row):\n";
print_r($resLocal);

echo "\n5. Verify in inventory (master_medicine_id should be NULL):\n";
print_r($resInv);

