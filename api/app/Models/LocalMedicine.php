<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocalMedicine extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (!empty($model->item_name ?? $model->name)) {
                $source = $model->item_name ?? $model->name;
                $normalized = strtolower(trim($source));
                $normalized = preg_replace('/\s+/', ' ', $normalized);
                $model->normalized_name = $normalized;
            }
        });
    }
}
