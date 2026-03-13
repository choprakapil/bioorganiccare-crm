<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = app(\App\Http\Controllers\Admin\AdminHybridSuggestionController::class);
echo "SERVICES:\n";
echo $controller->services()->getContent() . "\n\n";

echo "MEDICINES:\n";
echo $controller->medicines()->getContent() . "\n\n";

echo "MASTER MEDICINES:\n";
echo $controller->masterMedicines()->getContent() . "\n\n";
