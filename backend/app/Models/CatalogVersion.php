<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogVersion extends Model
{
    protected $table = 'catalog_versions';

    /**
     * The table does not have an updated_at column.
     * Prevents SQL errors during version snapshot creation.
     */
    public $timestamps = false;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'version_number',
        'changed_by_user_id',
        'old_payload',
        'new_payload',
        'created_at',
    ];

    protected $casts = [
        'old_payload' => 'array',
        'new_payload' => 'array',
        'created_at' => 'datetime',
    ];
}
