<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorServiceSetting extends Model
{
    protected $fillable = ['user_id', 'catalog_id', 'custom_price', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'custom_price' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function catalog()
    {
        return $this->belongsTo(ClinicalCatalog::class, 'catalog_id');
    }
}
