<?php

namespace App\Policies;

use App\Models\Discount;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

class DiscountPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasTenantContext() && $user->can('discount.manage');
    }

    public function view(User $user, Discount $discount): bool
    {
        return $this->hasTenantContext()
            && $user->can('discount.manage')
            && (int) $user->tenant_id === (int) $discount->tenant_id;
    }

    public function create(User $user): bool
    {
        return $this->hasTenantContext() && $user->can('discount.manage');
    }

    public function update(User $user, Discount $discount): bool
    {
        return $this->hasTenantContext()
            && $user->can('discount.manage')
            && (int) $user->tenant_id === (int) $discount->tenant_id;
    }

    public function delete(User $user, Discount $discount): bool
    {
        return $this->hasTenantContext()
            && $user->can('discount.manage')
            && (int) $user->tenant_id === (int) $discount->tenant_id;
    }

    private function hasTenantContext(): bool
    {
        return app(TenantContext::class)->id() !== null;
    }
}
