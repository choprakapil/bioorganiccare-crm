<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminHybridSuggestionController;

$controller = app(AdminHybridSuggestionController::class);

echo "--- SERVICES ---\n";
echo $controller->services()->getContent() . "\n\n";

echo "--- MEDICINES ---\n";
echo $controller->medicines()->getContent() . "\n";
