<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DeleteManager;
use Illuminate\Http\Request;

class DeleteManagerController extends Controller
{
    public function __construct(private readonly DeleteManager $manager) {}

    // ─────────────────────────────────────────────────────────────────────────
    // GET /admin/delete/{entity}/{id}/summary
    // ─────────────────────────────────────────────────────────────────────────

    public function summary(string $entity, int $id)
    {
        try {
            return response()->json($this->manager->dependencySummary($entity, $id));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['error' => "Entity [{$entity}] #{$id} not found."], 404);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /admin/delete/{entity}/{id}/archive
    // ─────────────────────────────────────────────────────────────────────────

    public function archive(string $entity, int $id)
    {
        try {
            return response()->json($this->manager->archive($entity, $id));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /admin/delete/{entity}/{id}/restore
    // ─────────────────────────────────────────────────────────────────────────

    public function restore(string $entity, int $id)
    {
        try {
            return response()->json($this->manager->restore($entity, $id));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE /admin/delete/{entity}/{id}/force
    // ─────────────────────────────────────────────────────────────────────────

    public function forceDelete(string $entity, int $id)
    {
        try {
            $result = $this->manager->forceDelete($entity, $id);
            $code   = $result['status'] === 'blocked' ? 409 : 200;
            return response()->json($result, $code);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE /admin/delete/{entity}/{id}/force-cascade
    // ─────────────────────────────────────────────────────────────────────────

    public function forceDeleteCascade(string $entity, int $id)
    {
        try {
            $result = $this->manager->forceDeleteCascade($entity, $id);
            $code   = $result['status'] === 'blocked' ? 409 : 200;
            return response()->json($result, $code);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /admin/delete/{entity}/{id}/cascade-preview
    // ─────────────────────────────────────────────────────────────────────────

    public function cascadePreview(string $entity, int $id)
    {
        try {
            return response()->json($this->manager->cascadePreview($entity, $id));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['error' => "Entity [{$entity}] #{$id} not found."], 404);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE /admin/delete/{entity}/bulk
    // ─────────────────────────────────────────────────────────────────────────

    public function bulk(Request $request, string $entity)
    {
        $validated = $request->validate([
            'ids'     => 'required|array|min:1',
            'ids.*'   => 'required|integer|min:1',
            'cascade' => 'sometimes|boolean',
        ]);

        $cascade = $validated['cascade'] ?? false;

        if ($cascade && $entity !== 'specialty') {
            return response()->json([
                'error' => "Cascade bulk delete is only allowed for [specialty], not [{$entity}].",
            ], 422);
        }

        try {
            return response()->json(
                $this->manager->bulkForceDelete($entity, $validated['ids'], $cascade)
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
