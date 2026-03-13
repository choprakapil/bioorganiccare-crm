<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

use App\Support\Context\TenantContext;

class AuditLogController extends Controller
{
    protected TenantContext $context;

    public function __construct(TenantContext $context)
    {
        $this->context = $context;
    }

    /**
     * Display a listing of the audit logs with filters and pagination.
     */
    public function index(Request $request)
    {
        $owner = $this->context->getClinicOwner();

        if (!$owner) {
            return response()->json(['message' => 'Clinic context required.'], 422);
        }

        $doctorId = $owner->id;
        
        $query = AuditLog::whereHas('user', function($q) use ($doctorId) {
            $q->where('doctor_id', $doctorId)
              ->orWhere('id', $doctorId);
        });

        // 1. Date Filter
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date . ' 00:00:00');
        }
        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        // 2. Action Filter
        if ($request->has('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        // 3. User Filter
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // 4. Cursor Pagination (Enterprise Scaling O(1) Deep Paging)
        $logs = $query->with('user:id,name,role')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->cursorPaginate($request->query('per_page', 50));

        return response()->json($logs);
    }
}
