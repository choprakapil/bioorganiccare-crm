<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogAuditLog extends Model
{
    protected $table = 'catalog_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'action',
        'performed_by_user_id',
        'metadata',
        'ip_address',
        'user_agent',
        'created_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime'
    ];

    public function update(array $attributes = [], array $options = [])
    {
        throw new \Exception('Audit logs are immutable');
    }

    public function delete()
    {
        throw new \Exception('Audit logs cannot be deleted');
    }
}
