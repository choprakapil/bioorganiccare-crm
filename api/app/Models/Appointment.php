<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Traits\ProtectedDeletion;

class Appointment extends Model
{
    use ProtectedDeletion;
    use \App\Traits\BelongsToTenancy;

    protected $fillable = [
        'patient_id',
        'appointment_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'appointment_date' => 'datetime'
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
