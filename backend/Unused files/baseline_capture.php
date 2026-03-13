<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Setup auth context
$user = \App\Models\User::where('role', 'doctor')->first();
auth()->login($user);
$req = Request::create('/api/patients', 'GET');
app(\App\Support\Context\TenantContext::class)->resolve($req);

$results = [];

$endpoints = [
    'patients' => ['controller' => \App\Http\Controllers\PatientController::class, 'method' => 'index'],
    'finance_summary' => ['controller' => \App\Http\Controllers\FinanceController::class, 'method' => 'summary'],
    'finance_revenue_trend' => ['controller' => \App\Http\Controllers\FinanceController::class, 'method' => 'revenueTrend'],
    'invoices' => ['controller' => \App\Http\Controllers\InvoiceController::class, 'method' => 'index'],
    'inventory' => ['controller' => \App\Http\Controllers\InventoryController::class, 'method' => 'index'],
    'expenses' => ['controller' => \App\Http\Controllers\ExpenseController::class, 'method' => 'index'],
];

foreach ($endpoints as $name => $config) {
    $queries = [];

    // Start listening
    DB::listen(function ($query) use (&$queries) {
        $queries[] = [
            'sql' => $query->sql,
            'time_ms' => $query->time,
        ];
    });

    $startTime = microtime(true);

    try {
        $controller = app($config['controller']);
        $request = Request::create("/api/{$name}", 'GET');
        $request->setUserResolver(fn() => $user);
        $response = $controller->{$config['method']}($request);
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
    } catch (\Throwable $e) {
        $statusCode = 500;
        $content = json_encode(['error' => $e->getMessage()]);
    }

    $endTime = microtime(true);
    $elapsedMs = round(($endTime - $startTime) * 1000, 2);

    // Stop listening by resetting
    DB::getEventDispatcher()->forget('Illuminate\Database\Events\QueryExecuted');

    $queryCount = count($queries);
    $totalQueryTimeMs = round(array_sum(array_column($queries, 'time_ms')), 2);

    // Save baseline JSON
    $baselinePath = __DIR__ . "/baseline_{$name}.json";
    file_put_contents($baselinePath, $content);

    $results[$name] = [
        'status_code' => $statusCode,
        'response_size_bytes' => strlen($content),
        'query_count' => $queryCount,
        'total_query_time_ms' => $totalQueryTimeMs,
        'total_response_time_ms' => $elapsedMs,
        'queries' => array_map(fn($q) => $q['sql'], $queries),
    ];

    echo ($statusCode === 200 ? "✅" : "❌") . " {$name}: {$queryCount} queries, {$elapsedMs}ms total, {$statusCode} status\n";
}

// Write summary
file_put_contents(__DIR__ . '/baseline_summary.json', json_encode($results, JSON_PRETTY_PRINT));
echo "\n📊 Summary saved to baseline_summary.json\n";
echo "📁 Baseline JSON files saved for: " . implode(', ', array_keys($endpoints)) . "\n";
