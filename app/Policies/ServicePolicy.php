<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasTenantContext() && $user->can('service.view');
    }

    public function view(User $user, Service $service): bool
    {
        return $this->hasTenantContext()
            && $user->can('service.view')
            && (int) $user->tenant_id === (int) $service->tenant_id;
    }

    public function create(User $user): bool
    {
        return $this->hasTenantContext() && $user->can('service.create');
    }

    public function update(User $user, Service $service): bool
    {
        return $this->hasTenantContext()
            && $user->can('service.update')
            && (int) $user->tenant_id === (int) $service->tenant_id;
    }

    public function delete(User $user, Service $service): bool
    {
        return $this->hasTenantContext()
            && $user->can('service.delete')
            && (int) $user->tenant_id === (int) $service->tenant_id;
    }

    private function hasTenantContext(): bool
    {
        return app(TenantContext::class)->id() !== null;
    }
}
