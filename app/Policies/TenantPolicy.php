<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tenant.approvals.manage');
    }

    public function updateStatus(User $user, Tenant $tenant): bool
    {
        return $user->can('tenant.approvals.manage');
    }

    public function impersonate(User $user, Tenant $tenant): bool
    {
        return $user->can('tenant.impersonate');
    }

    public function manageSettings(User $user, Tenant $tenant): bool
    {
        return ! $user->isSuperAdmin()
            && $user->can('settings.manage')
            && (int) $user->tenant_id === (int) $tenant->id;
    }
}
