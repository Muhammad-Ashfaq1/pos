<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'tenant.approvals.manage',
            'tenant.impersonate',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.view',
            'roles.manage',
            'category.view',
            'category.create',
            'category.update',
            'category.delete',
            'products.view',
            'products.manage',
            'services.view',
            'services.manage',
            'inventory.view',
            'inventory.manage',
            'pos.bill',
            'discounts.manage',
            'refunds.manage',
            'customers.view',
            'customers.manage',
            'vehicles.view',
            'vehicles.manage',
            'reminders.manage',
            'reports.view',
            'audit-logs.view',
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}
