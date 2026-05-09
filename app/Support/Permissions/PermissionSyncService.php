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
    public function sync(bool $syncTenantAdmins = true): array
    {
        app(RoleSeeder::class)->run();
        app(PermissionSeeder::class)->run();
        app(RolePermissionSeeder::class)->run();

        $superAdmins = $this->syncSuperAdmins();

        $tenantAdmins = collect();

        if ($syncTenantAdmins) {
            $tenantAdmins = $this->syncTenantAdmins();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return [
            'super_admins_synced' => $superAdmins->count(),
            'super_admin_ids' => $superAdmins->pluck('id')->all(),
            'tenant_admins_synced' => $tenantAdmins->count(),
            'tenant_admin_ids' => $tenantAdmins->pluck('id')->all(),
        ];
    }

    private function syncSuperAdmins(): Collection
    {
        $superAdmins = User::query()
            ->where('role', User::SUPER_ADMIN)
            ->get();

        $superAdmins->each(function (User $user): void {
            $user->assignPrimaryRole(User::SUPER_ADMIN);
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
            $user->assignPrimaryRole(User::TENANT_ADMIN, $user->tenant_id);
        });

        return $tenantAdmins;
    }
}
