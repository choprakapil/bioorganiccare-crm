<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

// Setup auth
$user = \App\Models\User::where('role', 'doctor')->first();
auth()->login($user);
$req = Request::create('/api/finance/summary', 'GET');
$req->setUserResolver(fn() => $user);
app(\App\Support\Context\TenantContext::class)->resolve($req);

// Clear cache so we measure actual queries
Cache::forget("finance_summary_v2_{$user->id}");

$queries = [];
DB::listen(function ($query) use (&$queries) {
    $queries[] = [
        'sql' => $query->sql,
        'time_ms' => $query->time,
    ];
});

$startTime = microtime(true);

$controller = app(\App\Http\Controllers\FinanceController::class);
$response = $controller->summary($req);
$content = $response->getContent();
$status = $response->getStatusCode();

$endTime = microtime(true);
$elapsedMs = round(($endTime - $startTime) * 1000, 2);

DB::getEventDispatcher()->forget('Illuminate\Database\Events\QueryExecuted');

// Save outputs
file_put_contents(__DIR__ . '/finance_baseline_output.json', $content);
file_put_contents(__DIR__ . '/finance_baseline_queries.json', json_encode([
    'status' => $status,
    'total_queries' => count($queries),
    'total_query_time_ms' => round(array_sum(array_column($queries, 'time_ms')), 2),
    'total_response_time_ms' => $elapsedMs,
    'queries' => $queries,
], JSON_PRETTY_PRINT));

echo "Status: {$status}\n";
echo "Queries: " . count($queries) . "\n";
echo "Query time: " . round(array_sum(array_column($queries, 'time_ms')), 2) . "ms\n";
echo "Response time: {$elapsedMs}ms\n";
echo "Response size: " . strlen($content) . " bytes\n\n";

echo "Response JSON:\n{$content}\n\n";

echo "Query log:\n";
foreach ($queries as $i => $q) {
    echo "  Q" . ($i+1) . " [{$q['time_ms']}ms] " . substr($q['sql'], 0, 120) . "\n";
}
