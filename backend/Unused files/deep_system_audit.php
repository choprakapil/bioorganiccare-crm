<?php

echo "\n=============================\n";
echo "DOCTOR CRM FULL SYSTEM AUDIT\n";
echo "=============================\n";

/*
|--------------------------------------------------------------------------
| SECTION 1 — ENVIRONMENT & REDIS CHECK
|--------------------------------------------------------------------------
*/

echo "\n[1] REDIS & CACHE CONFIG\n";

echo "APP_ENV: " . config('app.env') . "\n";
echo "CACHE_DRIVER: " . config('cache.default') . "\n";
echo "SESSION_DRIVER: " . config('session.driver') . "\n";
echo "QUEUE_CONNECTION: " . config('queue.default') . "\n";

echo "Redis extension loaded? ";
echo extension_loaded('redis') ? "YES\n" : "NO\n";

echo "Predis installed? ";
echo class_exists(\Predis\Client::class) ? "YES\n" : "NO\n";

/*
|--------------------------------------------------------------------------
| SECTION 2 — IMPERSONATION DEPENDENCIES
|--------------------------------------------------------------------------
*/

echo "\n[2] IMPERSONATION ROUTES & MIDDLEWARE\n";

$routes = collect(\Route::getRoutes())->filter(function($route) {
    return str_contains($route->uri(), 'impersonate');
});

foreach ($routes as $r) {
    print_r([
        'uri' => $r->uri(),
        'middleware' => $r->middleware(),
        'action' => $r->getActionName()
    ]);
}

/*
|--------------------------------------------------------------------------
| SECTION 3 — SUBSCRIPTION MODULE CHECK
|--------------------------------------------------------------------------
*/

echo "\n[3] SUBSCRIPTION TABLE CHECK\n";

$tables = DB::select("SHOW TABLES");
foreach ($tables as $table) {
    print_r($table);
}

echo "\nCheck subscription-related tables:\n";

$subTables = ['subscriptions','plans','tenant_subscriptions','billing_logs'];
foreach ($subTables as $t) {
    try {
        $count = DB::table($t)->count();
        echo "$t exists. Count: $count\n";
    } catch (\Exception $e) {
        echo "$t does NOT exist.\n";
    }
}

/*
|--------------------------------------------------------------------------
| SECTION 4 — SPECIALTY CONTEXT
|--------------------------------------------------------------------------
*/

echo "\n[4] SPECIALTIES\n";
$specs = DB::table('specialties')->get();
foreach ($specs as $s) {
    print_r($s);
}

/*
|--------------------------------------------------------------------------
| SECTION 5 — CLINICAL CATALOG STRUCTURE
|--------------------------------------------------------------------------
*/

echo "\n[5] CLINICAL SERVICE CATEGORIES\n";
$cats = DB::table('clinical_service_categories')->get();
foreach ($cats as $c) {
    print_r($c);
}

echo "\n[6] CLINICAL CATALOG ITEMS\n";
$items = DB::table('clinical_catalog')->get();
foreach ($items as $i) {
    print_r($i);
}

echo "\nOrphan Catalog Items (No Category)\n";
$orphans = DB::table('clinical_catalog as cc')
    ->leftJoin('clinical_service_categories as c','cc.category_id','=','c.id')
    ->whereNull('c.id')
    ->select('cc.id','cc.specialty_id','cc.category_id')
    ->get();
foreach ($orphans as $o) {
    print_r($o);
}

/*
|--------------------------------------------------------------------------
| SECTION 6 — PHARMACY STRUCTURE
|--------------------------------------------------------------------------
*/

echo "\n[7] PHARMACY CATEGORIES\n";
try {
    $phCats = DB::table('pharmacy_categories')->get();
    foreach ($phCats as $p) {
        print_r($p);
    }
} catch (\Exception $e) {
    echo "pharmacy_categories table missing\n";
}

echo "\n[8] MASTER MEDICINES\n";
try {
    $meds = DB::table('master_medicines')->get();
    foreach ($meds as $m) {
        print_r($m);
    }
} catch (\Exception $e) {
    echo "master_medicines table missing\n";
}

/*
|--------------------------------------------------------------------------
| SECTION 7 — USERS & SPECIALTY LINKAGE
|--------------------------------------------------------------------------
*/

echo "\n[9] USERS\n";
$users = DB::table('users')->get();
foreach ($users as $u) {
    print_r([
        'id' => $u->id,
        'role' => $u->role ?? null,
        'specialty_id' => $u->specialty_id ?? null
    ]);
}

/*
|--------------------------------------------------------------------------
| SECTION 8 — FOREIGN KEY CHECK
|--------------------------------------------------------------------------
*/

echo "\n[10] FOREIGN KEY CHECK (clinical_catalog)\n";
$schema = DB::select("SHOW CREATE TABLE clinical_catalog");
print_r($schema);

echo "\n=============================\n";
echo "END OF SYSTEM AUDIT\n";
echo "=============================\n";
