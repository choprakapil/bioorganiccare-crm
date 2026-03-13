<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicalCatalog;
use App\Models\DoctorServiceSetting;
use App\Models\ServiceSubmission;
use App\Models\ServiceAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Support\Services\ServiceNormalizer;

class AdminServiceModerationController extends Controller
{
    use \App\Traits\NormalizesServiceName;

    // ══════════════════════════════════════════════════════════════════════════
    //  GET /api/admin/service-submissions
    //  List submissions — supports ?status= and ?specialty_id= filters
    // ══════════════════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $query = ServiceSubmission::with([
            'submittedBy:id,name,email',
            'specialty:id,name',
            'reviewedBy:id,name',
        ])
        ->latest();

        // Filter by status (defaults to pending queue)
        if ($request->filled('status')) {
            $request->validate(['status' => 'in:pending,approved,rejected']);
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'pending');
        }

        // Filter by specialty
        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        }

        $submissions = $query->paginate(25);

        return response()->json($submissions);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  POST /api/admin/service-submissions/{id}/approve
    // ══════════════════════════════════════════════════════════════════════════

    public function approve(Request $request, int $id)
    {
        $admin = Auth::user();

        $submission = ServiceSubmission::findOrFail($id);

        // Guard: only pending submissions can be approved
        if (! $submission->isPending()) {
            return response()->json([
                'message' => "Submission is already {$submission->status} and cannot be approved again.",
            ], 422);
        }

        $result = DB::transaction(function () use ($submission, $admin, $request) {

            // ── Step 1: Re-normalize (defensive — source of truth is always fresh) ──
            $normalized = $this->normalizeServiceName($submission->original_name);

            // ── Step 2: Check if normalized name already in clinical_catalog ────────
            // Include TRASHED items to prevent Unique Constraint Violation on soft-deleted rows
            $existingCatalogItem = ClinicalCatalog::withTrashed()
                ->where('specialty_id', $submission->specialty_id)
                ->where('normalized_name', $normalized)
                ->first();

            if ($existingCatalogItem) {
                // ── Merge/Restore path ────────────────────────────────────────────
                $catalogItem = $existingCatalogItem;
                
                if ($catalogItem->trashed()) {
                    $catalogItem->restore();
                    $path = 'restored';
                } else {
                    $path = 'merged';
                }
            } else {
                // ── Create path: new entry in clinical_catalog ────────────────────
                // Determine the type string from the submission.
                // proposed_type_id is nullable (type stored as raw note for now).
                // We read it from the audit log notes or fall back to 'Treatment'.
                $type = $this->resolveTypeFromSubmission($submission);

                try {
                    $catalogItem = ClinicalCatalog::create([
                        'specialty_id'       => $submission->specialty_id,
                        'item_name'          => $submission->original_name,
                        'normalized_name'    => $normalized,
                        'type'               => $type,
                        'default_fee'        => $submission->proposed_default_fee,
                        'category_id'        => null,
                        'created_by_user_id' => $submission->submitted_by_user_id,
                        'approved_by_user_id' => $admin->id,
                        'approved_at'        => now(),
                    ]);
                    $path = 'created';
                } catch (\Illuminate\Database\QueryException $e) {
                    // Check for Unique Constraint Violation (SQL State 23000)
                    if ($e->getCode() == 23000) {
                        // Race Condition Hit: Retrieve the item created by concurrent process
                        $catalogItem = ClinicalCatalog::withTrashed()
                             ->where('specialty_id', $submission->specialty_id)
                             ->where('normalized_name', $normalized)
                             ->firstOrFail();

                        if ($catalogItem->trashed()) {
                             $catalogItem->restore();
                             $path = 'restored_on_retry';
                        } else {
                             $path = 'merged_on_retry';
                        }
                    } else {
                        throw $e;
                    }
                }
            }

            // ── Step 3: Attach to the submitting doctor ONLY ──────────────────────
            DoctorServiceSetting::updateOrCreate(
                [
                    'user_id'    => $submission->submitted_by_user_id,
                    'catalog_id' => $catalogItem->id,
                ],
                [
                    'custom_price' => $submission->proposed_default_fee,
                    'is_active'    => true,
                ]
            );

            // ── Step 4: Mark submission as approved ───────────────────────────────
            $submission->update([
                'status'              => 'approved',
                'reviewed_by_user_id' => $admin->id,
                'reviewed_at'         => now(),
            ]);

            // ── Step 5: Write audit log ───────────────────────────────────────────
            ServiceAuditLog::record(
                submissionId:      $submission->id,
                action:            'approved',
                performedByUserId: $admin->id,
                notes: "Path: {$path} | Catalog item id: {$catalogItem->id} | "
                     . ($request->filled('notes') ? $request->notes : 'No admin note.')
            );

            return [
                'submission'  => $submission->fresh(),
                'catalog_item' => ServiceNormalizer::normalize($catalogItem, 'global'),
                'path'        => $path,
            ];
        });

        return response()->json([
            'message'      => 'Submission approved successfully.',
            'path'         => $result['path'],  // 'created' or 'merged'
            'catalog_item' => $result['catalog_item'],
            'submission'   => $result['submission'],
        ], 200);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  POST /api/admin/service-submissions/{id}/reject
    // ══════════════════════════════════════════════════════════════════════════

    public function reject(Request $request, int $id)
    {
        $admin = Auth::user();

        $submission = ServiceSubmission::findOrFail($id);

        // Guard: only pending submissions can be rejected
        if (! $submission->isPending()) {
            return response()->json([
                'message' => "Submission is already {$submission->status} and cannot be rejected again.",
            ], 422);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        DB::transaction(function () use ($submission, $admin, $validated) {

            // ── Step 1: Update submission status (clinical_catalog NEVER touched) ──
            $submission->update([
                'status'              => 'rejected',
                'rejection_reason'    => $validated['rejection_reason'],
                'reviewed_by_user_id' => $admin->id,
                'reviewed_at'         => now(),
            ]);

            // ── Step 2: Write audit log ───────────────────────────────────────────
            ServiceAuditLog::record(
                submissionId:      $submission->id,
                action:            'rejected',
                performedByUserId: $admin->id,
                notes: "Reason: {$validated['rejection_reason']}"
            );
        });

        return response()->json([
            'message'    => 'Submission rejected.',
            'submission' => $submission->fresh(['reviewedBy:id,name']),
        ], 200);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Extract the service type string from a submission.
     *
     * Currently, type is stored as a plain string in the audit log notes
     * because proposed_type_id FK points to service_types (a lookup table
     * not yet seeded). We read it back from the first audit log entry.
     * Falls back to 'Treatment' if not determinable.
     *
     * When service_types is seeded in a future step, this can be replaced
     * with: $submission->proposedType->name
     */
    private function resolveTypeFromSubmission(ServiceSubmission $submission): string
    {
        $log = ServiceAuditLog::where('submission_id', $submission->id)
            ->where('action', 'submitted')
            ->first();

        if ($log && $log->notes) {
            // Notes format: "Proposed service: "..." | Type: Treatment | Fee: 500"
            if (preg_match('/Type:\s*(Treatment|Medicine)/', $log->notes, $matches)) {
                return $matches[1];
            }
        }

        return 'Treatment'; // Safe default
    }
}
