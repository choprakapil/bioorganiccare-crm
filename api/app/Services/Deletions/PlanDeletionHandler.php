<?php

namespace App\Services\Deletions;

use App\Models\SubscriptionPlan;
use App\Models\User;

class PlanDeletionHandler implements DeletionHandlerInterface
{
    public function summary(int $id): array
    {
        $plan  = SubscriptionPlan::findOrFail($id);
        $users = User::where('plan_id', $id)->where('role', 'doctor')->count();
        $totalPlansInSpecialty = SubscriptionPlan::where('specialty_id', $plan->specialty_id)->count();
        return [
            'entity'            => 'plan',
            'id'                => $id,
            'entity_name'       => $plan->name,
            'active_doctors'    => $users,
            'specialty_plans'   => $totalPlansInSpecialty,
            'force_delete_safe' => ($users === 0 && $totalPlansInSpecialty > 1),
            'cascade_deletable' => false,
        ];
    }
}
