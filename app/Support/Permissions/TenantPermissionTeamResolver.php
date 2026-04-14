<?php

namespace App\Support\Permissions;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\PermissionsTeamResolver;

class TenantPermissionTeamResolver implements PermissionsTeamResolver
{
    protected bool $hasExplicitTeam = false;

    protected int|string|null $teamId = null;

    public function setPermissionsTeamId(int|string|Model|null $id): void
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        if ($id === null) {
            $this->hasExplicitTeam = false;
            $this->teamId = null;

            return;
        }

        $this->hasExplicitTeam = true;
        $this->teamId = $id;
    }

    public function getPermissionsTeamId(): int|string|null
    {
        if ($this->hasExplicitTeam) {
            return $this->teamId;
        }

        if (function_exists('tenant') && tenant()) {
            return tenant()->getTenantKey();
        }

        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return $user->tenant_id ?? 0;
    }
}
