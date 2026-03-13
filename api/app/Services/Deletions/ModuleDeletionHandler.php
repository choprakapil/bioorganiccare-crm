<?php

namespace App\Services\Deletions;

use App\Models\Module;

class ModuleDeletionHandler implements DeletionHandlerInterface
{
    public function summary(int $id): array
    {
        $mod = Module::findOrFail($id);
        $specs = $mod->specialties()->count();
        $plans = $mod->plans()->count();
        return [
            'entity'            => 'module',
            'id'                => $id,
            'entity_name'       => $mod->name,
            'specialties'       => $specs,
            'plans'             => $plans,
            'force_delete_safe' => ($specs === 0 && $plans === 0),
            'cascade_deletable' => false,
        ];
    }
}
