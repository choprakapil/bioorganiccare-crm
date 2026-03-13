<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Treatment;
use App\Models\AuditLog;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Enums\InvoiceStatus;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Support\Context\TenantContext;

class InvoiceController extends Controller
{
    private TenantContext $context;

    public function __construct(TenantContext $context)
    {
        $this->context = $context;
    }

    public function index(Request $request)
    {
        $query = Invoice::where('doctor_id', $this->context->getClinicOwner()->id)
            ->with('patient')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Enterprise Scaling: Cursor Pagination
        return response()->json($query->cursorPaginate(20));
    }

    public function store(Request $request, \App\Services\InvoiceService $invoiceService)
    {
        $validated = $request->validate([
            'patient_id' => [
                'required',
                Rule::exists('patients', 'id')->where(function ($query) {
                    $query->where('doctor_id', $this->context->getClinicOwner()->id);
                }),
            ],
            'treatment_ids' => 'required|array',
            'treatment_ids.*' => [
                Rule::exists('treatments', 'id')->where(function ($query) {
                    $query->where('doctor_id', $this->context->getClinicOwner()->id);
                }),
            ],
            'due_date' => 'nullable|date',
            'discount_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $doctor_id = $this->context->getClinicOwner()->id;
        $patient = Patient::where('doctor_id', $doctor_id)->findOrFail($validated['patient_id']);

        // Tenancy check
        if ($patient->doctor_id !== $doctor_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $invoice = $invoiceService->createFromTreatments([
                'doctor_id' => $doctor_id,
                'patient_id' => $validated['patient_id'],
                'treatment_ids' => $validated['treatment_ids'],
                'due_date' => $validated['due_date'] ?? null,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'paid_amount' => $validated['paid_amount'] ?? 0,
                'payment_method' => $validated['payment_method'] ?? null,
                'status' => $validated['status'] ?? InvoiceStatus::UNPAID,
            ]);

            // Ledger entries (invoice_created debit + optional payment_applied credit)
            // are recorded inside InvoiceService::createFromTreatments() — not here,
            // to ensure exactly one debit per invoice.

            return response()->json([
                'invoice' => $invoice->load('patient'),
                'items' => $invoice->items,
                'ledger' => $invoice->ledgerEntries
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function finalize(Invoice $invoice, \App\Services\CacheInvalidationService $cache)
    {
        if ($invoice->doctor_id !== $this->context->getClinicOwner()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }


        if ($invoice->is_finalized) {
            return response()->json(['message' => 'Invoice is already finalized.'], 422);
        }

        return retry(3, function() use ($invoice, $cache) {
            return DB::transaction(function() use ($invoice, $cache) {
                $locked = Invoice::where('id', $invoice->id)
                    ->where('doctor_id', $this->context->getClinicOwner()->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                if ($locked->is_finalized) return response()->json($locked);

                $locked->update(['is_finalized' => true]);

                AuditLog::log('invoice_finalized', "Finalized invoice #{$locked->id}.", ['invoice_id' => $locked->id]);

                $cache->invalidateFinance($locked->doctor_id);

                return response()->json(['message' => 'Invoice finalized', 'invoice' => $locked]);
            });
        }, 100);
    }

    public function updateStatus(Request $request, Invoice $invoice, \App\Services\CacheInvalidationService $cache)
    {
        if ($invoice->doctor_id !== $this->context->getClinicOwner()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }


        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', InvoiceStatus::all()),
            'payment_method' => 'nullable|string',
        ]);

        return retry(3, function() use ($invoice, $validated, $cache) {
            return DB::transaction(function() use ($invoice, $validated, $cache) {
                $locked = Invoice::where('id', $invoice->id)
                    ->where('doctor_id', $this->context->getClinicOwner()->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $oldStatus = $locked->status;
                $newStatus = $validated['status'];

                if ($oldStatus === $newStatus) return response()->json($locked);

                // 1. Cancellation Logic
                if ($oldStatus !== InvoiceStatus::CANCELLED && $newStatus === InvoiceStatus::CANCELLED) {
                    if (!$locked->stock_reverted) $this->revertStock($locked);
                    
                    $cancellationCredit = (float) $locked->total_amount - (float) $locked->paid_amount;
                    if ($cancellationCredit > 0) {
                        \App\Models\LedgerEntry::record($locked, 'cancellation', $cancellationCredit, 'credit');
                    }
                    $locked->paid_amount = $locked->total_amount;
                    $locked->balance_due = 0;
                }
                // 2. Resurrection Logic
                elseif ($oldStatus === InvoiceStatus::CANCELLED && $newStatus !== InvoiceStatus::CANCELLED) {
                    if ($locked->stock_reverted) $this->deductStock($locked);
                    \App\Models\LedgerEntry::record($locked, 'resurrection', (float) $locked->total_amount, 'debit');
                    $locked->paid_amount = 0;
                    $locked->balance_due = $locked->total_amount;
                }
                // 3. Paid Target Enforcement
                elseif ($newStatus === InvoiceStatus::PAID && (float) $locked->paid_amount < (float) $locked->total_amount) {
                    $remaining = (float) $locked->total_amount - (float) $locked->paid_amount;
                    \App\Models\LedgerEntry::record($locked, 'payment_applied', $remaining, 'credit', ['detail' => 'Automatic adjustment on status Paid']);
                    $locked->paid_amount = $locked->total_amount;
                    $locked->balance_due = 0;
                }

                $locked->status = $newStatus;
                if (isset($validated['payment_method'])) {
                    $locked->payment_method = $validated['payment_method'];
                }

                $locked->save();

                \App\Models\AuditLog::log('invoice_status_updated', "Updated #{$locked->id} from {$oldStatus} to {$newStatus}", ['invoice_id' => $locked->id]);

                $cache->invalidateFinance($locked->doctor_id);

                return response()->json($locked);
            });
        }, 100);
    }

    /**
     * Proportional Payment Logic (Partial / Full Settlement)
     */
    public function applyPayment(Request $request, Invoice $invoice, \App\Services\CacheInvalidationService $cache)
    {
        if ($invoice->doctor_id !== $this->context->getClinicOwner()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }


        $validated = $request->validate([
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string',
            'idempotency_key' => 'required|string|max:100',
        ]);

        $amount = $validated['payment_amount'];

        if ($invoice->status === InvoiceStatus::CANCELLED) {
            return response()->json(['message' => 'Cannot apply payment to a cancelled invoice'], 422);
        }

        if ($amount > $invoice->balance_due) {
            return response()->json(['message' => 'Payment amount exceeds remaining balance'], 422);
        }

        return retry(3, function() use ($invoice, $amount, $validated, $cache) {
            return DB::transaction(function () use ($invoice, $amount, $validated, $cache) {
                $locked = Invoice::where('id', $invoice->id)
                    ->where('doctor_id', $this->context->getClinicOwner()->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // IDEMPOTENCY CHECK
                $existing = \App\Models\LedgerEntry::where('invoice_id', $locked->id)
                    ->where('idempotency_key', $validated['idempotency_key'])
                    ->first();

                if ($existing) {
                    return response()->json($locked);
                }

                $newPaid = $locked->paid_amount + $amount;

                if ($newPaid > ($locked->total_amount + 0.01)) { // Buffer for float
                    return response()->json(['message' => 'Payment exceeds remaining balance'], 422);
                }

                $locked->paid_amount = $newPaid;
                $locked->balance_due = $locked->total_amount - $newPaid;

                // Auto-Negotiate Status based on cash position
                if ($locked->paid_amount == 0) {
                    $locked->status = InvoiceStatus::UNPAID;
                } elseif (abs($locked->paid_amount - $locked->total_amount) < 0.01) {
                    $locked->status = InvoiceStatus::PAID;
                } else {
                    $locked->status = InvoiceStatus::PARTIAL;
                }

                if (isset($validated['payment_method'])) {
                    $locked->payment_method = $validated['payment_method'];
                }

                // Record ledger entry BEFORE save() so reconcileLedger() (fired by
                // the `saved` event) sees consistent debit/credit totals.
                // increment() in record() updates ledger_credit_total on both DB
                // and the in-memory model instance before save() is called.
                \App\Models\LedgerEntry::record($locked, 'payment_applied', $amount, 'credit', ['method' => $validated['payment_method'] ?? null], $validated['idempotency_key']);

                $locked->save();

                AuditLog::log(
                    'payment_applied',
                    "Applied payment of ₹{$amount} to invoice #{$locked->id}. Total paid: ₹{$locked->paid_amount}. New status: {$locked->status}",
                    [
                        'invoice_id' => $locked->id,
                        'amount_added' => $amount,
                        'total_paid' => $locked->paid_amount,
                        'status' => $locked->status
                    ]
                );

                $cache->invalidateFinance($locked->doctor_id);

                return response()->json($locked);
            });
        }, 100);
    }

    public function show(Invoice $invoice)
    {
        if ($invoice->doctor_id !== $this->context->getClinicOwner()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'invoice' => $invoice->load(['patient', 'doctor']),
            'items' => $invoice->items,
            'ledger' => $invoice->ledgerEntries
        ]);
    }

    public function destroy(Invoice $invoice)
    {
        try {
            return response()->json(app(DeleteManager::class)->delete('invoice', $invoice->id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Deduct stock for pharmacy items in an invoice (Resurrection flow).
     */
    private function deductStock(Invoice $invoice)
    {
        $items = $invoice->items()->where('type', 'Medicine')->get();

        foreach ($items as $item) {
            $allocations = \App\Models\InvoiceItemBatchAllocation::where('invoice_item_id', $item->id)
                ->lockForUpdate()
                ->get();

            if ($allocations->isEmpty()) {
                continue;
            }

            // Derived Inventory ID from the first batch allocation
            $firstAlloc = $allocations->first();
            $batchSample = \App\Models\InventoryBatch::where('doctor_id', $invoice->doctor_id)
                ->where('id', $firstAlloc->inventory_batch_id)
                ->first();
            if (!$batchSample) continue;

            $inventoryItem = \App\Models\Inventory::where('id', $batchSample->inventory_id)
                ->where('doctor_id', $invoice->doctor_id)
                ->lockForUpdate()
                ->first();

            if ($inventoryItem) {
                foreach ($allocations as $allocation) {
                    $batch = \App\Models\InventoryBatch::where('id', $allocation->inventory_batch_id)
                        ->lockForUpdate()
                        ->first();

                    // BEGIN ENTERPRISE REALLOCATION PATCH
                    if (!$batch || $batch->quantity_remaining < $allocation->quantity_taken) {
                        $invoice->requires_reallocation = true;
                        $invoice->status = InvoiceStatus::REALLOCATION_REQUIRED;
                        $invoice->reallocation_token = (string) \Illuminate\Support\Str::uuid();
                        $invoice->save();
                        return;
                    }
                    // END ENTERPRISE REALLOCATION PATCH

                    $batch->quantity_remaining -= $allocation->quantity_taken;
                    $batch->save();
                }

                $inventoryItem->decrement('stock', $item->quantity);

                AuditLog::log(
                    'inventory_deducted',
                    "Deducted {$item->quantity} units of '{$inventoryItem->item_name}' for Resurrected Invoice #{$invoice->id}",
                    ['inventory_id' => $inventoryItem->id, 'deducted_qty' => $item->quantity, 'new_stock' => $inventoryItem->stock]
                );
            }
        }

        $invoice->stock_reverted = false;
        $invoice->saveQuietly();
    }

    /**
     * Revert stock for pharmacy items in an invoice.
     * Idempotent protection should be checked before calling.
     */
    private function revertStock(Invoice $invoice)
    {
        $items = $invoice->items()->where('type', 'Medicine')->get();

        foreach ($items as $item) {
            $allocations = \App\Models\InvoiceItemBatchAllocation::where('invoice_item_id', $item->id)
                ->lockForUpdate()
                ->get();

            if ($allocations->isEmpty()) continue;

            $firstAlloc = $allocations->first();
            $batchSample = \App\Models\InventoryBatch::where('doctor_id', $invoice->doctor_id)
                ->where('id', $firstAlloc->inventory_batch_id)
                ->first();
            if (!$batchSample) continue;

            $inventoryItem = \App\Models\Inventory::where('id', $batchSample->inventory_id)
                ->where('doctor_id', $invoice->doctor_id)
                ->lockForUpdate()
                ->first();
            
            if ($inventoryItem) {
                foreach ($allocations as $allocation) {
                    $batch = \App\Models\InventoryBatch::where('id', $allocation->inventory_batch_id)
                        ->lockForUpdate()
                        ->first();

                    if ($batch) {
                        $batch->quantity_remaining += $allocation->quantity_taken;
                        $batch->save();
                    }
                }

                $inventoryItem->increment('stock', $item->quantity);

                AuditLog::log(
                    'inventory_restored',
                    "Restored {$item->quantity} units of '{$inventoryItem->item_name}' from Invoice #{$invoice->id}",
                    ['inventory_id' => $inventoryItem->id, 'restored_qty' => $item->quantity, 'new_stock' => $inventoryItem->stock]
                );
            }
        }

        $invoice->stock_reverted = true;
        $invoice->saveQuietly();
    }

    /**
     * Approve manual stock reallocation for an invoice.
     * Transitions status from 'ReallocationRequired' to 'Paid' upon successful FIFO binding.
     */
    public function approveReallocation(\App\Models\Invoice $invoice)
    {
        // CRITICAL-4 FIX: Tenancy guard was missing — any doctor could approve
        // any invoice's reallocation if they knew the ID.
        if ($invoice->doctor_id !== $this->context->getClinicOwner()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($invoice->status !== InvoiceStatus::REALLOCATION_REQUIRED) {
            return response()->json(["error" => "Invoice not in ReallocationRequired state"], 422);
        }


        return \Illuminate\Support\Facades\DB::transaction(function () use ($invoice) {
            foreach ($invoice->items as $item) {
                if ($item->type !== "Medicine") continue;
                
                $inventoryId = $item->inventory_id;
                
                // Fallback: If inventory_id is missing on the item, try to find it via allocations
                if (!$inventoryId) {
                     $firstAlloc = \App\Models\InvoiceItemBatchAllocation::where("invoice_item_id", $item->id)->first();
                     if ($firstAlloc) {
                         $batch = \App\Models\InventoryBatch::where('doctor_id', $invoice->doctor_id)
                            ->where('id', $firstAlloc->inventory_batch_id)
                            ->first();
                         if ($batch) $inventoryId = $batch->inventory_id;
                     }
                }

                if (!$inventoryId) continue;

                $remaining = $item->quantity;

                $batches = \App\Models\InventoryBatch::where("inventory_id", $inventoryId)
                    ->where("quantity_remaining", ">", 0)
                    ->orderBy("created_at")
                    ->lockForUpdate()
                    ->get();

                foreach ($batches as $batch) {
                    if ($remaining <= 0) break;

                    $take = min($batch->quantity_remaining, $remaining);

                    $batch->quantity_remaining -= $take;
                    $batch->save();

                    \App\Models\InvoiceItemBatchAllocation::create([
                        "invoice_item_id" => $item->id,
                        "inventory_batch_id" => $batch->id,
                        "quantity_taken" => $take,
                        "unit_cost" => $batch->unit_cost
                    ]);

                    $remaining -= $take;
                }

                if ($remaining > 0) {
                     $itemName = $item->name ?? "Item #" . $item->id;
                     throw new \Exception("Insufficient stock for '{$itemName}' during reallocation approval.");
                }

                $inventory = \App\Models\Inventory::where('doctor_id', $this->context->getClinicOwner()->id)->lockForUpdate()->find($inventoryId);
                if ($inventory) {
                    $inventory->decrement("stock", $item->quantity);
                }
            }

            $invoice->requires_reallocation = false;
            $invoice->reallocation_token = null;
            $invoice->status = InvoiceStatus::PAID;
            $invoice->save();

            // IMMUTABLE LEDGER: Reallocation Approval
            \App\Models\LedgerEntry::record($invoice, 'reallocation', $invoice->balance_due, 'credit', ['detail' => 'Stock re-verified']);

            AuditLog::log(
                "reallocation_approved",
                "Reallocation approved for invoice #" . $invoice->id,
                ["invoice_id" => $invoice->id]
            );

            return response()->json(["success" => true]);
        });
    }

    public function generatePdf($id)
    {
        $invoice = Invoice::with(['patient', 'doctor', 'items'])
            ->where('doctor_id', $this->context->getClinicOwner()->id)
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));
        
        return $pdf->stream("invoice_{$invoice->id}.pdf");
    }

    public function showPublic($uuid)
    {
        $invoice = Invoice::where('uuid', $uuid)->firstOrFail();

        return response()->json([
            'invoice' => $invoice->load(['patient', 'doctor']),
            'items' => $invoice->items,
        ]);
    }

    public function generatePublicPdf($uuid)
    {
        $invoice = Invoice::with(['patient', 'doctor', 'items'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));
        
        return $pdf->stream("invoice_{$invoice->id}.pdf");
    }
}
