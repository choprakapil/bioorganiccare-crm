<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\AuditLog;
use App\Models\ClinicalCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\InvoiceStatus;
use App\Support\Context\TenantContext;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    private TenantContext $context;

    public function __construct(TenantContext $context)
    {
        $this->context = $context;
    }

    public function analytics(Request $request)
    {
        $doctorId = $this->context->getClinicOwner()->id;
        $mode = $request->query('mode', 'accrued'); // realized | accrued

        $startOfMonth = now()->startOfMonth();
        $startOfPrevMonth = now()->subMonth()->startOfMonth();
        $endOfPrevMonth = now()->subMonth()->endOfMonth();

        // Proportional Calculation Logic for Realized Mode
        $isRealized = ($mode === 'realized');
        
        // Helper for consistent sales metrics across months/modes
        $getMetrics = function ($start, $end = null) use ($doctorId, $isRealized) {
            $query = \App\Models\InvoiceItem::join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->where('invoices.doctor_id', $doctorId)
                ->where('invoice_items.type', 'Medicine')
                ->where('invoices.created_at', '>=', $start);

            if ($end) {
                $query->where('invoices.created_at', '<=', $end);
            }

            if ($isRealized) {
                $query->whereIn('invoices.status', [InvoiceStatus::PAID, InvoiceStatus::PARTIAL]);
                $select = '
                    SUM(invoice_items.fee * (invoices.paid_amount / NULLIF(invoices.total_amount, 0))) as revenue,
                    SUM((invoice_items.unit_cost * invoice_items.quantity) * (invoices.paid_amount / NULLIF(invoices.total_amount, 0))) as cogs
                ';
            } else {
                $query->where('invoices.status', '!=', InvoiceStatus::CANCELLED);
                $select = '
                    SUM(invoice_items.fee) as revenue,
                    SUM(invoice_items.unit_cost * invoice_items.quantity) as cogs
                ';
            }

            return $query->select(\Illuminate\Support\Facades\DB::raw($select))->first();
        };

        // 1. Current Month Data
        $currentMetrics = $getMetrics($startOfMonth);
        $revenue = (float)($currentMetrics->revenue ?? 0);
        $cogs = (float)($currentMetrics->cogs ?? 0);
        $profit = $revenue - $cogs;

        // 2. Previous Month Data (for Trends)
        $prevMetrics = $getMetrics($startOfPrevMonth, $endOfPrevMonth);
        $prevRevenue = (float)($prevMetrics->revenue ?? 0);
        $prevCogs = (float)($prevMetrics->cogs ?? 0);
        $prevProfit = $prevRevenue - $prevCogs;

        // 3. Trend Calculations
        $calcDelta = function ($current, $previous) {
            if ($previous == 0) return $current > 0 ? 'New' : 0;
            return round((($current - $previous) / $previous) * 100, 1);
        };

        $revenueDelta = $calcDelta($revenue, $prevRevenue);
        $profitDelta = $calcDelta($profit, $prevProfit);

        // 4. Floor Value (Current Stock Assessment) - Always global
        $floorValue = \App\Models\InventoryBatch::where('doctor_id', $doctorId)
            ->select(\Illuminate\Support\Facades\DB::raw('SUM(quantity_remaining * unit_cost) as total'))
            ->value('total') ?? 0;

        // 5. Low Stock Count
        $lowStockCount = \App\Models\Inventory::where('doctor_id', $doctorId)
            ->whereRaw('stock <= reorder_level')
            ->count();

        // 6. Top Selling Medicines (Quantity based - using proportional logic for realized)
        $topSellingQuery = \App\Models\InvoiceItem::join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.doctor_id', $doctorId)
            ->where('invoice_items.type', 'Medicine');

        if ($isRealized) {
            $topSellingQuery->whereIn('invoices.status', [InvoiceStatus::PAID, InvoiceStatus::PARTIAL])
                ->select('invoice_items.name', \Illuminate\Support\Facades\DB::raw('SUM(invoice_items.quantity * (invoices.paid_amount / NULLIF(invoices.total_amount, 0))) as total_quantity'));
        } else {
            $topSellingQuery->where('invoices.status', '!=', InvoiceStatus::CANCELLED)
                ->select('invoice_items.name', \Illuminate\Support\Facades\DB::raw('SUM(invoice_items.quantity) as total_quantity'));
        }

        $topSelling = $topSellingQuery->groupBy('invoice_items.name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        // 7. Sparkline Data (Last 6 Months Revenue)
        $sixMonthsAgo = now()->subMonths(5)->startOfMonth();
        $sparklineQuery = \App\Models\InvoiceItem::join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.doctor_id', $doctorId)
            ->where('invoice_items.type', 'Medicine')
            ->where('invoices.created_at', '>=', $sixMonthsAgo);

        if ($isRealized) {
            $sparklineQuery->whereIn('invoices.status', [InvoiceStatus::PAID, InvoiceStatus::PARTIAL])
                ->select(
                    \Illuminate\Support\Facades\DB::raw('DATE_FORMAT(invoices.created_at, "%Y-%m") as month'),
                    \Illuminate\Support\Facades\DB::raw('SUM(invoice_items.fee * (invoices.paid_amount / NULLIF(invoices.total_amount, 0))) as total')
                );
        } else {
            $sparklineQuery->where('invoices.status', '!=', InvoiceStatus::CANCELLED)
                ->select(
                    \Illuminate\Support\Facades\DB::raw('DATE_FORMAT(invoices.created_at, "%Y-%m") as month'),
                    \Illuminate\Support\Facades\DB::raw('SUM(invoice_items.fee) as total')
                );
        }

        $monthlyData = $sparklineQuery->groupBy('month')
            ->orderBy('month', 'asc')
            ->pluck('total', 'month')
            ->toArray();

        // Fill gaps with zero
        $sparkline = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthKey = now()->subMonths($i)->format('Y-m');
            $sparkline[] = (float)($monthlyData[$monthKey] ?? 0);
        }

        return response()->json([
            'mode' => $mode,
            'floor_value' => round($floorValue, 2),
            'monthly_revenue' => round($revenue, 2),
            'monthly_cogs' => round($cogs, 2),
            'gross_profit' => round($profit, 2),
            'margin_percent' => $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0,
            'low_stock_count' => $lowStockCount,
            'top_selling' => $topSelling,
            'sparkline' => $sparkline,
            'previous_month' => [
                'revenue' => round($prevRevenue, 2),
                'cogs' => round($prevCogs, 2),
                'profit' => round($prevProfit, 2),
            ],
            'trend' => [
                'revenue_delta' => $revenueDelta,
                'profit_delta' => $profitDelta
            ]
        ]);
    }

    public function index(Request $request)
    {
        $query = Inventory::where('doctor_id', $this->context->getClinicOwner()->id)
            ->with(['master_medicine']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $paginated = $query->orderBy('item_name')->paginate(20);
        
        $paginated->getCollection()->transform(function ($item) {
            $item->is_archived = false;
            $item->is_usable = true;
            
            if ($item->master_medicine) {
                $item->is_archived = $item->master_medicine->trashed();
                $item->is_usable = !$item->master_medicine->trashed() && $item->master_medicine->is_active;
            }
            
            return $item;
        });

        return response()->json($paginated);
    }

    public function store(Request $request)
    {
        $specialtyId = $this->context->getSpecialtyId();

        $validated = $request->validate([
            'master_medicine_id' => [
                'nullable',
                Rule::exists('master_medicines', 'id')->where(function ($query) use ($specialtyId) {
                    $query->where('specialty_id', $specialtyId);
                }),
            ],
            'item_name' => 'nullable|string|max:255', // Added for direct creation
            'catalog_id' => [
                'nullable',
                Rule::exists('clinical_catalog', 'id')->where(function ($query) use ($specialtyId) {
                    $query->where('specialty_id', $specialtyId);
                }),
            ],
            'stock' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'purchase_cost' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
        ]);

        // Validate sale_price >= purchase_cost
        // Validate sale_price >= purchase_cost
        if ($validated['sale_price'] < $validated['purchase_cost']) {
            return response()->json([
                'message' => 'Sale price cannot be less than purchase cost',
                'errors' => ['sale_price' => ['Sale price must be greater than or equal to purchase cost']]
            ], 422);
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $validated, $specialtyId) {
            $itemData = [
                'stock' => $validated['stock'],
                'reorder_level' => $validated['reorder_level'],
                'purchase_cost' => $validated['purchase_cost'],
                'sale_price' => $validated['sale_price'],
                'sku' => $request->sku
            ];

            $ownerId = $this->context->getClinicOwner()->id;

            // GOVERNED FLOW: Master Medicine (Existing ID)
            if (!empty($validated['master_medicine_id'])) {
                $master = \App\Models\MasterMedicine::where('specialty_id', $specialtyId)
                    ->findOrFail($validated['master_medicine_id']);
                
                $inventory = Inventory::updateOrCreate(
                    [
                        'doctor_id' => $ownerId,
                        'master_medicine_id' => $validated['master_medicine_id']
                    ],
                    array_merge($itemData, ['item_name' => $master->name])
                );

                // Create initial batch for FIFO tracking
                if ($validated['stock'] > 0) {
                    \App\Models\InventoryBatch::create([
                        'inventory_id'       => $inventory->id,
                        'doctor_id'          => $ownerId,
                        'original_quantity'  => $validated['stock'],
                        'quantity_remaining' => $validated['stock'],
                        'unit_cost'          => $validated['purchase_cost'],
                        'batch_type'         => 'initial',
                        'purchase_reference' => \Illuminate\Support\Str::uuid(),
                    ]);
                }

                return response()->json($inventory, 201);
            }
            // NEW DIRECT FLOW: Create Master on the fly using Item Name
            elseif (!empty($validated['item_name'])) {
                // STEP A - Normalize item_name (Advanced)
                // Convert to lowercase, remove extra spaces, normalize multi-spaces
                $normalized = preg_replace('/\s+/', ' ', trim(strtolower($validated['item_name'])));

                // STEP B - Prevent Case-Insensitive Duplicates (Exact Match)
                $master = \App\Models\MasterMedicine::whereRaw('LOWER(name) = ?', [$normalized])
                    ->where('specialty_id', $this->context->getSpecialtyId())
                    ->first();

                if (!$master) {
                    // TASK 2: Soft Duplicate Warning (NOT Hard Block)
                    // Only trigger if similar results exist AND no exact match AND not forced
                    if (!$request->boolean('force_create')) {
                        $similar = \App\Models\MasterMedicine::whereRaw('LOWER(name) LIKE ?', ["%{$normalized}%"])
                            ->where('specialty_id', $this->context->getSpecialtyId())
                            ->where('is_active', true)
                            ->limit(5)
                            ->get(['id', 'name']);

                        if ($similar->count() > 0) {
                            return response()->json([
                                'message' => 'Similar medicines already exist.',
                                'warning' => 'Similar medicines already exist.',
                                'similar' => $similar
                            ], 409);
                        }
                    }

                    \Illuminate\Support\Facades\DB::table('local_medicines')->insert([
                        'doctor_id' => $ownerId,
                        'specialty_id' => $this->context->getSpecialtyId(),
                        'item_name' => ucfirst($normalized),
                        'normalized_name' => $normalized,
                        'buy_price' => $validated['purchase_cost'],
                        'sell_price' => $validated['sale_price'],
                        'is_promoted' => false,
                        'promoted_master_id' => null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $inventory = Inventory::updateOrCreate(
                        [
                            'doctor_id' => $ownerId,
                            'master_medicine_id' => null,
                            'item_name' => ucfirst($normalized)
                        ],
                        $itemData
                    );

                    // Create initial batch for FIFO tracking
                    if ($validated['stock'] > 0) {
                        \App\Models\InventoryBatch::create([
                            'inventory_id'       => $inventory->id,
                            'doctor_id'          => $ownerId,
                            'original_quantity'  => $validated['stock'],
                            'quantity_remaining' => $validated['stock'],
                            'unit_cost'          => $validated['purchase_cost'],
                            'batch_type'         => 'initial',
                            'purchase_reference' => \Illuminate\Support\Str::uuid(),
                        ]);
                    }

                    return response()->json($inventory, 201);
                }

                // STEP C - Update Inventory (Keep Intact)
                $inventory = Inventory::updateOrCreate(
                    [
                        'doctor_id' => $ownerId,
                        'master_medicine_id' => $master->id
                    ],
                    array_merge($itemData, ['item_name' => $master->name])
                );

                // Create initial batch for FIFO tracking
                if ($validated['stock'] > 0) {
                    \App\Models\InventoryBatch::create([
                        'inventory_id'       => $inventory->id,
                        'doctor_id'          => $ownerId,
                        'original_quantity'  => $validated['stock'],
                        'quantity_remaining' => $validated['stock'],
                        'unit_cost'          => $validated['purchase_cost'],
                        'batch_type'         => 'initial',
                        'purchase_reference' => \Illuminate\Support\Str::uuid(),
                    ]);
                }

                return response()->json($inventory, 201);
            }
            
            // LEGACY FLOW: Clinical Catalog (Should be phased out for medicines)
            elseif (!empty($validated['catalog_id'])) {
                $catalog = ClinicalCatalog::where('specialty_id', $specialtyId)
                    ->findOrFail($validated['catalog_id']);
                
                 // Check if already added
                 $exists = Inventory::where('doctor_id', $ownerId)
                    ->where('catalog_id', $validated['catalog_id'])
                    ->first();

                 if ($exists) {
                     $exists->update($itemData);
                     return response()->json($exists, 201);
                 }

                 $inventory = Inventory::create(array_merge($itemData, [
                     'doctor_id' => $ownerId,
                     'catalog_id' => $validated['catalog_id'],
                     'item_name' => $catalog->item_name
                 ]));

                 // Create initial batch for FIFO tracking
                 if ($validated['stock'] > 0) {
                     \App\Models\InventoryBatch::create([
                         'inventory_id'       => $inventory->id,
                         'doctor_id'          => $ownerId,
                         'original_quantity'  => $validated['stock'],
                         'quantity_remaining' => $validated['stock'],
                         'unit_cost'          => $validated['purchase_cost'],
                         'batch_type'         => 'initial',
                         'purchase_reference' => \Illuminate\Support\Str::uuid(),
                     ]);
                 }

                 return response()->json($inventory, 201);
            } 
            
            else {
                return response()->json(['message' => 'Medicine must be selected from the Master Catalog or have a valid name.'], 422);
            }
        });
    }

    public function update(Request $request, Inventory $inventory)
    {
        if ($inventory->doctor_id !== $this->context->getClinicOwner()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'item_name'    => 'sometimes|string',
            'sku'          => 'sometimes|nullable|string',
            // NOTE: 'stock' is intentionally EXCLUDED — stock can only be changed
            // via POST /inventory/{id}/replenish  or  POST /inventory/{id}/adjust
            // Any attempt to pass 'stock' in the request body is silently ignored.
            'reorder_level' => 'sometimes|integer|min:0',
            'purchase_cost' => 'sometimes|numeric|min:0',
            'sale_price'    => 'sometimes|numeric|min:0',
        ]);

        // Validate sale_price >= purchase_cost
        $salePrice    = $validated['sale_price']    ?? $inventory->sale_price;
        $purchaseCost = $validated['purchase_cost'] ?? $inventory->purchase_cost;

        if ($salePrice < $purchaseCost) {
            return response()->json([
                'message' => 'Sale price cannot be less than purchase cost',
                'errors'  => ['sale_price' => ['Sale price must be >= purchase cost']]
            ], 422);
        }

        $inventory->update($validated);

        AuditLog::log(
            'inventory_item_updated',
            "Updated details for '{$inventory->item_name}'",
            ['inventory_id' => $inventory->id]
        );

        return response()->json($inventory);
    }

    public function destroy(Inventory $inventory)
    {
        try {
            return response()->json(app(DeleteManager::class)->delete('inventory', $inventory->id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /inventory/{inventory}/adjust
     *
     * The ONLY legitimate path for manual stock correction.
     * Creates an InventoryBatch record for full audit trail.
     * Prevents stock going negative.
     */
    public function adjust(Request $request, Inventory $inventory)
    {
        if ($inventory->doctor_id !== $this->context->getClinicOwner()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'adjustment_quantity' => 'required|integer|not_in:0',
            'reason'              => 'required|string|max:500',
        ]);

        $qty    = (int) $validated['adjustment_quantity'];
        $reason = $validated['reason'];

        return \Illuminate\Support\Facades\DB::transaction(function () use ($inventory, $qty, $reason) {
            // Lock the row — prevents concurrent adjustments racing
            $inventory = Inventory::where('id', $inventory->id)
                ->where('doctor_id', $this->context->getClinicOwner()->id)
                ->lockForUpdate()
                ->firstOrFail();

            $currentStock = $inventory->stock;

            // Guard: negative adjustment cannot exceed available stock
            if ($qty < 0 && abs($qty) > $currentStock) {
                return response()->json([
                    'message' => "Cannot remove " . abs($qty) . " units — only {$currentStock} in stock.",
                    'errors'  => ['adjustment_quantity' => ['Adjustment would make stock negative']]
                ], 422);
            }

            $newStock = $currentStock + $qty;

            // Create Batch record for full audit trail
            $batchQty = abs($qty);
            \App\Models\InventoryBatch::create([
                'inventory_id'       => $inventory->id,
                'doctor_id'          => $inventory->doctor_id,
                'original_quantity'  => $batchQty,
                'quantity_remaining' => $qty > 0 ? $batchQty : 0, // Negative adjustments are consumed
                'unit_cost'          => $inventory->purchase_cost,
                'batch_type'         => 'adjustment',
                'adjustment_reason'  => $reason,
                'purchase_reference' => \Illuminate\Support\Str::uuid(),
            ]);

            // Apply the stock change atomically
            if ($qty > 0) {
                $inventory->increment('stock', $qty);
            } else {
                $inventory->decrement('stock', abs($qty));
            }

            AuditLog::log(
                'inventory_stock_adjusted',
                "Manual adjustment for '{$inventory->item_name}': {$currentStock} → {$newStock} (Δ{$qty}). Reason: {$reason}",
                [
                    'inventory_id'  => $inventory->id,
                    'old_stock'     => $currentStock,
                    'new_stock'     => $newStock,
                    'delta'         => $qty,
                    'reason'        => $reason,
                ]
            );

            return response()->json([
                'message'   => 'Stock adjusted successfully',
                'old_stock' => $currentStock,
                'new_stock' => $newStock,
                'delta'     => $qty,
                'inventory' => $inventory->fresh(),
            ]);
        });
    }

    public function replenish(Request $request, Inventory $inventory)
    {
        if ($inventory->doctor_id !== $this->context->getClinicOwner()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'purchase_cost' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'purchase_reference' => 'required|uuid'
        ]);

        // Validate sale_price >= purchase_cost
        $salePrice = $validated['sale_price'] ?? $inventory->sale_price;
        $purchaseCost = $validated['purchase_cost'];
        
        if ($salePrice < $purchaseCost) {
            return response()->json([
                'message' => 'Sale price cannot be less than purchase cost',
                'errors' => ['sale_price' => ['Sale price must be greater than or equal to purchase cost']]
            ], 422);
        }

        $oldStock = $inventory->stock;

        $isIdempotencyHit = \Illuminate\Support\Facades\DB::transaction(function () use (&$inventory, $validated) {
            $inventory = \App\Models\Inventory::where('id', $inventory->id)
                ->where('doctor_id', $this->context->getClinicOwner()->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Idempotency Guard: Stop if this purchase was already processed
            $existingBatch = \App\Models\InventoryBatch::where('doctor_id', $inventory->doctor_id)
                ->where('purchase_reference', $validated['purchase_reference'])
                ->first();
            if ($existingBatch) {
                return true; // Mark as hit
            }

            $inventory->increment('stock', $validated['quantity']);

            // FIFO Phase 1: Create new batch record
            \App\Models\InventoryBatch::create([
                'inventory_id' => $inventory->id,
                'doctor_id' => $inventory->doctor_id,
                'original_quantity' => $validated['quantity'],
                'quantity_remaining' => $validated['quantity'],
                'unit_cost' => $validated['purchase_cost'],
                'purchase_reference' => $validated['purchase_reference']
            ]);

            // Mandatory Pricing Update & Automated Expense
            $inventory->purchase_cost = $validated['purchase_cost'];
            
            $totalCost = $validated['quantity'] * $validated['purchase_cost'];
            if ($totalCost > 0) {
                \App\Models\Expense::create([
                    'doctor_id' => $inventory->doctor_id,
                    'category' => 'Medical Supplies',
                    'amount' => $totalCost,
                    'expense_date' => now(),
                    'description' => "Inventory Purchase: {$inventory->item_name} ({$validated['quantity']} units @ {$validated['purchase_cost']})"
                ]);
            }
            if (isset($validated['sale_price'])) {
                $inventory->sale_price = $validated['sale_price'];
            }

            $inventory->save();
            return false; // Not a hit
        });

        if ($isIdempotencyHit) {
            return response()->json([
                'message' => 'Inventory replenished (already processed)',
                'new_stock' => $inventory->stock
            ]);
        }

        AuditLog::log(
            'inventory_replenished',
            "Replenished '{$inventory->item_name}': Added {$validated['quantity']} units. New stock: {$inventory->stock}",
            ['inventory_id' => $inventory->id, 'added' => $validated['quantity'], 'new_total' => $inventory->stock]
        );

        return response()->json([
            'message' => 'Inventory replenished',
            'new_stock' => $inventory->stock,
            'purchase_cost' => $inventory->purchase_cost,
            'sale_price' => $inventory->sale_price
        ]);
    }

    public function searchMedicines(Request $request)
    {
        $query = trim(strtolower($request->get('q')));

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $medicines = \App\Models\MasterMedicine::whereRaw('LOWER(name) LIKE ?', ["%{$query}%"])
            ->where('specialty_id', $this->context->getSpecialtyId())
            ->where('is_active', true)
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($medicines);
    }
}
