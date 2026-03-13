<?php

namespace App\Services;

class GovernanceApprovalPolicy
{
    public function __construct(
        private readonly SystemSettingService $settings
    ) {}

    public function requiresDualApproval(): bool
    {
        // Check system_settings.dual_admin_approval_enabled (default: true)
        $val = $this->settings->get('dual_admin_approval_enabled', '1');
        return $val === '1' || $val === 'true' || $val === true;
    }

    public function canApprove(int $requesterId, int $approverId): bool
    {
        if ($this->requiresDualApproval()) {
            return $requesterId !== $approverId;
        }

        return true;
    }
}
