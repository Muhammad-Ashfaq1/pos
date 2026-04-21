<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;
use App\Models\User;

class TenantContext
{
    public function current(): ?Tenant
    {
        if (function_exists('tenant') && tenant()) {
            return tenant();
        }

        /** @var User|null $user */
        $user = auth()->user();

        if (! $user?->tenant_id) {
            return null;
        }

        $user->loadMissing('tenant');

        return $user->tenant;
    }

    public function id(): ?int
    {
        return $this->current()?->getKey();
    }

    public function initialize(Tenant|int $tenant): Tenant
    {
        if (! $tenant instanceof Tenant) {
            $tenant = Tenant::query()->findOrFail($tenant);
        }

        tenancy()->initialize($tenant);

        return $tenant;
    }

    public function fromUser(?User $user): ?Tenant
    {
        if (! $user?->tenant_id) {
            return null;
        }

        $user->loadMissing('tenant');

        return $user->tenant;
    }

    public function end(): void
    {
        if (function_exists('tenancy') && tenancy()->initialized) {
            tenancy()->end();
        }
    }
}
