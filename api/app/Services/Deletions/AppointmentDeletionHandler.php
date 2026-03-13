<?php

namespace App\Services\Deletions;

use App\Models\Appointment;

class AppointmentDeletionHandler implements DeletionHandlerInterface
{
    public function summary(int $id): array
    {
        $app = Appointment::withTrashed()->findOrFail($id);
        return [
            'entity'            => 'appointment',
            'id'                => $id,
            'entity_name'       => "Appointment on " . $app->appointment_date,
            'is_archived'       => (bool) $app->deleted_at,
            'force_delete_safe' => true,
            'cascade_deletable' => false,
        ];
    }
}
