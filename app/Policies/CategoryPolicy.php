<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasTenantContext() && $user->can('category.view');
    }

    public function view(User $user, Category $category): bool
    {
        return $this->hasTenantContext()
            && $user->can('category.view')
            && (int) $user->tenant_id === (int) $category->tenant_id;
    }

    public function create(User $user): bool
    {
        return $this->hasTenantContext() && $user->can('category.create');
    }

    public function update(User $user, Category $category): bool
    {
        return $this->hasTenantContext()
            && $user->can('category.update')
            && (int) $user->tenant_id === (int) $category->tenant_id;
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->hasTenantContext()
            && $user->can('category.delete')
            && (int) $user->tenant_id === (int) $category->tenant_id;
    }

    private function hasTenantContext(): bool
    {
        return app(TenantContext::class)->id() !== null;
    }
}
