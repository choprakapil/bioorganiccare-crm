<?php

namespace App\Services;

use App\Models\DeletionRequest;
use App\Models\User;
use App\Models\Specialty;
use App\Models\ClinicalCatalog;
use App\Models\MasterMedicine;
use Illuminate\Support\Facades\DB;

class DeletionWorkflowManager
{
    public function __construct(
        private readonly DeleteManager $deleteManager,
        private readonly GovernanceApprovalPolicy $approvalPolicy
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // ENTITY EXISTENCE CHECK
    // ─────────────────────────────────────────────────────────────────────────

    private function assertEntityExists(string $entity, int $id): void
    {
        $exists = match ($entity) {
            'specialty'         => Specialty::withTrashed()->where('id', $id)->exists(),
            'service'           => ClinicalCatalog::withTrashed()->where('id', $id)->exists(),
            'medicine'          => MasterMedicine::withTrashed()->where('id', $id)->exists(),
            'doctor'            => User::withTrashed()->where('id', $id)->where('role', 'doctor')->exists(),
            'staff'             => User::withTrashed()->where('id', $id)->where('role', 'staff')->exists(),
            'plan'              => SubscriptionPlan::where('id', $id)->exists(),
            'category'          => ClinicalServiceCategory::withTrashed()->where('id', $id)->exists(),
            'pharmacy_category' => PharmacyCategory::where('id', $id)->exists(),
            default             => throw new \InvalidArgumentException("Unknown entity: [{$entity}]"),
        };

        if (!$exists) {
            throw new \RuntimeException("Entity [{$entity}] #{$id} does not exist (including trashed).");
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. CREATE REQUEST
    // ─────────────────────────────────────────────────────────────────────────

    public function createRequest(string $entity, int $id, int $userId): DeletionRequest
    {
        return DB::transaction(function () use ($entity, $id, $userId) {
            $this->assertEntityExists($entity, $id);

            // Prevent duplicate pending requests
            if (DeletionRequest::where('entity_type', $entity)
                ->where('entity_id', $id)
                ->where('status', 'pending')
                ->exists()) {
                throw new \InvalidArgumentException("A pending deletion request already exists for this entity.");
            }

            if (!User::where('id', $userId)->exists()) {
                throw new \InvalidArgumentException("User ID [{$userId}] does not exist.");
            }

            $preview = $this->deleteManager->cascadePreview($entity, $id);

            return DeletionRequest::create([
                'entity_type'          => $entity,
                'entity_id'            => $id,
                'requested_by'         => $userId,
                'approved_by'          => null,
                'status'               => 'pending',
                'cascade_preview_json' => $preview,
                'reason'               => null,
                'approved_at'          => null,
                'executed_at'          => null,
            ]);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. APPROVE REQUEST
    // ─────────────────────────────────────────────────────────────────────────

    public function approveRequest(int $requestId, int $approverId): DeletionRequest
    {
        $request  = DeletionRequest::findOrFail($requestId);
        $approver = User::findOrFail($approverId);

        if (!$request->isPending()) {
            throw new \RuntimeException(
                "Cannot approve: request #{$requestId} is in status [{$request->status}], not [pending]."
            );
        }

        if (!$this->approvalPolicy->canApprove($request->requested_by, $approverId)) {
            throw new \RuntimeException(
                "Dual approval is enabled. Approver cannot be same as requester."
            );
        }

        $this->assertEntityExists($request->entity_type, $request->entity_id);

        $request->update([
            'status'      => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        return $request->fresh();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. REJECT REQUEST
    // ─────────────────────────────────────────────────────────────────────────

    public function rejectRequest(int $requestId, int $approverId, string $reason): DeletionRequest
    {
        $request = DeletionRequest::findOrFail($requestId);

        if (!$request->isPending() && !$request->isApproved()) {
            throw new \RuntimeException(
                "Cannot reject: request #{$requestId} is in status [{$request->status}]. Only [pending] or [approved] can be rejected."
            );
        }

        $request->update([
            'status'      => 'rejected',
            'approved_by' => $approverId,
            'reason'      => $reason,
        ]);

        return $request->fresh();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. EXECUTE REQUEST — SNAPSHOT INTEGRITY ENFORCED
    // ─────────────────────────────────────────────────────────────────────────

    public function executeRequest(int $requestId, int $executorId): array
    {
        $request  = DeletionRequest::findOrFail($requestId);
        $executor = User::findOrFail($executorId);

        if (!$request->isApproved()) {
            throw new \RuntimeException(
                "Cannot execute: request #{$requestId} is in status [{$request->status}], not [approved]."
            );
        }

        $entity = $request->entity_type;
        $id     = (int) $request->entity_id;

        $this->assertEntityExists($entity, $id);

        // ── Snapshot integrity check (hard guard — runs even if frontend skipped) ──
        $livePreview   = $this->deleteManager->cascadePreview($entity, $id);
        $storedPreview = $request->cascade_preview_json;

        $storedCounts = $storedPreview['will_delete'] ?? [];
        $liveCounts   = $livePreview['will_delete']   ?? [];

        ksort($storedCounts);
        ksort($liveCounts);

        if ($storedCounts !== $liveCounts) {
            $diff = [];
            foreach ($liveCounts as $key => $liveCount) {
                $storedCount = $storedCounts[$key] ?? 'MISSING';
                if ($liveCount !== $storedCount) {
                    $diff[$key] = ['stored' => $storedCount, 'live' => $liveCount];
                }
            }
            foreach ($storedCounts as $key => $storedCount) {
                if (!isset($liveCounts[$key])) {
                    $diff[$key] = ['stored' => $storedCount, 'live' => 'MISSING'];
                }
            }

            throw new \RuntimeException(
                "SNAPSHOT MISMATCH: Live data changed since approval. Differences: " . json_encode($diff) .
                " — Re-request and re-approve with current snapshot."
            );
        }

        // ── Execute in transaction ───────────────────────────────────────────
        $result = DB::transaction(function () use ($entity, $id, $request) {
            $deleteResult = $this->deleteManager->forceDeleteCascade($entity, $id);

            $request->update([
                'status'      => 'executed',
                'executed_at' => now(),
            ]);

            return $deleteResult;
        });

        return array_merge($result, [
            'request_id'  => $requestId,
            'executed_by' => $executorId,
            'executed_at' => now()->toIso8601String(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. DETECT DRIFT — READ ONLY, NO SIDE EFFECTS
    //    Compare live cascadePreview vs stored cascade_preview_json.
    //    No mutation. No cache bump. No delete. No transaction.
    // ─────────────────────────────────────────────────────────────────────────

    public function detectDrift(int $requestId): array
    {
        $request = DeletionRequest::findOrFail($requestId);

        $storedPreview = $request->cascade_preview_json;

        if (empty($storedPreview) || !isset($storedPreview['will_delete'])) {
            throw new \RuntimeException(
                "Request #{$requestId} has no cascade_preview_json snapshot to compare against."
            );
        }

        $entity = $request->entity_type;
        $id     = (int) $request->entity_id;

        $livePreview = $this->deleteManager->cascadePreview($entity, $id);

        $storedCounts    = $storedPreview['will_delete'] ?? [];
        $liveCounts      = $livePreview['will_delete']   ?? [];
        $storedTotalRows = $storedPreview['total_rows']  ?? array_sum($storedCounts);
        $liveTotalRows   = $livePreview['total_rows']    ?? array_sum($liveCounts);

        ksort($storedCounts);
        ksort($liveCounts);

        if ($storedCounts === $liveCounts) {
            return [
                'drift'        => false,
                'differences'  => [],
                'live_total'   => $liveTotalRows,
                'stored_total' => $storedTotalRows,
            ];
        }

        $differences = [];

        foreach ($liveCounts as $key => $liveCount) {
            $storedCount = $storedCounts[$key] ?? 'MISSING';
            if ($liveCount !== $storedCount) {
                $differences[$key] = ['stored' => $storedCount, 'live' => $liveCount];
            }
        }

        foreach ($storedCounts as $key => $storedCount) {
            if (!isset($liveCounts[$key])) {
                $differences[$key] = ['stored' => $storedCount, 'live' => 'MISSING'];
            }
        }

        return [
            'drift'        => true,
            'differences'  => $differences,
            'live_total'   => $liveTotalRows,
            'stored_total' => $storedTotalRows,
        ];
    }
}
