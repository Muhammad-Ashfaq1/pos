<?php

namespace Database\Seeders;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ApprovedShopSeeder extends Seeder
{
    private const SHOP_COUNT = 10;

    private const SHOP_TEMPLATES = [
        ['name' => 'Rapid Lube Downtown',  'city' => 'Houston',     'state' => 'Texas',      'country' => 'USA', 'business_type' => 'Oil Change & Quick Service'],
        ['name' => 'Prime Auto Care',       'city' => 'Dallas',      'state' => 'Texas',      'country' => 'USA', 'business_type' => 'Auto Service & Preventive Maintenance'],
        ['name' => 'Urban Garage',          'city' => 'Austin',      'state' => 'Texas',      'country' => 'USA', 'business_type' => 'Repair, Diagnostics & Tire Service'],
        ['name' => 'Pit Stop Pro',          'city' => 'San Antonio', 'state' => 'Texas',      'country' => 'USA', 'business_type' => 'Quick Lube & Inspection'],
        ['name' => 'Velocity Auto Works',   'city' => 'Phoenix',     'state' => 'Arizona',    'country' => 'USA', 'business_type' => 'General Auto Repair'],
        ['name' => 'Highway Heroes',        'city' => 'Denver',      'state' => 'Colorado',   'country' => 'USA', 'business_type' => 'Roadside & Maintenance'],
        ['name' => 'Gear Masters',          'city' => 'Seattle',     'state' => 'Washington', 'country' => 'USA', 'business_type' => 'Transmission & Engine'],
        ['name' => 'Drive Right Auto',      'city' => 'Portland',    'state' => 'Oregon',     'country' => 'USA', 'business_type' => 'Full Auto Service'],
        ['name' => 'Express Oil Hub',       'city' => 'Miami',       'state' => 'Florida',    'country' => 'USA', 'business_type' => 'Oil Change & Tires'],
        ['name' => 'Capital Car Care',      'city' => 'Atlanta',     'state' => 'Georgia',    'country' => 'USA', 'business_type' => 'Comprehensive Auto Care'],
    ];

    public function run(): void
    {
        if (app()->environment('production') && ! app()->runningUnitTests()) {
            $this->command?->warn('Skipping demo approved shops in production.');

            return;
        }

        $superAdminId = User::query()->where('role', User::SUPER_ADMIN)->value('id');

        for ($shopNumber = 1; $shopNumber <= self::SHOP_COUNT; $shopNumber++) {
            $template = self::SHOP_TEMPLATES[($shopNumber - 1) % count(self::SHOP_TEMPLATES)];

            $shopName = sprintf('%s %d', $template['name'], $shopNumber);
            $adminEmail = sprintf('admin%d@pos.com', $shopNumber);
            $ownerPhone = sprintf('+1 555 010 %04d', 2200 + $shopNumber);
            $ownerName = sprintf('Shop %d Admin', $shopNumber);

            $tenant = Tenant::updateOrCreate(
                ['owner_email' => $adminEmail],
                [
                    'name' => $shopName,
                    'slug' => sprintf('%s-%d', Str::slug($template['name']), $shopNumber),
                    'owner_email' => $adminEmail,
                    'owner_phone' => $ownerPhone,
                    'business_name' => $shopName,
                    'business_email' => $adminEmail,
                    'business_phone' => $ownerPhone,
                    'shop_name' => $shopName,
                    'business_type' => $template['business_type'],
                    'owner_name' => $ownerName,
                    'email' => $adminEmail,
                    'phone' => $ownerPhone,
                    'website_url' => sprintf('https://shops.demo.test/shop-%d', $shopNumber),
                    'address' => sprintf('%d Service Bay Road', 1450 + $shopNumber),
                    'city' => $template['city'],
                    'state' => $template['state'],
                    'country' => $template['country'],
                    'status' => TenantStatus::Approved->value,
                    'approved_at' => now(),
                    'approved_by' => $superAdminId,
                    'onboarding_completed_at' => now(),
                    'onboarding_status' => 'completed',
                    'rejected_reason' => null,
                    'rejected_at' => null,
                    'suspended_at' => null,
                ]
            );

            $admin = User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $ownerName,
                    'password' => 'password',
                    'tenant_id' => $tenant->id,
                    'role' => User::TENANT_ADMIN,
                    'phone' => $ownerPhone,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $admin->assignPrimaryRole(User::TENANT_ADMIN, $tenant->id);
        }
    }
}
