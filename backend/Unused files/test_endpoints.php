<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/api/settings/promotion_requires_approval', 'GET')
);
echo "\n--- SETTINGS ---\n";
echo $response->getContent();

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/api/admin/hybrid-suggestions/services', 'GET')
);
echo "\n--- LOCAL SERVICES ---\n";
echo substr($response->getContent(), 0, 500);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/api/admin/clinical-catalog', 'GET')
);
echo "\n--- GLOBAL SERVICES ---\n";
echo substr($response->getContent(), 0, 500);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/api/admin/hybrid-suggestions/medicines', 'GET')
);
echo "\n--- LOCAL MEDICINES ---\n";
echo substr($response->getContent(), 0, 500);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/api/admin/hybrid-suggestions/master-medicines', 'GET')
);
echo "\n--- GLOBAL MEDICINES ---\n";
echo substr($response->getContent(), 0, 500);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/api/admin/hybrid-promotions/pending', 'GET')
);
echo "\n--- PENDING ---\n";
echo substr($response->getContent(), 0, 500);

