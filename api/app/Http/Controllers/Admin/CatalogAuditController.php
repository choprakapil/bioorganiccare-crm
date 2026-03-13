<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogAuditLog;
use Illuminate\Http\Request;

use App\Support\Context\TenantContext;

class CatalogAuditController extends Controller
{
    private TenantContext $context;

    public function __construct(TenantContext $context)
    {
        $this->context = $context;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = CatalogAuditLog::query()
            ->orderBy('id', 'desc');

        if ($user->role === 'doctor') {
            $query->where('performed_by_user_id', $user->id);
        } elseif ($user->role === 'staff' && $this->context->getSpecialtyId()) {
            // "Specialty Admin" concept, filtering strictly based on specialty
            // Wait, we don't have specialty_id directly on the audit log.
            // But we don't know the schema entirely for User, let's assume they only see what they did or we just join if needed.
            // Simplified approach from prompt: "Specialty Admin -> filter by specialty"
            // Let's adapt if needed. Let's filter by the user's branch normally, but here let's stick to the prompt.
            // To filter by specialty, we might need to join the respective catalog tables.
            $query->where(function($q) use ($user) {
                $q->where('performed_by_user_id', $user->id)
                  ->orWhere(function($qSub) use ($user) {
                      $qSub->where('entity_type', 'clinical')
                           ->whereIn('entity_id', \App\Models\ClinicalCatalog::withTrashed()->where('specialty_id', $this->context->getSpecialtyId())->pluck('id'));
                  })
                  ->orWhere(function($qSub) use ($user) {
                      $qSub->where('entity_type', 'pharmacy')
                           ->whereIn('entity_id', \App\Models\MasterMedicine::withTrashed()
                               ->whereHas('pharmacy_category', function($qCat) {
                                   $qCat->where('specialty_id', $this->context->getSpecialtyId());
                               })->pluck('id')
                           );
                  });
            });
        }

        return response()->json($query->paginate(50));
    }
}
