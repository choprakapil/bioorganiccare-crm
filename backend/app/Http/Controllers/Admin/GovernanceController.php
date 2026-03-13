<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Treatment;
use App\Models\InvoiceItem;
use App\Models\InventoryBatch;
use Illuminate\Support\Str;

class GovernanceController extends Controller
{
    public function health()
    {
        $duplicateServices = DB::select("
            SELECT normalized_name, COUNT(*) as count
            FROM clinical_catalog
            GROUP BY normalized_name
            HAVING COUNT(*) > 1
        ");

        $duplicateMedicines = DB::select("
            SELECT normalized_name, COUNT(*) as count
            FROM master_medicines
            GROUP BY normalized_name
            HAVING COUNT(*) > 1
        ");

        $inventoryWithoutBatches = DB::select("
            SELECT i.id
            FROM inventory i
            LEFT JOIN inventory_batches b ON b.inventory_id = i.id
            WHERE b.id IS NULL
        ");

        $negativeInventory = DB::select("
            SELECT id, stock
            FROM inventory
            WHERE stock < 0
        ");

        $orphanTreatments = DB::select("
            SELECT id
            FROM treatments
            WHERE catalog_id IS NULL AND inventory_id IS NULL
        ");

        $invoiceMedicineDrift = DB::select("
            SELECT id
            FROM invoice_items
            WHERE type='Medicine' AND inventory_id IS NULL
        ");

        $ledgerMismatch = DB::select("
            SELECT id
            FROM invoices
            WHERE ABS(total_amount - ledger_debit_total) > 0.01
        ");

        $floatingLedgerEntries = DB::select("
            SELECT id
            FROM ledger_entries
            WHERE invoice_id IS NULL
        ");

        $checks = [
            'duplicate_services' => $duplicateServices,
            'duplicate_medicines' => $duplicateMedicines,
            'orphan_treatments' => $orphanTreatments,
            'inventory_without_batches' => $inventoryWithoutBatches,
            'ledger_mismatch' => $ledgerMismatch,
            'negative_inventory' => $negativeInventory,
            'floating_ledger_entries' => $floatingLedgerEntries,
            'invoice_medicine_drift' => $invoiceMedicineDrift
        ];

        $hasCritical = count($ledgerMismatch) > 0 || count($negativeInventory) > 0 || count($floatingLedgerEntries) > 0 || count($inventoryWithoutBatches) > 0;
        $hasWarning = count($duplicateServices) > 0 || count($duplicateMedicines) > 0 || count($orphanTreatments) > 0 || count($invoiceMedicineDrift) > 0;

        $status = 'healthy';
        if ($hasCritical) {
            $status = 'critical';
        } elseif ($hasWarning) {
            $status = 'warning';
        }

        $issuesFound = array_reduce($checks, function($carry, $item) {
            return $carry + count($item);
        }, 0);

        return response()->json([
            'status' => $status,
            'checks' => $checks,
            'issues_found' => $issuesFound,
            'checks_performed' => 8,
            'repair_actions_available' => 7
        ]);
    }

    public function repairNegativeInventory(Request $request)
    {
        $this->requireConfirm($request);

        $negatives = DB::select("
            SELECT id
            FROM inventory
            WHERE stock < 0
        ");

        $affected = 0;
        foreach ($negatives as $item) {
            $batchStock = DB::table('inventory_batches')
                ->where('inventory_id', $item->id)
                ->sum('quantity_remaining');

            DB::table('inventory')
                ->where('id', $item->id)
                ->update(['stock' => $batchStock ?? 0]);
            $affected++;
        }

        return response()->json(['affected' => $affected]);
    }

    public function repairMedicineDrift(Request $request)
    {
        $this->requireConfirm($request);

        $drifts = DB::select("
            SELECT id, name
            FROM invoice_items
            WHERE type='Medicine' AND inventory_id IS NULL
        ");

        $affected = 0;
        foreach ($drifts as $drift) {
            $inventory = DB::table('inventory')
                ->where('item_name', $drift->name)
                ->first();

            if ($inventory) {
                DB::table('invoice_items')
                    ->where('id', $drift->id)
                    ->update(['inventory_id' => $inventory->id]);
                $affected++;
            }
        }

        return response()->json(['affected' => $affected]);
    }

    public function repairFloatingLedger(Request $request)
    {
        $this->requireConfirm($request);

        $orphans = DB::select("
            SELECT id
            FROM ledger_entries
            WHERE invoice_id IS NULL
        ");

        $affected = 0;
        foreach ($orphans as $orphan) {
            DB::table('ledger_entries')
                ->where('id', $orphan->id)
                ->update(['status' => 'orphaned']);
            $affected++;
        }

        return response()->json(['affected' => $affected]);
    }

    private function requireConfirm(Request $request)
    {
        if ($request->input('confirm') !== 'REPAIR') {
            abort(400, 'Invalid confirmation token. Type REPAIR to confirm.');
        }
    }

    public function repairDuplicateServices(Request $request)
    {
        $this->requireConfirm($request);

        $duplicates = DB::select("
            SELECT normalized_name, COUNT(*) as count
            FROM clinical_catalog
            GROUP BY normalized_name
            HAVING COUNT(*) > 1
        ");

        $affected = 0;
        foreach ($duplicates as $dup) {
            $records = DB::table('clinical_catalog')
                ->where('normalized_name', $dup->normalized_name)
                ->orderBy('id', 'asc')
                ->get();
            
            // keep the first
            $records->shift();

            foreach ($records as $record) {
                DB::table('clinical_catalog')
                    ->where('id', $record->id)
                    ->update(['normalized_name' => $record->normalized_name . '-dup-' . $record->id]);
                $affected++;
            }
        }

        return response()->json(['affected' => $affected]);
    }

    public function repairDuplicateMedicines(Request $request)
    {
        $this->requireConfirm($request);

        $duplicates = DB::select("
            SELECT normalized_name, COUNT(*) as count
            FROM master_medicines
            GROUP BY normalized_name
            HAVING COUNT(*) > 1
        ");

        $affected = 0;
        foreach ($duplicates as $dup) {
            $records = DB::table('master_medicines')
                ->where('normalized_name', $dup->normalized_name)
                ->orderBy('id', 'asc')
                ->get();
            
            // keep the first
            $records->shift();

            foreach ($records as $record) {
                DB::table('master_medicines')
                    ->where('id', $record->id)
                    ->update(['normalized_name' => $record->normalized_name . '-dup-' . $record->id]);
                $affected++;
            }
        }

        return response()->json(['affected' => $affected]);
    }

    public function repairInventoryBatches(Request $request)
    {
        $this->requireConfirm($request);

        // Simple repair logic: recalculate overall inventory stock based on batches
        // Note: Full repair logic may need to be expanded, the prompt requested to rebuild array totals if mismatch occurs.
        
        $mismatches = DB::select("
            SELECT i.id, i.stock as current_stock, SUM(b.quantity_remaining) as batch_stock
            FROM inventory i
            LEFT JOIN inventory_batches b ON b.inventory_id = i.id
            GROUP BY i.id, i.stock
            HAVING current_stock != COALESCE(SUM(b.quantity_remaining), 0)
        ");

        $affected = 0;
        foreach ($mismatches as $mismatch) {
            DB::table('inventory')
                ->where('id', $mismatch->id)
                ->update(['stock' => $mismatch->batch_stock ?? 0]);
            $affected++;
        }

        return response()->json(['affected' => $affected]);
    }

    public function repairOrphanTreatments(Request $request)
    {
        $this->requireConfirm($request);

        $orphans = DB::select("
            SELECT id
            FROM treatments
            WHERE catalog_id IS NULL AND inventory_id IS NULL
        ");

        $affected = 0;
        foreach ($orphans as $orphan) {
            DB::table('treatments')
                ->where('id', $orphan->id)
                ->update(['status' => 'Cancelled']); // Cannot modify invoices directly according to rules
            $affected++;
        }

        return response()->json(['affected' => $affected]);
    }
}
