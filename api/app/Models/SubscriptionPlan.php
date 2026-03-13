<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Traits\ProtectedDeletion;

class SubscriptionPlan extends Model
{
    use ProtectedDeletion;
    protected $fillable = [
        'specialty_id',
        'name',
        'tier',
        'price',
        'max_staff',
        'max_patients',
        'max_appointments_monthly',
        'is_active',
    ];

    public function modules()
    {
        return $this->belongsToMany(
            \App\Models\Module::class,
            'plan_module',
            'plan_id',
            'module_id'
        )->withPivot('enabled');
    }

    public function specialty()
    {
        return $this->belongsTo(\App\Models\Specialty::class);
    }

    public function isUnlimited(string $field): bool
    {
        return $this->{$field} === -1;
    }
}
