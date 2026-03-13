<?php

$out = [];

// Phase 2: Database State Snapshot
$out['counts'] = [
    'patients' => \App\Models\Patient::count(),
    'treatments' => \App\Models\Treatment::count(),
    'invoices' => \App\Models\Invoice::count(),
    'ledger_entries' => \App\Models\LedgerEntry::count(),
    'inventory' => \App\Models\Inventory::count(),
    'inventory_batches' => \App\Models\InventoryBatch::count(),
    'expenses' => \App\Models\Expense::count(),
];

$out['latest'] = [
    'patients' => \App\Models\Patient::latest()->take(5)->get()->toArray(),
    'treatments' => \App\Models\Treatment::latest()->take(5)->get()->toArray(),
    'invoices' => \App\Models\Invoice::latest()->take(5)->get()->toArray(),
    'ledger_entries' => \App\Models\LedgerEntry::latest()->take(10)->get()->toArray(),
];

$out['inventory_status'] = \App\Models\Inventory::get()->map(function($inv) {
    return [
        'id' => $inv->id,
        'item_name' => $inv->item_name,
        'stock' => $inv->stock,
        'doctor_id' => $inv->doctor_id,
        'batches' => \App\Models\InventoryBatch::where('inventory_id', $inv->id)->get()->map(function($b) {
            return [
                'id' => $b->id,
                'quantity_remaining' => $b->quantity_remaining,
            ];
        })->toArray()
    ];
})->toArray();

// Phase 5: Ledger Integrity Check
$txGroups = \App\Models\LedgerEntry::select('transaction_group_uuid')
    ->distinct()
    ->latest()
    ->take(10)
    ->pluck('transaction_group_uuid');

$ledgerIntegrity = [];
foreach ($txGroups as $uuid) {
    $entries = \App\Models\LedgerEntry::where('transaction_group_uuid', $uuid)->get();
    $debits = $entries->where('direction', 'debit')->sum('amount');
    $credits = $entries->where('direction', 'credit')->sum('amount');
    $ledgerIntegrity[] = [
        'uuid' => $uuid,
        'debits' => $debits,
        'credits' => $credits,
        'balanced' => bccomp($debits, $credits, 2) === 0,
        'entry_count' => $entries->count()
    ];
}
$out['ledger_integrity'] = $ledgerIntegrity;

// Phase 6: Finance Metrics Data
$out['finance_metrics'] = [
    'payment_applied_credits' => \App\Models\LedgerEntry::where('type', 'payment_applied')->where('direction', 'credit')->sum('amount'),
    'balance_due_credits' => \App\Models\LedgerEntry::where('type', 'balance_due')->where('direction', 'credit')->sum('amount'),
    'total_expenses' => \App\Models\Expense::sum('amount'),
    'inventory_value' => \App\Models\InventoryBatch::where('quantity_remaining', '>', 0)->sum(\Illuminate\Support\Facades\DB::raw('quantity_remaining * unit_cost'))
];

// Phase 9: Data Consistency Check
$out['consistency'] = [
    'negative_stock' => \App\Models\Inventory::where('stock', '<', 0)->count(),
    'orphan_invoices' => \App\Models\Invoice::whereNull('patient_id')->count(), // assuming patient_id is required
    'orphan_ledger_entries' => \App\Models\LedgerEntry::whereNull('invoice_id')->count(), 
    'treatments_without_patient' => \App\Models\Treatment::whereNull('patient_id')->count(),
    'inventory_batches_without_inventory' => \App\Models\InventoryBatch::doesntHave('inventory')->count(),
];

echo json_encode($out, JSON_PRETTY_PRINT);
