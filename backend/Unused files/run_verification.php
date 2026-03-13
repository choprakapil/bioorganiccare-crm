<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "-----------------------------------------\n";
echo "CHECK 1 — FK Validation\n";
echo "-----------------------------------------\n";
$fk = DB::select("SELECT DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = 'inventory_master_medicine_id_foreign'");
print_r($fk);

echo "\n-----------------------------------------\n";
echo "CHECK 2 — UNIQUE INDEX VALIDATION\n";
echo "-----------------------------------------\n";
$indexes = DB::select("SHOW INDEX FROM master_medicines");
print_r($indexes);

echo "\n-----------------------------------------\n";
echo "CHECK 3 — DUPLICATE CHECK\n";
echo "-----------------------------------------\n";
$duplicates = DB::select("SELECT specialty_id, normalized_name, COUNT(*) c FROM master_medicines GROUP BY specialty_id, normalized_name HAVING c > 1");
print_r($duplicates);

echo "\n-----------------------------------------\n";
echo "CHECK 4 — Orphan Risk Check\n";
echo "-----------------------------------------\n";
$orphans = DB::select("SELECT COUNT(*) as count FROM inventory WHERE master_medicine_id IS NOT NULL AND master_medicine_id NOT IN (SELECT id FROM master_medicines)");
print_r($orphans);

echo "\n-----------------------------------------\n";
echo "CHECK 5 — Table Existence\n";
echo "-----------------------------------------\n";
$local_services = DB::select("SHOW TABLES LIKE 'local_services'");
print_r($local_services);

$local_medicines = DB::select("SHOW TABLES LIKE 'local_medicines'");
print_r($local_medicines);
