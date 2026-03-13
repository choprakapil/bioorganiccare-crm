<?php

namespace App\Http\Controllers;

use App\Models\ClinicalCatalog;
use App\Models\DoctorServiceSetting;
use App\Models\ServiceSubmission;
use App\Models\ServiceAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Support\Context\TenantContext;
use App\Support\Services\ServiceNormalizer;

class ClinicalCatalogController extends Controller
{
    private TenantContext $context;

    public function __construct(TenantContext $context)
    {
        $this->context = $context;
    }

    use \App\Traits\NormalizesServiceName;

    // ══════════════════════════════════════════════════════════════════════════
    //  INDEX — unchanged from original
    // ══════════════════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $user = $this->context->getClinicOwner();
        $specialtyId = $this->context->getSpecialtyId();
        $view = $request->input('view', 'all');
        
        // ── Case A: Global Catalogue View ───────────────────────────────────
        if ($view === 'catalog') {
            $items = ClinicalCatalog::withTrashed()->where('specialty_id', $specialtyId)->get();
            $settings = \App\Models\DoctorServiceSetting::where('user_id', $user->id)
                        ->get()
                        ->keyBy('catalog_id');

            return response()->json($items->map(function ($item) use ($settings) {
                $setting = $settings->get($item->id);
                $item->is_active = $setting ? $setting->is_active : false;
                $item->original_fee = $item->default_fee;
                $item->is_archived = $item->deleted_at !== null;
                if ($setting && $setting->custom_price !== null) {
                    $item->default_fee = $setting->custom_price;
                }
                $item->is_usable = $item->deleted_at === null && $item->is_active;
                
                return ServiceNormalizer::normalize($item, 'global');
            }));
        }

        if ($view === 'my-services') {
            $local = \Illuminate\Support\Facades\DB::table('local_services')
                ->where('doctor_id', $user->id)
                ->where('is_promoted', false)
                ->get()
                ->map(function ($local) {
                    return ServiceNormalizer::normalize([
                        'id'              => $local->id,
                        'item_name'       => $local->item_name,
                        'type'            => $local->type ?? 'Treatment',
                        'default_fee'     => $local->default_fee,
                        'original_fee'    => $local->default_fee,
                        'is_active'       => is_null($local->deleted_at),
                        'approval_status' => 'local',
                        'specialty_id'    => $local->specialty_id,
                        'is_local'        => true,
                        'is_usable'       => is_null($local->deleted_at),
                    ], 'local');
                });

            $global = \Illuminate\Support\Facades\DB::table('doctor_service_settings')
                ->join('clinical_catalog', 'doctor_service_settings.catalog_id', '=', 'clinical_catalog.id')
                ->where('doctor_service_settings.user_id', $user->id)
                ->where('doctor_service_settings.is_active', true)
                ->whereNull('clinical_catalog.deleted_at')
                ->select(
                    'clinical_catalog.id',
                    'clinical_catalog.item_name',
                    'clinical_catalog.type',
                    'clinical_catalog.default_fee as original_fee',
                    'doctor_service_settings.custom_price as default_fee',
                    'clinical_catalog.specialty_id'
                )
                ->get()
                ->map(function ($global) {
                    return ServiceNormalizer::normalize([
                        'id'              => $global->id,
                        'item_name'       => $global->item_name,
                        'type'            => $global->type ?? 'Treatment',
                        'default_fee'     => $global->default_fee ?? $global->original_fee,
                        'original_fee'    => $global->original_fee,
                        'is_active'       => true,
                        'approval_status' => 'approved',
                        'specialty_id'    => $global->specialty_id,
                        'is_local'        => false,
                        'is_usable'       => true,
                    ], 'global');
                });

            return response()->json($local->concat($global)->values());
        }

        // ── Default / Case A: Global Catalogue View ────────────────────────
        $items = ClinicalCatalog::withTrashed()->where('specialty_id', $specialtyId)->get();
        $settings = \App\Models\DoctorServiceSetting::where('user_id', $user->id)
                    ->get()
                    ->keyBy('catalog_id');

        return response()->json($items->map(function ($item) use ($settings) {
            $setting = $settings->get($item->id);
            $item->is_active = $setting ? $setting->is_active : false;
            $item->original_fee = $item->default_fee;
            $item->is_archived = $item->deleted_at !== null;
            if ($setting && $setting->custom_price !== null) {
                $item->default_fee = $setting->custom_price;
            }
            $item->is_usable = $item->deleted_at === null && $item->is_active;
            
            return ServiceNormalizer::normalize($item, 'global');
        }));
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  STORE — NEW MODERATION FLOW (replaces direct clinical_catalog insert)
    // ══════════════════════════════════════════════════════════════════════════

    public function store(Request $request)
    {
        $user = $this->context->getClinicOwner();
        $specialtyId = $this->context->getSpecialtyId();

        // Safety guard checks before standard validation
        if (strtolower($request->input('type', '')) === 'medicine') {
            return response()->json([
                'message' => 'Medicines must be created from Pharmacy Inventory.'
            ], 422);
        }

        $validated = $request->validate([
            'item_name'   => 'required|string|max:255',
            'type'        => 'required|in:Treatment',
            'default_fee' => 'required|numeric|min:0',
            'category_id' => [
                'nullable',
                Rule::exists('clinical_service_categories', 'id')->where(function ($query) use ($specialtyId) {
                    $query->where('specialty_id', $specialtyId);
                }),
            ],
        ]);

        // ── Step 1: Normalize ─────────────────────────────────────────────────
        $normalized = $this->normalizeServiceName($validated['item_name']);
        
        // ── Stop Duplicate Treatments ─────────────────────────────────────────
        $existingLocal = DB::table('local_services')
            ->where('doctor_id', $user->id)
            ->where('normalized_name', $normalized)
            ->whereNull('deleted_at')
            ->exists();
            
        if ($existingLocal) {
            return response()->json([
                'message' => 'This treatment already exists.'
            ], 409);
        }

        // ── Step 2: Check global catalog for existing normalized match ────────
        $existingCatalogItem = ClinicalCatalog::where('specialty_id', $specialtyId)
            ->where('normalized_name', $normalized)
            ->first();

        if ($existingCatalogItem) {
            // ── Path A: Service already in catalog → auto-attach to doctor ────
            DB::transaction(function () use ($user, $existingCatalogItem, $validated) {
                DoctorServiceSetting::updateOrCreate(
                    [
                        'user_id'    => $user->id,
                        'catalog_id' => $existingCatalogItem->id,
                    ],
                    [
                        'custom_price' => $validated['default_fee'],
                        'is_active'    => true,
                    ]
                );
            });

            return response()->json([
                'status'  => 'attached',
                'message' => 'Service already exists and has been added to your services.',
                'item'    => ServiceNormalizer::normalize($existingCatalogItem, 'global'),
            ], 200);
        }

        // ── Path B: New service → create local service ───────────────────
        try {
            $localServiceId = DB::table('local_services')->insertGetId([
                'doctor_id'           => $user->id,
                'specialty_id'        => $specialtyId,
                'item_name'           => trim($validated['item_name']),
                'normalized_name'     => $normalized,
                'type'                => $validated['type'],
                'default_fee'         => $validated['default_fee'],
                'is_promoted'         => false,
                'promoted_catalog_id' => null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Check if it's a unique constraint violation (MySQL exact/race condition)
            if ($e->errorInfo[1] == 1062) {
                return response()->json([
                    'message' => 'This treatment already exists.'
                ], 409);
            }
            throw $e;
        }

        return response()->json([
            'status'     => 'created',
            'message'    => 'Local service created successfully.',
            'submission' => [
                'id'             => 'local_' . $localServiceId,
                'original_name'  => trim($validated['item_name']),
                'status'         => 'active',
                'created_at'     => now(),
            ],
        ], 201);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  UPDATE SETTINGS — unchanged from original
    // ══════════════════════════════════════════════════════════════════════════

    public function updateSettings(Request $request)
    {
        $user = $this->context->getClinicOwner();
        $ownerId = $user->id;
        $specialtyId = $this->context->getSpecialtyId();
        
        $validated = $request->validate([
            'id'           => 'required|integer',
            'type'         => 'required|in:local,global',
            'custom_price' => 'nullable|numeric|min:0',
            'is_active'    => 'required|boolean'
        ]);

        if ($validated['type'] === 'local') {
            $updateData = [];
            if ($validated['custom_price'] !== null) {
                $updateData['default_fee'] = $validated['custom_price'];
            }
            if ($validated['is_active']) {
                $updateData['deleted_at'] = null;
            } else {
                $updateData['deleted_at'] = now();
            }

            DB::table('local_services')
                ->where('id', $validated['id'])
                ->where('doctor_id', $ownerId)
                ->update($updateData);

            return response()->json(['status' => 'success', 'type' => 'local']);
        }

        // Global updating logic:
        $setting = \App\Models\DoctorServiceSetting::updateOrCreate(
            [
                'user_id'    => $ownerId,
                'catalog_id' => $validated['id']
            ],
            [
                'custom_price' => $validated['custom_price'],
                'is_active'    => $validated['is_active']
            ]
        );

        if ($validated['is_active']) {
            event(new \App\Events\CatalogActivated('clinical', $validated['id'], [
                'doctor_id' => auth()->id(),
                'custom_price' => $setting->custom_price
            ]));
        } else {
            event(new \App\Events\CatalogDeactivated('clinical', $validated['id'], [
                'doctor_id' => auth()->id(),
                'custom_price' => $setting->custom_price
            ]));
        }

        return response()->json($setting);
    }
}
