<?php

namespace App\Services\Deletions;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;

class DoctorDeletionHandler implements DeletionHandlerInterface
{
    public function summary(int $id): array
    {
        $doctor   = User::withTrashed()->where('role', 'doctor')->findOrFail($id);
        $patients = Patient::withTrashed()->where('doctor_id', $id)->count();
        $invoices = DB::table('invoices')->where('doctor_id', $id)->count();
        return [
            'entity'            => 'doctor',
            'id'                => $id,
            'entity_name'       => $doctor->name,
            'is_archived'       => (bool) $doctor->deleted_at,
            'patients'          => $patients,
            'invoices'          => $invoices,
            'force_delete_safe' => ($patients === 0 && $invoices === 0),
            'cascade_deletable' => false,
        ];
    }
}
