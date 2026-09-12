<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Permissions\PermissionTeamScope;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public const ROLE_PERMISSIONS = [
        User::TENANT_ADMIN => [
            'dashboard.view',
            'users.view',
            'users.create',
            'users.update',
            'roles.view',
            'category.view',
            'category.create',
            'category.update',
            'category.delete',
            'subcategory.view',
            'subcategory.create',
            'subcategory.update',
            'subcategory.delete',
            'product-type.view',
            'product-type.create',
            'product-type.update',
            'product-type.delete',
            'product.view',
            'product.create',
            'product.update',
            'product.delete',
            'product.adjust_stock',
            'products.view',
            'products.manage',
            'service.view',
            'service.create',
            'service.update',
            'service.delete',
            'service-category.view',
            'service-category.create',
            'service-category.update',
            'service-category.delete',
            'services.view',
            'services.manage',
            'inventory.view',
            'inventory.manage',
            'pos.bill',
            'orders.view',
            'orders.create',
            'returns.view',
            'refunds.manage',
            'discount.manage',
            'discount.apply_bill',
            'discount.apply_item',
            'discounts.manage',
            'discount-group.view',
            'discount-group.manage',
            'cards.view',
            'cards.create',
            'cards.update',
            'cards.delete',
            'cards.manage',
            'customer.view',
            'customer.create',
            'customer.update',
            'customer.delete',
            'customers.view',
            'customers.manage',
            'vehicle.view',
            'vehicle.create',
            'vehicle.update',
            'vehicle.delete',
            'vehicles.view',
            'vehicles.manage',
            'reminders.manage',
            'reports.view',
            'settings.manage',
        ],
        User::MANAGER => [
            'dashboard.view',
            'category.view',
            'subcategory.view',
            'product-type.view',
            'product.view',
            'products.view',
            'service.view',
            'service-category.view',
            'services.view',
            'inventory.view',
            'pos.bill',
            'orders.view',
            'orders.create',
            'returns.view',
            'refunds.manage',
            'discount.manage',
            'discount.apply_bill',
            'discount.apply_item',
            'discount-group.view',
            'discount-group.manage',
            'cards.view',
            'cards.create',
            'cards.update',
            'cards.delete',
            'cards.manage',
            'customer.view',
            'customer.create',
            'customer.update',
            'customers.view',
            'vehicle.view',
            'vehicle.create',
            'vehicle.update',
            'vehicles.view',
            'reports.view',
        ],
        User::CASHIER => [
            'dashboard.view',
            'pos.bill',
            'orders.view',
            'orders.create',
            'returns.view',
            'refunds.manage',
            'discount.apply_bill',
            'discount.apply_item',
            'customer.view',
            'customer.create',
            'customer.update',
            'customers.view',
            'vehicle.view',
            'vehicle.create',
            'vehicle.update',
            'vehicles.view',
            'reports.view',
        ],
        User::TECHNICIAN => [
            'dashboard.view',
            'orders.view',
            'service.view',
            'services.view',
            'customer.view',
            'customers.view',
            'vehicle.view',
            'vehicles.view',
            'reports.view',
        ],
        User::INVENTORY_CLERK => [
            'dashboard.view',
            'inventory.view',
            'inventory.manage',
            'product.view',
            'product.adjust_stock',
            'products.view',
            'reports.view',
        ],
        User::EMPLOYEE => [
            'dashboard.view',
            'pos.bill',
            'orders.view',
            'orders.create',
            'returns.view',
            'refunds.manage',
            'product.view',
            'product.create',
            'product.update',
            'products.view',
            'service.view',
            'services.view',
            'cards.view',
            'cards.create',
            'customer.view',
            'customer.create',
            'customers.view',
            'vehicle.view',
            'vehicle.create',
            'vehicles.view',
            'reports.view',
        ],
        User::CUSTOMER => [
            'dashboard.view',
        ],
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Seed against global (null-team) role templates.
        setPermissionsTeamId(null);

        Role::findByName(User::SUPER_ADMIN)->syncPermissions(
            Permission::query()->where('guard_name', 'web')->get()
        );

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            Role::findByName($roleName)->syncPermissions($permissions);
        }

        // Seed tenant-scoped roles and default permissions for any existing tenants.
        Tenant::query()->orderBy('id')->get()->each(function (Tenant $tenant): void {
            $this->seedForTenant($tenant);
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Seed all tenant roles and their default permissions scoped to the specified tenant.
     */
    public function seedForTenant(Tenant|int $tenant): void
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        PermissionTeamScope::for($tenantId, function () {
            foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
                $role = Role::findOrCreate($roleName, 'web');
                $role->syncPermissions($permissions);
            }
        });
    }
}
