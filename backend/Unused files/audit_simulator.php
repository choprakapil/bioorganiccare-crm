<?php
use Illuminate\Http\Request;

// Assume Context/Auth is setup via actingAs in Tinker if needed.
// Actually, Tinker usually runs without session, so we need to log in first.
$user = \App\Models\User::where('role', 'doctor')->first();
auth()->login($user);
$req = Request::create('/api/patients', 'POST');
app(\App\Support\Context\TenantContext::class)->resolve($req);

$catalogId = \App\Models\LocalService::where('item_name', 'like', '%Root Canal Basic%')->first()->id;

$rand = rand(100, 999);
$scenarios = [
    [
        'name' => 'Audit Paid Test',
        'phone' => '1000' . $rand . '001',
        'age' => 30,
        'gender' => 'Other',
        'payment_status' => 'Paid',
        'payment_method' => 'Cash',
        'services' => [['id' => $catalogId, 'item_name' => 'Root Canal Basic', 'is_local' => true, 'fee' => 1500]],
        'amount_paid' => 1500
    ],
    [
        'name' => 'Audit Partial Test',
        'phone' => '2000' . $rand . '002',
        'age' => 30,
        'gender' => 'Other',
        'payment_status' => 'Partial',
        'payment_method' => 'Cash',
        'services' => [['id' => $catalogId, 'item_name' => 'Root Canal Basic', 'is_local' => true, 'fee' => 1500]],
        'amount_paid' => 500
    ],
    [
        'name' => 'Audit Unpaid Test',
        'phone' => '3000' . $rand . '003',
        'age' => 30,
        'gender' => 'Other',
        'payment_status' => 'Unpaid',
        'payment_method' => 'None', // or null
        'services' => [['id' => $catalogId, 'item_name' => 'Root Canal Basic', 'is_local' => true, 'fee' => 1500]],
        'amount_paid' => 0
    ],
];

foreach ($scenarios as $s) {
    $request = Request::create('/api/patients', 'POST', $s);
    app(\App\Http\Controllers\PatientController::class)->store($request, app(\App\Services\InvoiceService::class));
}

// Inventory test
$medicine = \App\Models\Inventory::where('item_name', 'Rct medicine 1')->first();
$patientId = \App\Models\Patient::where('name', 'Audit Paid Test')->first()->id;

if ($medicine) {
    // Phase 4: stock before
    $stockBefore = $medicine->stock;

    // Record treatment dispensing this medicine
    $trtReq = Request::create('/api/treatments', 'POST', [
        'patient_id' => $patientId,
        'inventory_id' => $medicine->id,
        'procedure_name' => $medicine->item_name,
        'fee' => $medicine->sale_price ?? 100,
        'quantity' => 1,
        'status' => \App\Enums\TreatmentStatus::COMPLETED,
        'teeth' => null
    ]);
    app(\App\Http\Controllers\TreatmentController::class)->store($trtReq);

    // capture stock_after_treatment
    $stockAfterTrt = \App\Models\Inventory::find($medicine->id)->stock;

    // Generate Invoice
    $trt = \App\Models\Treatment::where('patient_id', $patientId)
        ->where('inventory_id', $medicine->id)->latest()->first();
        
    if ($trt) {
        $invReq = Request::create('/api/invoices', 'POST', [
            'patient_id' => $patientId,
            'treatment_ids' => [$trt->id],
            'status' => 'Paid',
            'paid_amount' => $medicine->sale_price ?? 100,
            'payment_method' => 'Cash'
        ]);
        app(\App\Http\Controllers\InvoiceController::class)->store($invReq, app(\App\Services\InvoiceService::class));
    }

    // Capture stock_after_invoice
    $stockAfterInv = \App\Models\Inventory::find($medicine->id)->stock;

    echo json_encode([
        'inventory_test' => [
            'item' => $medicine->item_name,
            'stock_before' => $stockBefore,
            'stock_after_treatment' => $stockAfterTrt,
            'stock_after_invoice' => $stockAfterInv
        ]
    ]);
} else {
    echo "Medicine 'Rct medicine 1' not found\n";
}
