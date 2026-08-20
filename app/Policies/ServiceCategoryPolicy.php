<?php

namespace App\Policies;

use App\Models\ServiceCategory;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

class ServiceCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasTenantContext() && $user->can('service-category.view');
    }

    public function view(User $user, ServiceCategory $serviceCategory): bool
    {
        return $this->hasTenantContext()
            && $user->can('service-category.view')
            && (int) $user->tenant_id === (int) $serviceCategory->tenant_id;
    }

    public function create(User $user): bool
    {
        return $this->hasTenantContext() && $user->can('service-category.create');
    }

    public function update(User $user, ServiceCategory $serviceCategory): bool
    {
        return $this->hasTenantContext()
            && $user->can('service-category.update')
            && (int) $user->tenant_id === (int) $serviceCategory->tenant_id;
    }

    public function delete(User $user, ServiceCategory $serviceCategory): bool
    {
        return $this->hasTenantContext()
            && $user->can('service-category.delete')
            && (int) $user->tenant_id === (int) $serviceCategory->tenant_id;
    }

    private function hasTenantContext(): bool
    {
        return app(TenantContext::class)->id() !== null;
    }
}
