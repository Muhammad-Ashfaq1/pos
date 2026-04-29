<?php

namespace Database\Seeders;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ApprovedShopSeeder extends Seeder
{
    private const DEFAULT_SHOPS = [
        [
            'name' => 'Rapid Lube Downtown',
            'city' => 'Houston',
            'state' => 'Texas',
            'country' => 'USA',
            'business_type' => 'Oil Change & Quick Service',
        ],
        [
            'name' => 'Prime Auto Care North',
            'city' => 'Dallas',
            'state' => 'Texas',
            'country' => 'USA',
            'business_type' => 'Auto Service & Preventive Maintenance',
        ],
        [
            'name' => 'Urban Garage West',
            'city' => 'Austin',
            'state' => 'Texas',
            'country' => 'USA',
            'business_type' => 'Repair, Diagnostics & Tire Service',
        ],
    ];

    public function run(): void
    {
        if (app()->environment('production') && ! app()->runningUnitTests()) {
            $this->command?->warn('Skipping demo approved shops in production.');

            return;
        }

        $shopCount = max((int) env('DEMO_ACTIVE_SHOPS_COUNT', count(self::DEFAULT_SHOPS)), 1);
        $shopPassword = env('DEMO_SHOP_PASSWORD', env('TENANT_DEMO_PASSWORD', 'password123'));
        $superAdminId = User::query()->where('role', User::SUPER_ADMIN)->value('id');
        $websiteBaseUrl = rtrim((string) env('DEMO_SHOP_WEBSITE_BASE_URL', 'https://shops.demo.test'), '/');

        for ($shopNumber = 1; $shopNumber <= $shopCount; $shopNumber++) {
            $template = self::DEFAULT_SHOPS[($shopNumber - 1) % count(self::DEFAULT_SHOPS)];
            $isLegacyConfiguredShop = $shopNumber === 1;

            $shopName = $isLegacyConfiguredShop
                ? env('DEMO_SHOP_NAME', $template['name'])
                : sprintf('%s %d', $template['name'], $shopNumber);

            $shopEmail = $isLegacyConfiguredShop
                ? env('DEMO_SHOP_EMAIL', sprintf('owner+shop%d@example.com', $shopNumber))
                : sprintf('owner+shop%d@example.com', $shopNumber);

            $shopWebsiteUrl = $isLegacyConfiguredShop
                ? env('DEMO_SHOP_WEBSITE_URL', sprintf('%s/shop-%d', $websiteBaseUrl, $shopNumber))
                : sprintf('%s/shop-%d', $websiteBaseUrl, $shopNumber);

            $ownerPhone = sprintf('+1 555 010 %04d', 2200 + $shopNumber);
            $ownerName = sprintf('Demo Shop Owner %d', $shopNumber);

            $tenant = Tenant::updateOrCreate(
                ['owner_email' => $shopEmail],
                [
                    'name' => $shopName,
                    'slug' => sprintf('%s-%d', Str::slug($shopName), $shopNumber),
                    'owner_email' => $shopEmail,
                    'owner_phone' => $ownerPhone,
                    'business_name' => $shopName,
                    'business_email' => $shopEmail,
                    'business_phone' => $ownerPhone,
                    'shop_name' => $shopName,
                    'business_type' => $template['business_type'],
                    'owner_name' => $ownerName,
                    'email' => $shopEmail,
                    'phone' => $ownerPhone,
                    'website_url' => $shopWebsiteUrl,
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
                ['email' => $shopEmail],
                [
                    'name' => $ownerName,
                    'password' => $shopPassword,
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
