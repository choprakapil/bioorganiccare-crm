<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Support\Services\ServiceNormalizer;

class AdminHybridPromotionController extends Controller
{
    public function promoteService(Request $request, $id, $skipApprovalCheck = false)
    {
        $adminId = Auth::id() ?: 1;

        return DB::transaction(function () use ($request, $id, $adminId, $skipApprovalCheck) {
            $local = DB::table('local_services')
                ->where('id', $id)
                ->where('is_promoted', false)
                ->lockForUpdate()
                ->first();

            if (!$local) {
                return response()->json(['error' => 'Local service not found or already promoted'], 404);
            }

            // Conflict Exact Check
            $exact = DB::table('clinical_catalog')
                ->where('specialty_id', $local->specialty_id)
                ->where('normalized_name', $local->normalized_name)
                ->first();

            if ($exact) {
                // Link directly to existing global item instead of conflict error
                DB::table('local_services')
                    ->where('id', $local->id)
                    ->update([
                        'is_promoted' => true,
                        'promoted_catalog_id' => $exact->id,
                        'updated_at' => now(),
                        'deleted_at' => now()
                    ]);
                
                return response()->json(ServiceNormalizer::normalize(DB::table('clinical_catalog')->where('id', $exact->id)->first(), 'global'));
            }

            // Similar Check (Bidirectional)
            $force = $request->input('force_promote', false) || $request->input('force_promote') === 'true';
            if (!$force) {
                $similars = DB::table('clinical_catalog')
                    ->where('specialty_id', $local->specialty_id)
                    ->where(function ($q) use ($local) {
                        $q->where('normalized_name', 'LIKE', '%' . $local->normalized_name . '%')
                          ->orWhereRaw('? LIKE CONCAT("%", normalized_name, "%")', [$local->normalized_name]);
                    })
                    ->select('id', 'item_name', 'normalized_name', 'type', 'default_fee')
                    ->get()
                    ->map(fn($s) => ServiceNormalizer::normalize($s, 'global'));
                if ($similars->count() > 0) {
                    return response()->json([
                        'status' => 'conflict_similar',
                        'suggestions' => $similars
                    ], 409);
                }
            }

            if (!$skipApprovalCheck && config('app.promotion_requires_approval', false)) {
                $reqId = DB::table('promotion_requests')->insertGetId([
                    'entity_type' => 'clinical',
                    'local_id' => $local->id,
                    'snapshot_json' => json_encode($local),
                    'status' => 'pending',
                    'created_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return response()->json([
                    'status' => 'pending_approval',
                    'message' => 'Promotion requires dual admin approval. Request created.',
                    'promotion_request_id' => $reqId
                ], 202);
            }

            $globalId = DB::table('clinical_catalog')->insertGetId([
                'specialty_id' => $local->specialty_id,
                'item_name' => $local->item_name,
                'normalized_name' => $local->normalized_name,
                'type' => $local->type,
                'default_fee' => $local->default_fee,
                'created_by_user_id' => $local->doctor_id,
                'approved_by_user_id' => $adminId,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('catalog_versions')->insert([
                'entity_type' => 'clinical',
                'entity_id' => $globalId,
                'version_number' => 1,
                'changed_by_user_id' => $adminId,
                'old_payload' => json_encode([]),
                'new_payload' => json_encode((array) $local),
                'created_at' => now(),
            ]);

            DB::table('catalog_audit_logs')->insert([
                'entity_type' => 'clinical',
                'entity_id' => $globalId,
                'action' => 'promoted_from_local',
                'performed_by_user_id' => $adminId,
                'metadata' => json_encode(['original_local_id' => $local->id]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'CLI',
                'created_at' => now(),
            ]);

            // Attach doctor via doctor_service_settings
            DB::insert('INSERT IGNORE INTO doctor_service_settings (user_id, catalog_id, custom_price, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)', [
                $local->doctor_id,
                $globalId,
                $local->default_fee,
                true,
                now(),
                now()
            ]);

            // Update local_services retro link
            DB::table('local_services')
                ->where('id', $local->id)
                ->update([
                    'is_promoted' => true,
                    'promoted_catalog_id' => $globalId,
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            $promotedGlobal = DB::table('clinical_catalog')->where('id', $globalId)->first();
            return response()->json(ServiceNormalizer::normalize($promotedGlobal, 'global'));
        });
    }

    public function promoteMedicine(Request $request, $id, $skipApprovalCheck = false)
    {
        $adminId = Auth::id() ?: 1;

        return DB::transaction(function () use ($request, $id, $adminId, $skipApprovalCheck) {
            $local = DB::table('local_medicines')
                ->where('id', $id)
                ->where('is_promoted', false)
                ->lockForUpdate()
                ->first();

            if (!$local) {
                return response()->json(['error' => 'Local medicine not found or already promoted'], 404);
            }

            // Conflict Exact Check
            $exact = DB::table('master_medicines')
                ->where('specialty_id', $local->specialty_id)
                ->where('normalized_name', $local->normalized_name)
                ->first();

            if ($exact) {
                return response()->json([
                    'status' => 'conflict_exact',
                    'global_id' => $exact->id,
                    'message' => 'Exact match already exists.'
                ], 409);
            }

            // Similar Check (Bidirectional)
            $force = $request->input('force_promote', false) || $request->input('force_promote') === 'true';
            if (!$force) {
                $similars = DB::table('master_medicines')
                    ->where('specialty_id', $local->specialty_id)
                    ->where(function ($q) use ($local) {
                        $q->where('normalized_name', 'LIKE', '%' . $local->normalized_name . '%')
                          ->orWhereRaw('? LIKE CONCAT("%", normalized_name, "%")', [$local->normalized_name]);
                    })
                    ->select('id', 'name as item_name')
                    ->get();
                if ($similars->count() > 0) {
                    return response()->json([
                        'status' => 'conflict_similar',
                        'suggestions' => $similars
                    ], 409);
                }
            }

            if (!$skipApprovalCheck && config('app.promotion_requires_approval', false)) {
                $reqId = DB::table('promotion_requests')->insertGetId([
                    'entity_type' => 'pharmacy',
                    'local_id' => $local->id,
                    'snapshot_json' => json_encode($local),
                    'status' => 'pending',
                    'created_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return response()->json([
                    'status' => 'pending_approval',
                    'message' => 'Promotion requires dual admin approval. Request created.',
                    'promotion_request_id' => $reqId
                ], 202);
            }

            $categoryId = $request->category_id ?? null;

            if (!$categoryId) {
                $categoryId = DB::table('pharmacy_categories')
                    ->where('specialty_id', $local->specialty_id)
                    ->where('name', 'Others')
                    ->value('id');
            }

            $categoryName = DB::table('pharmacy_categories')
                ->where('id', $categoryId)
                ->value('name') ?? 'Others';

            $globalId = DB::table('master_medicines')->insertGetId([
                'specialty_id' => $local->specialty_id,
                'name' => $local->item_name,
                'normalized_name' => $local->normalized_name,
                'default_purchase_price' => $local->buy_price,
                'default_selling_price' => $local->sell_price,
                'created_by_user_id' => $local->doctor_id,
                'approved_by_user_id' => $adminId,

                // SAFE CATEGORY LINK
                'pharmacy_category_id' => $categoryId,
                'category' => $categoryName,

                'unit' => 'Unit',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('catalog_versions')->insert([
                'entity_type' => 'pharmacy',
                'entity_id' => $globalId,
                'version_number' => 1,
                'changed_by_user_id' => $adminId,
                'old_payload' => json_encode([]),
                'new_payload' => json_encode((array) $local),
                'created_at' => now(),
            ]);

            DB::table('catalog_audit_logs')->insert([
                'entity_type' => 'pharmacy',
                'entity_id' => $globalId,
                'action' => 'promoted_from_local',
                'performed_by_user_id' => $adminId,
                'metadata' => json_encode(['original_local_id' => $local->id]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'CLI',
                'created_at' => now(),
            ]);

            // Retro-link inventory
            DB::table('inventory')
                ->where('doctor_id', $local->doctor_id)
                ->whereNull('master_medicine_id')
                ->whereRaw('LOWER(item_name) = ?', [$local->normalized_name])
                ->update([
                    'master_medicine_id' => $globalId,
                    'updated_at' => now()
                ]);

            // Retro-link duplicates
            DB::table('local_medicines')
                ->where('id', $local->id)
                ->update([
                    'is_promoted' => true,
                    'promoted_master_id' => $globalId,
                    'updated_at' => now()
                ]);

            $promotedMaster = DB::table('master_medicines')->where('id', $globalId)->first();
            return response()->json($promotedMaster);
        });
    }

    public function bulkPromote(Request $request)
    {
        $services = $request->input('services', []);
        $medicines = $request->input('medicines', []);

        $success = 0;
        $failed = [];

        DB::beginTransaction();
        try {
            foreach ($services as $sid) {
                // If using config approach vs param, since we updated to parameter we don't pass skipApprovalCheck for bulk
                // unless intended. For now we just pass through.
                $res = $this->promoteService($request, $sid);
                if ($res->getStatusCode() >= 400) {
                    $failed[] = $sid;
                } else {
                    $success++;
                }
            }
            foreach ($medicines as $mid) {
                $res = $this->promoteMedicine($request, $mid);
                if ($res->getStatusCode() >= 400) {
                    $failed[] = $mid;
                } else {
                    $success++;
                }
            }

            if (count($failed) > 0) {
                throw new \Exception("Bulk promotion failed for items: " . implode(',', $failed));
            }

            DB::commit();
            return response()->json(['success_count' => $success, 'failed' => []]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage(), 'success_count' => 0, 'failed' => $failed], 500);
        }
    }

    public function approvePromotion(Request $request, $reqId)
    {
        $adminId = Auth::id() ?: 1;

        return DB::transaction(function () use ($reqId, $adminId, $request) {
            $req = DB::table('promotion_requests')->where('id', $reqId)->where('status', 'pending')->lockForUpdate()->first();
            if (!$req) return response()->json(['error' => 'not found'], 404);

            $snap = json_decode($req->snapshot_json);
            
            // 1. Validate drift first
            if ($req->entity_type === 'clinical') {
                $cur = DB::table('local_services')->where('id', $req->local_id)->first();
                if (!$cur || $cur->normalized_name !== $snap->normalized_name || (float)$cur->default_fee !== (float)$snap->default_fee || $cur->type !== $snap->type) {
                    throw new \Exception("PROMOTION DRIFT DETECTED");
                }
            } else {
                $cur = DB::table('local_medicines')->where('id', $req->local_id)->first();
                if (!$cur || $cur->normalized_name !== $snap->normalized_name || (float)$cur->buy_price !== (float)$snap->buy_price || (float)$cur->sell_price !== (float)$snap->sell_price) {
                    throw new \Exception("PROMOTION DRIFT DETECTED");
                }
            }

            // 2. Update promotion_requests
            DB::table('promotion_requests')->where('id', $reqId)->update([
                'status' => 'approved', 
                'approved_by' => $adminId, 
                'updated_at' => now()
            ]);

            // 3. Temporarily disable approval requirement natively implemented via parameter
            // 4. Call promoteService internally and 5. Return result
            if ($req->entity_type === 'clinical') {
                return $this->promoteService($request, $req->local_id, true);
            } else {
                return $this->promoteMedicine($request, $req->local_id, true);
            }
        });
    }

    public function listPending()
    {
        $pending = DB::table('promotion_requests')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($pending);
    }

    public function rejectPromotion($id)
    {
        DB::table('promotion_requests')
            ->where('id', $id)
            ->update([
                'status' => 'rejected',
                'updated_at' => now()
            ]);

        return response()->json(['status' => 'rejected']);
    }
}