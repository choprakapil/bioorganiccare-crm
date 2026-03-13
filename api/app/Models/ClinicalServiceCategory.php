<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Traits\ProtectedDeletion;

class ClinicalServiceCategory extends Model
{
    use SoftDeletes, ProtectedDeletion;
    protected $fillable = ['specialty_id', 'name', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function items()
    {
        return $this->hasMany(ClinicalCatalog::class, 'category_id');
    }
}
