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

    /**
     * Featured demo shop (shop #1) — Al Rukn Al Thaki, Sharjah UAE.
     * Password is shop-specific (not TENANT_DEMO_PASSWORD). Override via FEATURED_SHOP_PASSWORD.
     */
    private const FEATURED_SHOP = [
        'name' => 'Al Rukn Al Thaki',
        'slug' => 'al-rukn-al-thaki',
        'owner_name' => 'Faisal Raza',
        'owner_email' => 'alrukanalthaki@gmail.com',
        'owner_phone' => '+971 50 719 3052',
        'password' => 'RkTh@m7Kq2!xP9vL',
        'business_type' => 'Oil Change & Quick Service',
        'website_url' => 'https://alruknalthaki.com/',
        'address' => '1 Street 4, Industrial Area',
        'city' => 'Sharjah',
        'state' => 'Sharjah',
        'country' => 'United Arab Emirates',
        'tagline' => 'Trusted auto care in Sharjah',
        'business_hours' => [
            'monday' => ['is_closed' => false, 'open' => '08:00', 'close' => '21:00'],
            'tuesday' => ['is_closed' => false, 'open' => '08:00', 'close' => '21:00'],
            'wednesday' => ['is_closed' => false, 'open' => '08:00', 'close' => '21:00'],
            'thursday' => ['is_closed' => false, 'open' => '08:00', 'close' => '21:00'],
            'friday' => ['is_closed' => false, 'open' => '08:00', 'close' => '21:00'],
            'saturday' => ['is_closed' => false, 'open' => '08:00', 'close' => '21:00'],
            'sunday' => ['is_closed' => false, 'open' => '08:00', 'close' => '21:00'],
        ],
    ];

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
    ];

    public function run(): void
    {
        $superAdminId = User::query()->where('role', User::SUPER_ADMIN)->value('id');
        $emailDomain = trim((string) env('SEED_EMAIL_DOMAIN', 'obtainsolutions.com'), '@');
        $password = (string) env('TENANT_DEMO_PASSWORD', 'password');
        $websiteBase = rtrim((string) env('DEMO_SHOP_WEBSITE_BASE_URL', 'https://shops.obtainsolutions.com'), '/');

        for ($shopNumber = 1; $shopNumber <= self::SHOP_COUNT; $shopNumber++) {
            if ($shopNumber === 1) {
                $this->seedFeaturedShop($superAdminId);

                continue;
            }

            $template = self::SHOP_TEMPLATES[($shopNumber - 2) % count(self::SHOP_TEMPLATES)];

            $shopName = sprintf('%s %d', $template['name'], $shopNumber);
            $adminEmail = sprintf('admin%d@%s', $shopNumber, $emailDomain);
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
                    'website_url' => sprintf('%s/shop-%d', $websiteBase, $shopNumber),
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
                    'password' => $password,
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

    private function seedFeaturedShop(?int $superAdminId): void
    {
        $shop = self::FEATURED_SHOP;
        $password = (string) env('FEATURED_SHOP_PASSWORD', $shop['password']);
        $settings = Tenant::defaultSettings();
        $settings['business_hours'] = $shop['business_hours'];
        $settings['branding']['tagline'] = $shop['tagline'];
        $settings['regional']['currency'] = 'AED';
        $settings['regional']['timezone'] = 'Asia/Dubai';
        $settings['regional']['locale'] = 'en';

        // Keep legacy admin1@… row in sync if it already exists from older seeds.
        $legacyEmail = sprintf('admin1@%s', trim((string) env('SEED_EMAIL_DOMAIN', 'obtainsolutions.com'), '@'));

        $tenant = Tenant::query()
            ->where('owner_email', $shop['owner_email'])
            ->orWhere('owner_email', $legacyEmail)
            ->orWhere('slug', $shop['slug'])
            ->orderBy('id')
            ->first();

        $payload = [
            'name' => $shop['name'],
            'slug' => $shop['slug'],
            'owner_email' => $shop['owner_email'],
            'owner_phone' => $shop['owner_phone'],
            'business_name' => $shop['name'],
            'business_email' => $shop['owner_email'],
            'business_phone' => $shop['owner_phone'],
            'shop_name' => $shop['name'],
            'business_type' => $shop['business_type'],
            'owner_name' => $shop['owner_name'],
            'email' => $shop['owner_email'],
            'phone' => $shop['owner_phone'],
            'website_url' => $shop['website_url'],
            'address' => $shop['address'],
            'city' => $shop['city'],
            'state' => $shop['state'],
            'country' => $shop['country'],
            'settings' => $settings,
            'status' => TenantStatus::Approved->value,
            'approved_at' => now(),
            'approved_by' => $superAdminId,
            'onboarding_completed_at' => now(),
            'onboarding_status' => 'completed',
            'rejected_reason' => null,
            'rejected_at' => null,
            'suspended_at' => null,
        ];

        if ($tenant) {
            $tenant->fill($payload)->save();
        } else {
            $tenant = Tenant::query()->create($payload);
        }

        $admin = User::query()
            ->where('email', $shop['owner_email'])
            ->orWhere(function ($query) use ($legacyEmail, $tenant): void {
                $query->where('email', $legacyEmail)
                    ->where('tenant_id', $tenant->id);
            })
            ->orderBy('id')
            ->first();

        if ($admin) {
            $admin->fill([
                'name' => $shop['owner_name'],
                'email' => $shop['owner_email'],
                'password' => $password,
                'tenant_id' => $tenant->id,
                'role' => User::TENANT_ADMIN,
                'phone' => $shop['owner_phone'],
                'is_active' => true,
                'email_verified_at' => now(),
            ])->save();
        } else {
            $admin = User::query()->create([
                'name' => $shop['owner_name'],
                'email' => $shop['owner_email'],
                'password' => $password,
                'tenant_id' => $tenant->id,
                'role' => User::TENANT_ADMIN,
                'phone' => $shop['owner_phone'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        $admin->assignPrimaryRole(User::TENANT_ADMIN, $tenant->id);

        $this->command?->info(sprintf(
            'Featured shop: %s — login %s (shop-specific password; override with FEATURED_SHOP_PASSWORD)',
            $shop['name'],
            $shop['owner_email']
        ));
    }
}