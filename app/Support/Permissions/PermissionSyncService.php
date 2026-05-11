<?php

namespace App\Support\Permissions;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;

class PermissionSyncService
{
    private const TENANT_ROLES = [
        User::MANAGER,
        User::CASHIER,
        User::TECHNICIAN,
        User::INVENTORY_CLERK,
        User::EMPLOYEE,
    ];

    public function sync(bool $syncTenantAdmins = true): array
    {
        app(RoleSeeder::class)->run();
        app(PermissionSeeder::class)->run();
        app(RolePermissionSeeder::class)->run();

        $superAdmins = $this->syncSuperAdmins();

        $tenantAdmins = collect();
        $tenantRoleUsers = collect();

        if ($syncTenantAdmins) {
            $tenantAdmins = $this->syncTenantAdmins();
            $tenantRoleUsers = $this->syncTenantRoleUsers();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return [
            'super_admins_synced' => $superAdmins->count(),
            'super_admin_ids' => $superAdmins->pluck('id')->all(),
            'tenant_admins_synced' => $tenantAdmins->count(),
            'tenant_admin_ids' => $tenantAdmins->pluck('id')->all(),
            'tenant_role_users_synced' => $tenantRoleUsers->count(),
            'tenant_role_user_ids' => $tenantRoleUsers->pluck('id')->all(),
        ];
    }

    private function syncSuperAdmins(): Collection
    {
        $superAdmins = User::query()
            ->where('role', User::SUPER_ADMIN)
            ->get();

        $superAdmins->each(function (User $user): void {
            $this->ensureRoleAssigned($user, User::SUPER_ADMIN, 0);
        });

        return $superAdmins;
    }

    private function syncTenantAdmins(): Collection
    {
        $tenantAdmins = User::query()
            ->where('role', User::TENANT_ADMIN)
            ->whereNotNull('tenant_id')
            ->get();

        $tenantAdmins->each(function (User $user): void {
            $this->ensureRoleAssigned($user, User::TENANT_ADMIN, $user->tenant_id);
        });

        return $tenantAdmins;
    }

    private function syncTenantRoleUsers(): Collection
    {
        $users = User::query()
            ->whereIn('role', self::TENANT_ROLES)
            ->whereNotNull('tenant_id')
            ->get();

        $users->each(function (User $user): void {
            $this->ensureRoleAssigned($user, $user->role, $user->tenant_id);
        });

        return $users;
    }

    private function ensureRoleAssigned(User $user, string $role, ?int $tenantId): void
    {
        PermissionTeamScope::for($tenantId ?? 0, function () use ($user, $role): void {
            if (! $user->hasRole($role)) {
                $user->assignRole($role);
            }
        });
    }
}
