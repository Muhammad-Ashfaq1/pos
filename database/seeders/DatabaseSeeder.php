<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,
            ApprovedShopSeeder::class,
            TenantEmployeeSeeder::class,
            TenantRoleUserSeeder::class,
            TenantCatalogSeeder::class,
            CustomerPortalDemoSeeder::class,
            SalesDocumentsDemoSeeder::class,
            DashboardDemoSeeder::class,
        ]);
    }
}
