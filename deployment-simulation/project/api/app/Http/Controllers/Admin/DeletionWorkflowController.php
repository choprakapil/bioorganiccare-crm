<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeletionRequest;
use App\Services\DeletionWorkflowManager;
use Illuminate\Http\Request;

class DeletionWorkflowController extends Controller
{
    public function __construct(private readonly DeletionWorkflowManager $workflow) {}

    // ─────────────────────────────────────────────────────────────────────────
    // POST /admin/delete/{entity}/{id}/request-cascade
    // ─────────────────────────────────────────────────────────────────────────

    public function requestCascade(Request $request, string $entity, int $id)
    {
        $userId = $request->user()->id;

        try {
            $deletionRequest = $this->workflow->createRequest($entity, $id, $userId);
            return response()->json($deletionRequest, 201);
        } catch (\InvalidArgumentException $e) {
            if ($e->getMessage() === 'A pending deletion request already exists for this entity.') {
                return response()->json(['error' => $e->getMessage()], 409);
            }
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['error' => "Entity [{$entity}] #{$id} not found."], 404);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /admin/delete/requests
    // ─────────────────────────────────────────────────────────────────────────

    public function listRequests(Request $request)
    {
        $status = $request->query('status');

        $query = DeletionRequest::with(['requester', 'approver'])
            ->orderByDesc('created_at');

        if ($status && DeletionRequest::isValidStatus($status)) {
            $query->where('status', $status);
        }

        return response()->json($query->paginate(20));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /admin/delete/requests/{id}/drift-check
    // ─────────────────────────────────────────────────────────────────────────

    public function driftCheck(Request $request, int $id)
    {
        try {
            $result = $this->workflow->detectDrift($id);
            return response()->json($result);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['error' => "Deletion request #{$id} not found."], 404);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /admin/delete/requests/{id}/approve
    // ─────────────────────────────────────────────────────────────────────────

    public function approve(Request $request, int $id)
    {
        $approverId = $request->user()->id;

        try {
            $deletionRequest = $this->workflow->approveRequest($id, $approverId);
            return response()->json($deletionRequest);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['error' => "Deletion request #{$id} not found."], 404);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /admin/delete/requests/{id}/reject
    // ─────────────────────────────────────────────────────────────────────────

    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:2000',
        ]);

        $approverId = $request->user()->id;

        try {
            $deletionRequest = $this->workflow->rejectRequest($id, $approverId, $validated['reason']);
            return response()->json($deletionRequest);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['error' => "Deletion request #{$id} not found."], 404);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /admin/delete/requests/{id}/execute
    // ─────────────────────────────────────────────────────────────────────────

    public function execute(Request $request, int $id)
    {
        $executorId = $request->user()->id;

        try {
            $result = $this->workflow->executeRequest($id, $executorId);
            return response()->json($result);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['error' => "Deletion request #{$id} not found."], 404);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
