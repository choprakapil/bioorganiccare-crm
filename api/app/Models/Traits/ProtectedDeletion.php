<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;

trait ProtectedDeletion
{
    /**
     * Boot the trait.
     * Hooks into the deleting event to prevent direct Eloquent deletion.
     */
    protected static function bootProtectedDeletion(): void
    {
        static::deleting(function (Model $model) {
            if (!app()->has('deletion_context') || app('deletion_context') !== true) {
                throw new \RuntimeException(
                    "Direct model deletion is forbidden for [" . get_class($model) . "]. " .
                    "Destructive operations must use the centralized DeleteManager."
                );
            }
        });

        if (method_exists(static::class, 'forceDeleting')) {
            static::forceDeleting(function (Model $model) {
                if (!app()->has('deletion_context') || app('deletion_context') !== true) {
                    throw new \RuntimeException(
                        "Direct model force-deletion is forbidden for [" . get_class($model) . "]. " .
                        "Purge operations must use the centralized DeleteManager."
                    );
                }
            });
        }
    }
}
