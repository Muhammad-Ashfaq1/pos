<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasTenantContext()
            && ($user->can('customer.view') || $user->can('customers.view'));
    }

    public function view(User $user, Customer $customer): bool
    {
        return $this->hasTenantContext()
            && ($user->can('customer.view') || $user->can('customers.view'))
            && (int) $user->tenant_id === (int) $customer->tenant_id;
    }

    public function create(User $user): bool
    {
        return $this->hasTenantContext()
            && ($user->can('customer.create') || $user->can('customers.manage'));
    }

    public function update(User $user, Customer $customer): bool
    {
        if ($this->employeeIsReadOnly($user)) {
            return false;
        }

        return $this->hasTenantContext()
            && ($user->can('customer.update') || $user->can('customers.manage'))
            && (int) $user->tenant_id === (int) $customer->tenant_id;
    }

    public function delete(User $user, Customer $customer): bool
    {
        if ($this->employeeIsReadOnly($user)) {
            return false;
        }

        return $this->hasTenantContext()
            && ($user->can('customer.delete') || $user->can('customers.manage'))
            && (int) $user->tenant_id === (int) $customer->tenant_id;
    }

    private function employeeIsReadOnly(User $user): bool
    {
        return $user->isEmployee()
            && ! $user->isTenantAdmin()
            && ! $user->isManager()
            && $user->role !== User::ADMIN;
    }

    private function hasTenantContext(): bool
    {
        return app(TenantContext::class)->id() !== null;
    }
}
