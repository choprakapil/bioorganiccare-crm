<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Setup auth
$user = \App\Models\User::where('role', 'doctor')->first();
auth()->login($user);
$req = Request::create('/api/test', 'GET');
app(\App\Support\Context\TenantContext::class)->resolve($req);

$doctorId = $user->id;
$patientId = \App\Models\Patient::where('doctor_id', $doctorId)->first()->id;

// Create test treatments (service + medicine)
$rand = rand(1000, 9999);

echo "=== CREATING TEST TREATMENTS ===\n";

// Service treatment
$serviceTreatment = \App\Models\Treatment::create([
    'patient_id' => $patientId,
    'doctor_id' => $doctorId,
    'catalog_id' => \App\Models\ClinicalCatalog::first()?->id,
    'procedure_name' => "Opt Test Service {$rand}",
    'status' => \App\Enums\TreatmentStatus::COMPLETED,
    'fee' => 100,
    'quantity' => 1,
]);

// Medicine treatment
$inventory = \App\Models\Inventory::where('doctor_id', $doctorId)->where('stock', '>', 0)->first();
$medicineTreatment = null;
$treatmentIds = [$serviceTreatment->id];

if ($inventory) {
    $medicineTreatment = \App\Models\Treatment::create([
        'patient_id' => $patientId,
        'doctor_id' => $doctorId,
        'inventory_id' => $inventory->id,
        'procedure_name' => "Opt Test Medicine {$rand}",
        'status' => \App\Enums\TreatmentStatus::COMPLETED,
        'fee' => $inventory->sale_price,
        'quantity' => 1,
        'unit_cost' => $inventory->purchase_cost,
    ]);
    $treatmentIds[] = $medicineTreatment->id;
    echo "✅ Created service treatment #{$serviceTreatment->id}\n";
    echo "✅ Created medicine treatment #{$medicineTreatment->id} (inventory #{$inventory->id}, stock={$inventory->stock})\n";
} else {
    echo "⚠️  No inventory with stock > 0, testing service-only\n";
    echo "✅ Created service treatment #{$serviceTreatment->id}\n";
}

// Capture stock before
$stockBefore = $inventory ? \App\Models\Inventory::find($inventory->id)->stock : null;

// Enable query logging
$queries = [];
DB::listen(function ($query) use (&$queries) {
    $queries[] = [
        'sql' => $query->sql,
        'time_ms' => $query->time,
    ];
});

echo "\n=== INVOICING VIA InvoiceService (WITH EAGER LOADING) ===\n";

$invoiceService = app(\App\Services\InvoiceService::class);

try {
    $invoice = $invoiceService->createFromTreatments([
        'doctor_id' => $doctorId,
        'patient_id' => $patientId,
        'treatment_ids' => $treatmentIds,
        'status' => \App\Enums\InvoiceStatus::PAID,
        'paid_amount' => 0, // auto-calculated for PAID
        'payment_method' => 'Cash',
    ]);

    echo "✅ Invoice created: #{$invoice->id}\n";
    echo "   Total: ₹{$invoice->total_amount}\n";
    echo "   Status: {$invoice->status}\n";
    echo "   Paid: ₹{$invoice->paid_amount}\n";
} catch (\Throwable $e) {
    echo "❌ Invoice creation FAILED: {$e->getMessage()}\n";
    echo "   at {$e->getFile()}:{$e->getLine()}\n";
}

// Stop listening
DB::getEventDispatcher()->forget('Illuminate\Database\Events\QueryExecuted');

// Verify stock after
$stockAfter = $inventory ? \App\Models\Inventory::find($inventory->id)->stock : null;

// Verify ledger
$ledgerEntries = \App\Models\LedgerEntry::where('invoice_id', $invoice->id ?? 0)->get();

// Report
echo "\n=== VERIFICATION RESULTS ===\n";
echo "Query count: " . count($queries) . "\n";
echo "Total query time: " . round(array_sum(array_column($queries, 'time_ms')), 2) . "ms\n";

if ($inventory) {
    echo "\nStock verification:\n";
    echo "  Before: {$stockBefore}\n";
    echo "  After:  {$stockAfter}\n";
    echo "  Deducted: " . ($stockBefore - $stockAfter) . "\n";
    echo "  " . (($stockBefore - $stockAfter) === 1 ? "✅ Correct" : "❌ UNEXPECTED") . "\n";
}

echo "\nLedger verification:\n";
echo "  Entries: " . $ledgerEntries->count() . "\n";
foreach ($ledgerEntries as $le) {
    echo "    [{$le->direction}] {$le->type}: ₹{$le->amount}\n";
}

$debits = $ledgerEntries->where('direction', 'debit')->sum('amount');
$credits = $ledgerEntries->where('direction', 'credit')->sum('amount');
echo "  Debits total: ₹{$debits}\n";
echo "  Credits total: ₹{$credits}\n";
echo "  Balanced: " . (abs($debits - $credits) < 0.01 ? "✅ Yes" : "❌ NO") . "\n";

echo "\nInvoice items:\n";
$items = \App\Models\InvoiceItem::where('invoice_id', $invoice->id ?? 0)->get();
foreach ($items as $item) {
    echo "  [{$item->type}] {$item->name}: ₹{$item->fee} (version snapshot: " . ($item->catalog_version_snapshot ?? 'null') . ")\n";
}

echo "\n=== QUERY LOG ===\n";
foreach ($queries as $i => $q) {
    echo "  Q" . ($i+1) . " [{$q['time_ms']}ms] {$q['sql']}\n";
}

echo "\n=== DONE ===\n";
