<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceType extends Model
{
    protected $fillable = [
        'name',
        'normalized_name',
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(ServiceSubmission::class, 'proposed_type_id');
    }
}
