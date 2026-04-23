<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;
use App\Support\Tenancy\TenantContext;

class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasTenantContext()
            && ($user->can('vehicle.view') || $user->can('vehicles.view'));
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        return $this->hasTenantContext()
            && ($user->can('vehicle.view') || $user->can('vehicles.view'))
            && (int) $user->tenant_id === (int) $vehicle->tenant_id;
    }

    public function create(User $user): bool
    {
        return $this->hasTenantContext()
            && ($user->can('vehicle.create') || $user->can('vehicles.manage'));
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $this->hasTenantContext()
            && ($user->can('vehicle.update') || $user->can('vehicles.manage'))
            && (int) $user->tenant_id === (int) $vehicle->tenant_id;
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $this->hasTenantContext()
            && ($user->can('vehicle.delete') || $user->can('vehicles.manage'))
            && (int) $user->tenant_id === (int) $vehicle->tenant_id;
    }

    private function hasTenantContext(): bool
    {
        return app(TenantContext::class)->id() !== null;
    }
}
