<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Traits\ProtectedDeletion;

class PharmacyCategory extends Model
{
    use ProtectedDeletion;
    protected $fillable = ['specialty_id', 'name', 'sort_order'];

    public function specialty()
    {
        return $this->belongsTo(\App\Models\Admin\Specialty::class);
    }
    
    public function medicines()
    {
        return $this->hasMany(MasterMedicine::class, 'pharmacy_category_id');
    }
}
