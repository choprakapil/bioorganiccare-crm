<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\InvoiceItem;
use App\Models\InventoryBatch;
use App\Models\Invoice;

class SystemController extends Controller
{
    public function integrity()
    {
        $doctorId = auth()->id();

        $ledgerDrift = DB::select("
            SELECT id FROM invoices 
            WHERE doctor_id = ? 
            AND ABS((paid_amount + balance_due) - total_amount) > 0.01
        ", [$doctorId]);

        $overpay = DB::select("
            SELECT id FROM invoices 
            WHERE doctor_id = ? 
            AND paid_amount > total_amount
        ", [$doctorId]);

        $negativeBatches = InventoryBatch::where('doctor_id', $doctorId)
            ->where('quantity_remaining','<',0)
            ->count();

        $medicineNull = DB::select("
            SELECT ii.id 
            FROM invoice_items ii
            JOIN invoices i ON i.id = ii.invoice_id
            WHERE i.doctor_id = ?
            AND ii.type = 'Medicine'
            AND ii.inventory_id IS NULL
        ", [$doctorId]);

        $orphanAllocations = DB::select("
            SELECT a.id FROM invoice_item_batch_allocations a
            LEFT JOIN invoice_items ii ON ii.id = a.invoice_item_id
            LEFT JOIN invoices i ON i.id = ii.invoice_id
            WHERE i.doctor_id = ?
            AND ii.id IS NULL
        ", [$doctorId]);

        $inventoryDrift = DB::select("
            SELECT i.id FROM inventory i
            LEFT JOIN inventory_batches b ON b.inventory_id = i.id
            WHERE i.doctor_id = ?
            GROUP BY i.id, i.stock, i.purchase_cost
            HAVING ABS(
                COALESCE(SUM(b.quantity_remaining * b.unit_cost),0) 
                - (i.stock * i.purchase_cost)
            ) > 0.01
        ", [$doctorId]);

        $healthy = 
            count($ledgerDrift) === 0 &&
            count($overpay) === 0 &&
            $negativeBatches === 0 &&
            count($medicineNull) === 0 &&
            count($orphanAllocations) === 0 &&
            count($inventoryDrift) === 0;

        return response()->json([
            'ledger_drift' => count($ledgerDrift),
            'overpay' => count($overpay),
            'negative_batches' => $negativeBatches,
            'medicine_null_link' => count($medicineNull),
            'orphan_allocations' => count($orphanAllocations),
            'inventory_asset_drift' => count($inventoryDrift),
            'status' => $healthy ? 'healthy' : 'warning'
        ]);
    }
}
