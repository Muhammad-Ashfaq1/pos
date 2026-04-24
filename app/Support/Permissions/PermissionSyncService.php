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

        $tenantAdmins = collect();

        if ($syncTenantAdmins) {
            $tenantAdmins = $this->syncTenantAdmins();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return [
            'tenant_admins_synced' => $tenantAdmins->count(),
            'tenant_admin_ids' => $tenantAdmins->pluck('id')->all(),
        ];
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
