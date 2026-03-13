<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use App\Services\ModuleCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SpecialtyController extends Controller
{
    /**
     * List all specialties.
     */
    public function index()
    {
        $specialties = Specialty::with([
            'modules' => function ($q) {
                $q->select('modules.id', 'modules.key', 'modules.name')
                  ->withPivot('enabled');
            }
        ])->whereNull('deleted_at')->get();
    
        // Manually compute enabled module count and capabilities count
        $specialties->each(function ($spec) {
            $spec->enabled_modules_count = $spec->modules
                ->where('pivot.enabled', true)
                ->count();
    
            $spec->enabled_capabilities_count = collect($spec->capabilities ?? [])
                ->filter(fn($v) => $v === true)
                ->count();
        });
    
        return response()->json($specialties);
    }

    /**
     * List archived (soft-deleted) specialties.
     */
    public function archived()
    {
        return response()->json(Specialty::onlyTrashed()->orderBy('deleted_at', 'desc')->get());
    }

    /**
     * Store a new specialty.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:specialties,name',
            'capabilities' => 'required|array',
            'is_active' => 'boolean',
            'modules' => 'nullable|array'
        ]);

        $specialty = Specialty::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'features' => [], // Legacy compatibility
            'capabilities' => $validated['capabilities'],
            'is_active' => $validated['is_active'] ?? true
        ]);
        
        if (!empty($validated['modules'])) {
            $syncData = $this->prepareModuleSyncData($validated['modules']);
            $specialty->modules()->sync($syncData);
            $specialty->touch();
            ModuleCacheService::invalidateBySpecialty($specialty->id);
        }

        return response()->json($specialty->load('modules'), 201);
    }

    /**
     * Update an existing specialty.
     */
    public function update(Request $request, Specialty $specialty)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|unique:specialties,name,' . $specialty->id,
            'capabilities' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
            'modules' => 'sometimes|array'
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        
        $modelData = collect($validated)->except(['modules'])->toArray();
        $specialty->update($modelData);

        if (isset($validated['modules'])) {
            $syncData = $this->prepareModuleSyncData($validated['modules']);
            $specialty->modules()->sync($syncData);
            $specialty->touch();
            ModuleCacheService::invalidateBySpecialty($specialty->id);
        }

        return response()->json($specialty->load('modules'));
    }

    /**
     * Delete a specialty.
     */
    public function destroy(Specialty $specialty)
    {
        try {
            return response()->json(app(DeleteManager::class)->delete('specialty', $specialty->id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Helper to resolve module keys to IDs and prepare sync data.
     */
    private function prepareModuleSyncData(array $modulesInput)
    {
        $syncData = [];
        $keys = [];
        
        // First pass: Collect IDs and Keys
        foreach ($modulesInput as $mod) {
            $enabled = $mod['enabled'] ?? true;
            
            if (isset($mod['id'])) {
                $syncData[$mod['id']] = ['enabled' => $enabled];
            } elseif (isset($mod['key'])) {
                $keys[$mod['key']] = $enabled;
            }
        }

        // Resolve keys to IDs
        if (!empty($keys)) {
            $resolvedModules = \App\Models\Module::whereIn('key', array_keys($keys))->pluck('id', 'key');
            foreach ($resolvedModules as $key => $id) {
                $syncData[$id] = ['enabled' => $keys[$key]];
            }
        }

        return $syncData;
    }

    /**
     * Restore a soft-deleted specialty.
     */
    public function restore($id)
    {
        try {
            return response()->json(app(DeleteManager::class)->restore('specialty', $id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Permanently delete a soft-deleted specialty.
     * Blocked if any dependencies remain.
     */
    public function forceDelete($id)
    {
        try {
            $result = app(DeleteManager::class)->forceDelete('specialty', $id);
            $code   = $result['status'] === 'blocked' ? 409 : 200;
            return response()->json($result, $code);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Return a dependency count summary for a specialty.
     * Used by the UI before allowing force-delete — surfaces all blockers.
     * Works on both active and archived specialties (withTrashed).
     */
    public function dependencySummary($id)
    {
        try {
            return response()->json(app(DeleteManager::class)->dependencySummary('specialty', $id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
