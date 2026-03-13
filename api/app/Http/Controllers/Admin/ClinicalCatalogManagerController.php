<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicalCatalog;
use App\Models\ClinicalServiceCategory;
use App\Models\Specialty;
use Illuminate\Http\Request;
use App\Support\Services\ServiceNormalizer;

class ClinicalCatalogManagerController extends Controller
{
    /**
     * Get all categories and items for a specific specialty.
     */
    public function index(Specialty $specialty)
    {
        $items = ClinicalCatalog::withTrashed()
            ->where('specialty_id', $specialty->id)
            ->with('category')
            ->orderBy('item_name')
            ->get();

        return response()->json([
            'active'   => $items->filter(fn($i) => $i->deleted_at === null)->map(fn($i) => ServiceNormalizer::normalize($i, 'global'))->values(),
            'inactive' => [],
            'archived' => $items->filter(fn($i) => $i->deleted_at !== null)->map(fn($i) => ServiceNormalizer::normalize($i, 'global'))->values(),
        ]);
    }

    /**
     * Create a new category.
     */
    public function storeCategory(Request $request, Specialty $specialty)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'integer|min:0'
        ]);

        $category = $specialty->categories()->create([
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => true
        ]);

        return response()->json($category);
    }

    /**
     * Update a category.
     */
    public function updateCategory(Request $request, ClinicalServiceCategory $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'sort_order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean'
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    /**
     * Create a new service item.
     */
    public function storeService(Request $request, Specialty $specialty)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:clinical_service_categories,id',
            'item_name' => 'required|string|max:255',
            'type' => 'required|in:Treatment,Consultation,Procedure', // Strict Types
            'default_fee' => 'required|numeric|min:0'
        ]);

        // Ensure category belongs to specialty
        $category = ClinicalServiceCategory::where('id', $validated['category_id'])
            ->where('specialty_id', $specialty->id)
            ->firstOrFail();

        $item = ClinicalCatalog::create([
            'specialty_id' => $specialty->id,
            'category_id' => $validated['category_id'],
            'item_name' => $validated['item_name'],
            'type' => $validated['type'],
            'default_fee' => $validated['default_fee']
        ]);

        event(new \App\Events\CatalogEntityCreated('clinical', $item->id, ['version' => $item->version ?? null]));
        
        return response()->json(ServiceNormalizer::normalize($item, 'global'));
    }

    public function updateService(Request $request, ClinicalCatalog $item)
    {
        $validated = $request->validate([
            'item_name' => 'sometimes|string|max:255',
            'default_fee' => 'sometimes|numeric|min:0',
            'category_id' => 'sometimes|exists:clinical_service_categories,id',
            'type' => 'sometimes|in:Treatment,Consultation,Procedure'
        ]);

        if (isset($validated['category_id'])) {
            // Verify category matches item's specialty
            $category = ClinicalServiceCategory::where('id', $validated['category_id'])
                ->where('specialty_id', $item->specialty_id)
                ->firstOrFail();
        }

        $old = $item->toArray();
        $item->update($validated);
        $item->increment('version');

        \Illuminate\Support\Facades\DB::table('catalog_versions')->insert([
            'entity_type' => 'clinical',
            'entity_id' => $item->id,
            'version_number' => $item->version,
            'changed_by_user_id' => auth()->id(),
            'old_payload' => json_encode($old),
            'new_payload' => json_encode($item->fresh()->toArray()),
            'created_at' => now(),
        ]);

        event(new \App\Events\CatalogEntityUpdated('clinical', $item->id, ['version' => $item->version ?? null]));

        return response()->json(ServiceNormalizer::normalize($item, 'global'));
    }

    public function destroyCategory(Request $request, ClinicalServiceCategory $category)
    {
        try {
            return response()->json(app(DeleteManager::class)->delete('category', $category->id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroyService(ClinicalCatalog $item)
    {
        try {
            return response()->json(app(DeleteManager::class)->delete('service', $item->id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Bulk Import Services
     */
    public function import(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.specialty_name' => 'required|string',
            'items.*.category_name' => 'required|string',
            'items.*.service_name' => 'required|string',
            'items.*.type' => 'required|string|in:Treatment,Consultation,Procedure',
            'items.*.default_fee' => 'required|numeric|min:0',
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
                $category = ClinicalServiceCategory::firstOrCreate(
                    ['specialty_id' => $specialty->id, 'name' => $row['category_name']],
                    ['sort_order' => 0, 'is_active' => true]
                );

                // 3. Create Service (Prevent Duplicate Names in Category)
                $exists = ClinicalCatalog::where('category_id', $category->id)
                    ->where('item_name', $row['service_name'])
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                ClinicalCatalog::create([
                    'specialty_id' => $specialty->id,
                    'category_id' => $category->id,
                    'item_name' => $row['service_name'],
                    'type' => $row['type'],
                    'default_fee' => $row['default_fee'],
                    'is_active' => true // Master catalog items are active by default (availability logic is separate)
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

    public function restoreService(Specialty $specialty, $id)
    {
        try {
            return response()->json(app(DeleteManager::class)->restore('service', $id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function forceDeleteService(Specialty $specialty, $id)
    {
        try {
            $result = app(DeleteManager::class)->forceDelete('service', $id);
            $code   = $result['status'] === 'blocked' ? 409 : 200;
            return response()->json($result, $code);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * List all archived (soft-deleted) clinical services across all specialties.
     * Used by global Governance Dashboard.
     */
    public function archived()
    {
        $global = ClinicalCatalog::onlyTrashed()
            ->with('specialty:id,name')
            ->orderBy('deleted_at', 'desc')
            ->get()
            ->map(fn($item) => ServiceNormalizer::normalize($item, 'global'));

        $local = \App\Models\LocalService::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get()
            ->map(fn($item) => ServiceNormalizer::normalize($item, 'local'));
            
        return response()->json(
            $global->concat($local)->sortByDesc('deleted_at')->values()
        );
    }
}
