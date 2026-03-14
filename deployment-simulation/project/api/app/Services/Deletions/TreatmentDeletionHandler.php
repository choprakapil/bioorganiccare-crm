<?php

namespace App\Services\Deletions;

use App\Models\Treatment;
use Illuminate\Support\Facades\DB;

class TreatmentDeletionHandler implements DeletionHandlerInterface
{
    public function summary(int $id): array
    {
        $treatment = Treatment::withTrashed()->findOrFail($id);
        $invoices = DB::table('invoice_items')->where('treatment_id', $id)->count();
        return [
            'entity'            => 'treatment',
            'id'                => $id,
            'entity_name'       => "Treatment #" . $id,
            'is_archived'       => (bool) $treatment->deleted_at,
            'invoices'          => $invoices,
            'force_delete_safe' => ($invoices === 0),
            'cascade_deletable' => false,
        ];
    }
}
