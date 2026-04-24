<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasTenantContext()
            && $this->canViewProducts($user);
    }

    public function view(User $user, Product $product): bool
    {
        return $this->hasTenantContext()
            && $this->canViewProducts($user)
            && (int) $user->tenant_id === (int) $product->tenant_id;
    }

    public function create(User $user): bool
    {
        return $this->hasTenantContext()
            && ($user->can('product.create') || $user->can('products.manage'));
    }

    public function update(User $user, Product $product): bool
    {
        return $this->hasTenantContext()
            && ($user->can('product.update') || $user->can('products.manage'))
            && (int) $user->tenant_id === (int) $product->tenant_id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->hasTenantContext()
            && ($user->can('product.delete') || $user->can('products.manage'))
            && (int) $user->tenant_id === (int) $product->tenant_id;
    }

    private function canViewProducts(User $user): bool
    {
        return $user->can('product.view') || $user->can('products.view');
    }

    private function hasTenantContext(): bool
    {
        return app(TenantContext::class)->id() !== null;
    }
}
