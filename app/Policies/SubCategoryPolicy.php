<?php

namespace App\Policies;

use App\Models\SubCategory;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

class SubCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasTenantContext() && $user->can('subcategory.view');
    }

    public function view(User $user, SubCategory $subCategory): bool
    {
        return $this->hasTenantContext()
            && $user->can('subcategory.view')
            && (int) $user->tenant_id === (int) $subCategory->tenant_id;
    }

    public function create(User $user): bool
    {
        return $this->hasTenantContext() && $user->can('subcategory.create');
    }

    public function update(User $user, SubCategory $subCategory): bool
    {
        return $this->hasTenantContext()
            && $user->can('subcategory.update')
            && (int) $user->tenant_id === (int) $subCategory->tenant_id;
    }

    public function delete(User $user, SubCategory $subCategory): bool
    {
        return $this->hasTenantContext()
            && $user->can('subcategory.delete')
            && (int) $user->tenant_id === (int) $subCategory->tenant_id;
    }

    private function hasTenantContext(): bool
    {
        return app(TenantContext::class)->id() !== null;
    }
}
