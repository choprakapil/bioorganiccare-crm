<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait BelongsToTenancy
{
    /**
     * Boot the trait to auto-assign doctor_id during creation.
     */
    protected static function bootBelongsToTenancy()
    {
        static::creating(function ($model) {
            if (Auth::check() && is_null($model->doctor_id)) {
                $user = Auth::user();
                $model->doctor_id = ($user->role === 'staff') ? $user->doctor_id : $user->id;
            }
        });
    }
}
