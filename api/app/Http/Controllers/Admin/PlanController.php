<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\ModuleCacheService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlanController extends Controller
{
    /**
     * List all subscription plans.
     */
    public function index(Request $request)
    {
        $specialtyId = $request->query('specialty_id');

        $query = SubscriptionPlan::with(['modules' => function($q) {
            $q->select('modules.id', 'modules.key', 'modules.name', 'modules.description')
              ->withPivot('enabled');
        }]);

        if ($specialtyId) {
            $query->where('specialty_id', $specialtyId);
        }
        
        $plans = $query->get();

        $plans->load(['specialty.modules' => function($q) {
            $q->withPivot('enabled');
        }]);

        $plans->each(function ($plan) {
            if ($plan->specialty) {
                $specialtyModuleMap = $plan->specialty->modules->pluck('pivot.enabled', 'id')->toArray();

                foreach ($plan->modules as $module) {
                    $module->specialty_enabled = (bool) ($specialtyModuleMap[$module->id] ?? false);
                }
            }
            $plan->unsetRelation('specialty');
        });

        return response()->json($plans);
    }

    /**
     * Create a new subscription plan.
     * Hardened with Composite Unique validation and Auto-Module Provisioning.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                     => 'required|string|max:255',
            'specialty_id'             => 'required|exists:specialties,id',
            'tier'                     => [
                'required',
                'string',
                'in:starter,growth,pro,enterprise,basic',
                Rule::unique('subscription_plans')->where(function ($query) use ($request) {
                    return $query->where('specialty_id', $request->specialty_id);
                })
            ],
            'price'                    => 'required|numeric|min:0',
            'max_patients'             => 'required|integer|min:-1',
            'max_appointments_monthly' => 'required|integer|min:-1',
            'max_staff'                => 'required|integer|min:-1',
            'is_active'                => 'sometimes|boolean'
        ], [
            'tier.unique' => 'A plan with this tier already exists for this specialty.'
        ]);

        return DB::transaction(function() use ($validated) {
            try {
                // 1. Create Plan
                $plan = SubscriptionPlan::create([
                    'name'                     => $validated['name'],
                    'specialty_id'             => $validated['specialty_id'],
                    'tier'                     => $validated['tier'],
                    'price'                    => $validated['price'],
                    'max_patients'             => $validated['max_patients'],
                    'max_appointments_monthly' => $validated['max_appointments_monthly'],
                    'max_staff'                => $validated['max_staff'],
                    'is_active'                => $validated['is_active'] ?? true,
                ]);

                // 2. Auto-provision modules from specialty source of truth
                $specialtyModules = DB::table('specialty_module')
                    ->where('specialty_id', $validated['specialty_id'])
                    ->where('enabled', 1)
                    ->pluck('module_id');

                if ($specialtyModules->isNotEmpty()) {
                    $syncData = [];
                    foreach ($specialtyModules as $moduleId) {
                        $syncData[$moduleId] = ['enabled' => 1];
                    }
                    $plan->modules()->sync($syncData);
                }

                ModuleCacheService::invalidateByPlan($plan->id);

                return response()->json([
                    'message' => 'Plan created and provisioned successfully',
                    'plan'    => $plan->load('modules'),
                ], 201);

            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() === '23000') { // Duplicate entry
                    return response()->json([
                        'message' => 'Validation failed',
                        'errors'  => ['tier' => ['A plan with this tier already exists for this specialty.']]
                    ], 422);
                }
                throw $e;
            }
        });
    }

    /**
     * Update an existing plan's limits and features.
     */
    public function update(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'max_patients'             => 'required|integer',
            'max_appointments_monthly' => 'required|integer',
            'max_staff'                => 'required|integer',
            'tier'                     => [
                'required',
                'string',
                'in:starter,growth,pro,enterprise,basic',
                Rule::unique('subscription_plans')->where(function ($query) use ($plan) {
                    return $query->where('specialty_id', $plan->specialty_id);
                })->ignore($plan->id)
            ],
            'is_active'                => 'required|boolean',
            'modules'                  => 'required|array',
            'modules.*.id'             => 'required|exists:modules,id',
            'modules.*.enabled'        => 'required|boolean'
        ], [
            'tier.unique' => 'Another plan already uses this tier for this specialty.'
        ]);

        return DB::transaction(function() use ($validated, $plan) {
            try {
                $plan->update([
                    'max_patients'             => $validated['max_patients'],
                    'max_appointments_monthly' => $validated['max_appointments_monthly'],
                    'max_staff'                => $validated['max_staff'],
                    'tier'                     => $validated['tier'],
                    'is_active'                => $validated['is_active'],
                ]);

                $syncData = [];
                foreach ($validated['modules'] as $mod) {
                    $syncData[$mod['id']] = ['enabled' => $mod['enabled']];
                }
                $plan->modules()->sync($syncData);
                $plan->touch();
                
                ModuleCacheService::invalidateByPlan($plan->id);

                return response()->json([
                    'message' => 'Plan policies updated successfully',
                    'plan'    => $plan->load('modules')
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() === '23000') {
                    return response()->json([
                        'message' => 'Validation failed',
                        'errors'  => ['tier' => ['Another plan already uses this tier for this specialty.']]
                    ], 422);
                }
                throw $e;
            }
        });
    }

    /**
     * Delete a subscription plan.
     * Hardened with Dependency and Last-Plan checks.
     */
    public function destroy(SubscriptionPlan $plan)
    {
        try {
            return response()->json(app(DeleteManager::class)->delete('plan', $plan->id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
