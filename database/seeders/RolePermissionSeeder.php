<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::findByName(User::SUPER_ADMIN)->syncPermissions(
            Permission::query()->where('guard_name', 'web')->get()
        );

        Role::findByName(User::TENANT_ADMIN)->syncPermissions([
            Permission::findByName('dashboard.view', 'web'),
            Permission::findByName('users.view', 'web'),
            Permission::findByName('users.create', 'web'),
            Permission::findByName('users.update', 'web'),
            Permission::findByName('roles.view', 'web'),
            Permission::findByName('products.view', 'web'),
            Permission::findByName('products.manage', 'web'),
            Permission::findByName('services.view', 'web'),
            Permission::findByName('services.manage', 'web'),
            Permission::findByName('inventory.view', 'web'),
            Permission::findByName('inventory.manage', 'web'),
            Permission::findByName('pos.bill', 'web'),
            Permission::findByName('discounts.manage', 'web'),
            Permission::findByName('refunds.manage', 'web'),
            Permission::findByName('customers.view', 'web'),
            Permission::findByName('customers.manage', 'web'),
            Permission::findByName('vehicles.view', 'web'),
            Permission::findByName('vehicles.manage', 'web'),
            Permission::findByName('reminders.manage', 'web'),
            Permission::findByName('reports.view', 'web'),
            Permission::findByName('settings.manage', 'web'),
        ]);

        Role::findByName(User::MANAGER)->syncPermissions([
            Permission::findByName('dashboard.view', 'web'),
            Permission::findByName('products.view', 'web'),
            Permission::findByName('services.view', 'web'),
            Permission::findByName('inventory.view', 'web'),
            Permission::findByName('pos.bill', 'web'),
            Permission::findByName('customers.view', 'web'),
            Permission::findByName('vehicles.view', 'web'),
            Permission::findByName('reports.view', 'web'),
        ]);

        Role::findByName(User::CASHIER)->syncPermissions([
            Permission::findByName('dashboard.view', 'web'),
            Permission::findByName('pos.bill', 'web'),
            Permission::findByName('customers.view', 'web'),
            Permission::findByName('vehicles.view', 'web'),
        ]);

        Role::findByName(User::TECHNICIAN)->syncPermissions([
            Permission::findByName('dashboard.view', 'web'),
            Permission::findByName('services.view', 'web'),
            Permission::findByName('vehicles.view', 'web'),
        ]);

        Role::findByName(User::INVENTORY_CLERK)->syncPermissions([
            Permission::findByName('dashboard.view', 'web'),
            Permission::findByName('inventory.view', 'web'),
            Permission::findByName('inventory.manage', 'web'),
            Permission::findByName('products.view', 'web'),
        ]);

        Role::findByName(User::CUSTOMER)->syncPermissions([
            Permission::findByName('dashboard.view', 'web'),
        ]);
    }
}
