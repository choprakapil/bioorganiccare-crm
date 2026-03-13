<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecycleBinController extends Controller
{
    public function index()
    {
        // Only super admin can access
        if (Auth::user()->role !== 'super_admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $deletedDoctors = User::onlyTrashed()
            ->where('role', 'doctor')
            ->with(['specialty', 'plan'])
            ->orderBy('deleted_at', 'desc')
            ->get();

        return response()->json($deletedDoctors);
    }

    public function restore($id)
    {
        if (Auth::user()->role !== 'super_admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            return response()->json(app(\App\Services\DeleteManager::class)->restore('doctor', $id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function forceDelete($id)
    {
        if (Auth::user()->role !== 'super_admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $result = app(\App\Services\DeleteManager::class)->forceDelete('doctor', $id);
            $code   = $result['status'] === 'blocked' ? 409 : 200;
            return response()->json($result, $code);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
