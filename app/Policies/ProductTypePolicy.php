<?php

namespace App\Policies;

use App\Models\ProductType;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

class ProductTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasTenantContext() && $user->can('product-type.view');
    }

    public function view(User $user, ProductType $productType): bool
    {
        return $this->hasTenantContext()
            && $user->can('product-type.view')
            && (int) $user->tenant_id === (int) $productType->tenant_id;
    }

    public function create(User $user): bool
    {
        return $this->hasTenantContext() && $user->can('product-type.create');
    }

    public function update(User $user, ProductType $productType): bool
    {
        return $this->hasTenantContext()
            && $user->can('product-type.update')
            && (int) $user->tenant_id === (int) $productType->tenant_id;
    }

    public function delete(User $user, ProductType $productType): bool
    {
        return $this->hasTenantContext()
            && $user->can('product-type.delete')
            && (int) $user->tenant_id === (int) $productType->tenant_id;
    }

    private function hasTenantContext(): bool
    {
        return app(TenantContext::class)->id() !== null;
    }
}
