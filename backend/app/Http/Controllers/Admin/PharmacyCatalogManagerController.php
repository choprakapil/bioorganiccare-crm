<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use App\Models\PharmacyCategory;
use App\Models\MasterMedicine;
use Illuminate\Http\Request;

class PharmacyCatalogManagerController extends Controller
{
    /**
     * Get all categories and medicines for a specialty
     */
    public function index(Specialty $specialty)
    {
        $categories = PharmacyCategory::where('specialty_id', $specialty->id)
            ->with(['medicines' => function ($q) {
                $q->withTrashed()
                  ->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories);
    }

    public function storeCategory(Request $request, Specialty $specialty)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'sort_order' => 'integer'
        ]);

        $category = PharmacyCategory::create([
            'specialty_id' => $specialty->id,
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'] ?? 0
        ]);

        return response()->json($category, 201);
    }

    public function updateCategory(Request $request, PharmacyCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'sort_order' => 'integer'
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    public function storeMedicine(Request $request, Specialty $specialty)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:pharmacy_categories,id',
            'item_name' => 'required|string',
            'type' => 'nullable|string', 
            'default_purchase_price' => 'nullable|numeric',
            'default_selling_price' => 'nullable|numeric',
            'unit' => 'nullable|string|in:Strip,Bottle,Tube,Sachet,Vial,Ampoule,Packet,Kit,Unit,Piece,Box,Jar,Can,Capsule,Injection,Cream,Ointment,Gel,Spray,Drops'
        ]);

        // Ensure category belongs to specialty
        $category = PharmacyCategory::findOrFail($validated['category_id']);
        if ($category->specialty_id !== $specialty->id) {
            return response()->json(['message' => 'Category mismatch'], 400);
        }

        $medicine = MasterMedicine::create([
            'name' => $validated['item_name'],
            'specialty_id' => $specialty->id,
            'pharmacy_category_id' => $validated['category_id'],
            'unit' => $validated['unit'] ?? 'Unit',
            'category' => $category->name, 
            'default_purchase_price' => $validated['default_purchase_price'] ?? 0,
            'default_selling_price' => $validated['default_selling_price'] ?? 0,
            'is_active' => true
        ]);

        event(new \App\Events\CatalogEntityCreated('pharmacy', $medicine->id, ['version' => $medicine->version ?? null]));

        return response()->json($medicine, 201);
    }

    public function updateMedicine(Request $request, MasterMedicine $medicine)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|exists:pharmacy_categories,id',
            'item_name' => 'sometimes|string',
            'default_purchase_price' => 'sometimes|numeric',
            'default_selling_price' => 'sometimes|numeric',
            'unit' => 'sometimes|string|in:Strip,Bottle,Tube,Sachet,Vial,Ampoule,Packet,Kit,Unit,Piece,Box,Jar,Can,Capsule,Injection,Cream,Ointment,Gel,Spray,Drops'
        ]);

        $data = [];
        if (isset($validated['item_name'])) $data['name'] = $validated['item_name'];
        if (isset($validated['category_id'])) {
            $data['pharmacy_category_id'] = $validated['category_id'];
            $cat = PharmacyCategory::find($validated['category_id']);
            if ($cat) $data['category'] = $cat->name;
        }
        if (isset($validated['default_purchase_price'])) $data['default_purchase_price'] = $validated['default_purchase_price'];
        if (isset($validated['default_selling_price'])) $data['default_selling_price'] = $validated['default_selling_price'];
        if (isset($validated['unit'])) $data['unit'] = $validated['unit'];

        $old = $medicine->toArray();
        $medicine->update($data);
        $medicine->increment('version');

        \Illuminate\Support\Facades\DB::table('catalog_versions')->insert([
            'entity_type' => 'pharmacy',
            'entity_id' => $medicine->id,
            'version_number' => $medicine->version,
            'changed_by_user_id' => auth()->id(),
            'old_payload' => json_encode($old),
            'new_payload' => json_encode($medicine->fresh()->toArray()),
            'created_at' => now(),
        ]);

        event(new \App\Events\CatalogEntityUpdated('pharmacy', $medicine->id, ['version' => $medicine->version ?? null]));

        return response()->json($medicine);
    }

    public function destroyCategory(PharmacyCategory $category)
    {
        try {
            return response()->json(app(DeleteManager::class)->delete('pharmacy_category', $category->id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroyMedicine(MasterMedicine $medicine)
    {
        try {
            return response()->json(app(DeleteManager::class)->delete('medicine', $medicine->id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function restoreMedicine($id)
    {
        try {
            return response()->json(app(DeleteManager::class)->restore('medicine', $id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function forceDeleteMedicine($id)
    {
        try {
            $result = app(DeleteManager::class)->forceDelete('medicine', $id);
            $code   = $result['status'] === 'blocked' ? 409 : 200;
            return response()->json($result, $code);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Bulk Import Medicines
     */
    public function import(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.specialty_name' => 'required|string',
            'items.*.category_name' => 'required|string',
            'items.*.medicine_name' => 'required|string',
            'items.*.unit' => 'nullable|string|in:Strip,Bottle,Tube,Sachet,Vial,Ampoule,Packet,Kit,Unit,Piece,Box,Jar,Can,Capsule,Injection,Cream,Ointment,Gel,Spray,Drops',
            'items.*.default_buy_price' => 'nullable|numeric|min:0',
            'items.*.default_sell_price' => 'nullable|numeric|min:0',
        ]);

        $added = 0;
        $skipped = 0;
        $errors = [];

        foreach ($request->items as $index => $row) {
            try {
                // 1. Resolve Specialty
                $specialty = Specialty::where('name', $row['specialty_name'])->first();
                if (!$specialty) {
                    $errors[] = "Row " . ($index + 1) . ": Specialty '{$row['specialty_name']}' not found.";
                    $skipped++;
                    continue;
                }

                // 2. Resolve/Create Category
                $category = PharmacyCategory::firstOrCreate(
                    ['specialty_id' => $specialty->id, 'name' => $row['category_name']],
                    ['sort_order' => 0]
                );

                // 3. Create Medicine (Prevent Duplicates)
                $exists = MasterMedicine::where('pharmacy_category_id', $category->id)
                    ->where('name', $row['medicine_name'])
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                MasterMedicine::create([
                    'name' => $row['medicine_name'],
                    'specialty_id' => $specialty->id,
                    'pharmacy_category_id' => $category->id,
                    'unit' => $row['unit'] ?? 'Unit',
                    'category' => $category->name, // Legacy column redundancy? Keeping for safety.
                    'default_purchase_price' => $row['default_buy_price'] ?? 0,
                    'default_selling_price' => $row['default_sell_price'] ?? 0,
                    'is_active' => true
                ]);

                $added++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                $skipped++;
            }
        }

        return response()->json([
            'message' => "Import complete. Added: $added, Skipped: $skipped.",
            'errors' => $errors
        ]);
    }

    /**
     * List all archived (soft-deleted) medicines across all specialties.
     * Used by global Governance Dashboard.
     */
    public function archived()
    {
        return response()->json(
            MasterMedicine::onlyTrashed()
                ->with('specialty:id,name')
                ->orderBy('deleted_at', 'desc')
                ->get()
        );
    }
}
