<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Specialty;
use App\Models\SubscriptionPlan;

class Module extends Model
{
    protected $fillable = ['key', 'name', 'description', 'is_active'];

    public function specialties()
    {
        return $this->belongsToMany(Specialty::class, 'specialty_module')
            ->withPivot('enabled')
            ->withTimestamps();
    }

    public function plans()
    {
        return $this->belongsToMany(SubscriptionPlan::class, 'plan_module')
            ->withPivot('enabled')
            ->withTimestamps();
    }
}
