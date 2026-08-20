<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = [
            User::SUPER_ADMIN,
            User::TENANT_ADMIN,
            User::MANAGER,
            User::CASHIER,
            User::TECHNICIAN,
            User::INVENTORY_CLERK,
            User::EMPLOYEE,
            User::CUSTOMER,
        ];

        // Global template roles use null team_id so RolePermissionSeeder can find them.
        setPermissionsTeamId(null);

        foreach ($roles as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
