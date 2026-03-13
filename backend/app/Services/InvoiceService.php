<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Treatment;
use App\Models\Inventory;
use App\Models\InventoryBatch;
use App\Models\InvoiceItemBatchAllocation;
use App\Models\Notification;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Enums\InvoiceStatus;

class InvoiceService
{
    /**
     * Create an invoice from a list of treatment IDs.
     * Handles FIFO inventory deduction and allocation tracking.
     */
    public function createFromTreatments(array $data)
    {
        $doctorId = $data['doctor_id'];
        $patientId = $data['patient_id'];
        $treatmentIds = $data['treatment_ids'];
        $dueDate = $data['due_date'] ?? now()->addDays(7);
        $status = $data['status'] ?? InvoiceStatus::UNPAID;
        $paidAmount = $data['paid_amount'] ?? 0;
        $paymentMethod = $data['payment_method'] ?? null;

        return DB::transaction(function () use ($doctorId, $patientId, $treatmentIds, $dueDate, $status, $paidAmount, $paymentMethod, $data) {
            $treatments = Treatment::with([
                    'catalog',
                    'inventory.master_medicine'
                ])
                ->whereIn('id', $treatmentIds)
                ->where('doctor_id', $doctorId)
                ->whereNull('invoice_id')
                ->get();

            if ($treatments->isEmpty()) {
                throw new \Exception('No billable treatments found or already invoiced.');
            }

            $subtotal = $treatments->sum(function ($t) {
                return $t->fee * ($t->quantity ?? 1);
            });

            $discount = $data['discount_amount'] ?? 0;
            $total = max(0, $subtotal - $discount);

            // Adjust paid_amount based on status if not explicitly provided
            if ($status === InvoiceStatus::PAID) {
                $paidAmount = $total;
            }

            $invoice = Invoice::create([
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'total_amount' => $total,
                'discount_amount' => $discount,
                'paid_amount' => $paidAmount,
                'balance_due' => $total - $paidAmount,
                'status' => $status,
                'is_finalized' => false,
                'due_date' => $dueDate,
                'payment_method' => $paymentMethod
            ]);

            // IMMUTABLE LEDGER: Record Invoice Creation
            // When paid-at-creation, group debit+credit under the same transaction UUID
            $txGroupUuid = \Illuminate\Support\Str::uuid()->toString();

            \App\Models\LedgerEntry::record($invoice, 'invoice_created', $total, 'debit', [], null, $txGroupUuid);

            $remaining = $total - $paidAmount;

            if ($paidAmount > 0) {
                // Record Initial Payment (same transaction group for balance validation)
                \App\Models\LedgerEntry::record($invoice, 'payment_applied', $paidAmount, 'credit', ['method' => $paymentMethod], null, $txGroupUuid);
            }

            if ($remaining > 0) {
                \App\Models\LedgerEntry::record($invoice, 'balance_due', $remaining, 'credit', ['method' => 'AccountsReceivable'], null, $txGroupUuid);
            }

            // Validate balance for paired operations
            \App\Services\LedgerIntegrityService::validateTransactionGroup($txGroupUuid);

            foreach ($treatments as $t) {
                if ($t->catalog_id) {
                    $service = \App\Models\ClinicalCatalog::withTrashed()->find($t->catalog_id);
                    if ($service && $service->trashed()) {
                        throw new \Exception("Cannot invoice treatment '{$t->procedure_name}': The linked clinical service is archived and cannot be billed.");
                    }
                }

                $qty = $t->quantity ?? 1;
                $lineTotal = $t->fee * $qty;

                /*
                STRICT TYPE ENFORCEMENT:
                Medicine is determined strictly by inventory_id presence.
                If inventory_id is set but inventory record does not exist,
                abort immediately.
                */
                if ($t->inventory_id !== null) {
                    $exists = \App\Models\Inventory::where('id', $t->inventory_id)
                                ->where('doctor_id', $doctorId)
                                ->exists();

                    if (!$exists) {
                        throw ValidationException::withMessages([
                            'inventory_id' => ["Invalid or missing inventory record for '{$t->procedure_name}'."]
                        ]);
                    }
                }

                $catalogVersionSnapshot = null;
                if ($t->catalog_id) {
                    $catalogVersionSnapshot = $t->catalog->version ?? null;
                }
                if ($t->inventory_id) {
                    $catalogVersionSnapshot = $t->inventory->master_medicine->version ?? null;
                }

                // SAFE INTEGRITY: Prevent duplicate invoice items
                $existingItem = DB::table('invoice_items')
                    ->where('invoice_id', $invoice->id)
                    ->where('name', $t->procedure_name)
                    ->where('teeth', $t->teeth)
                    ->exists();

                if ($existingItem) {
                    continue;
                }

                $invoiceItem = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'inventory_id' => $t->inventory_id,
                    'catalog_version_snapshot' => $catalogVersionSnapshot,
                    'name' => $t->procedure_name,
                    'type' => $t->inventory_id !== null ? 'Medicine' : 'Procedure',
                    'quantity' => $qty,
                    'unit_price' => $t->fee,
                    'unit_cost' => $t->unit_cost,
                    'fee' => $lineTotal,
                    'teeth' => $t->teeth
                ]);

                // FIFO Batch Deduction Logic
                if ($t->inventory_id) {
                    $inventoryItem = Inventory::where('id', $t->inventory_id)
                        ->where('doctor_id', $doctorId)
                        ->lockForUpdate()
                        ->first();
                    
                    if ($inventoryItem) {
                        $oldStock = $inventoryItem->stock;

                        $batchQuery = InventoryBatch::where('inventory_id', $inventoryItem->id)
                            ->where('doctor_id', $doctorId)
                            ->where('quantity_remaining', '>', 0)
                            ->orderBy('created_at', 'asc')
                            ->lockForUpdate()
                            ->get();

                        $remaining = $qty;
                        $totalCost = 0;

                        foreach ($batchQuery as $batch) {
                            if ($remaining <= 0) break;

                            $deductQty = min($batch->quantity_remaining, $remaining);
                            $totalCost += $deductQty * $batch->unit_cost;

                            $batch->quantity_remaining -= $deductQty;
                            $batch->save();

                            // Allocation tracking
                            InvoiceItemBatchAllocation::create([
                                'invoice_item_id' => $invoiceItem->id,
                                'inventory_batch_id' => $batch->id,
                                'quantity_taken' => $deductQty,
                                'unit_cost' => $batch->unit_cost
                            ]);

                            $remaining -= $deductQty;
                        }

                        if ($remaining > 0) {
                            throw ValidationException::withMessages([
                                'inventory' => ["Insufficient batch stock for '{$inventoryItem->item_name}'."]
                            ]);
                        }

                        $effectiveUnitCost = $totalCost / $qty;
                        $invoiceItem->update(['unit_cost' => $effectiveUnitCost]);
                        
                        $inventoryItem->decrement('stock', $qty);
                        
                        if ($inventoryItem->stock <= $inventoryItem->reorder_level) {
                             Notification::create([
                                'user_id' => $doctorId,
                                'type' => 'error',
                                'title' => 'Low Stock Warning',
                                'message' => "Item '{$inventoryItem->item_name}' is below reorder level ({$inventoryItem->stock} left)."
                            ]);
                        }

                        AuditLog::log(
                            'inventory_deducted',
                            "Deducted {$qty} units of '{$inventoryItem->item_name}' for invoice #{$invoice->id} (FIFO Cost: {$effectiveUnitCost})",
                            ['inventory_id' => $inventoryItem->id, 'old' => $oldStock, 'new' => $oldStock - $qty, 'effective_cost' => $effectiveUnitCost]
                        );
                    }
                }

                $t->update(['invoice_id' => $invoice->id]);
            }

            AuditLog::log('invoice_created', "Generated invoice #{$invoice->id} for patient #{$invoice->patient_id}", ['invoice_id' => $invoice->id, 'total' => $total]);

            return $invoice;
        });
    }
}
