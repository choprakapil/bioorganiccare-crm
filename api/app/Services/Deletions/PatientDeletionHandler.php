<?php

namespace App\Services\Deletions;

use App\Models\Patient;
use App\Models\Treatment;
use Illuminate\Support\Facades\DB;

class PatientDeletionHandler implements DeletionHandlerInterface
{
    public function summary(int $id): array
    {
        $patient    = Patient::withTrashed()->findOrFail($id);
        $treatments = Treatment::where('patient_id', $id)->count();
        $invoices   = DB::table('invoices')->where('patient_id', $id)->count();
        return [
            'entity'            => 'patient',
            'id'                => $id,
            'entity_name'       => $patient->name,
            'is_archived'       => (bool) $patient->deleted_at,
            'treatments'        => $treatments,
            'invoices'          => $invoices,
            'force_delete_safe' => ($treatments === 0 && $invoices === 0),
            'cascade_deletable' => false,
        ];
    }
}
