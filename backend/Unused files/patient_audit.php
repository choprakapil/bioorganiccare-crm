<?php

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Patient;

// Setup auth
$user = User::where('role', 'doctor')->first();
auth()->login($user);

$queries = [];
DB::listen(function ($query) use (&$queries) {
    if (strpos($query->sql, 'patients') !== false || strpos($query->sql, 'invoices') !== false) {
        $queries[] = [
            'sql' => $query->sql,
            'time_ms' => $query->time,
        ];
    }
});

echo "=== PHASE 1: SQL CAPTURE ===\n";

// Execute query like in PatientController::index()
$patients = Patient::where('doctor_id', $user->id)
    ->withSum('invoices as invoice_total', 'total_amount')
    ->withSum('invoices as amount_paid', 'paid_amount')
    ->latest()
    ->paginate(20);

echo "\nQUERY COMPLETED. Found {$patients->count()} records on page 1.\n";

echo "\n=== QUERY LOG ===\n";
foreach ($queries as $i => $q) {
    echo "Q" . ($i+1) . " [{$q['time_ms']}ms]:\n{$q['sql']}\n\n";
}
