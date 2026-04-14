<?php

namespace Database\Seeders;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ApprovedShopSeeder extends Seeder
{
    public function run(): void
    {
        $shopEmail = env('DEMO_SHOP_EMAIL', 'owner@rapidlube.test');
        $shopPassword = env('DEMO_SHOP_PASSWORD', 'password123');
        $shopName = env('DEMO_SHOP_NAME', 'Rapid Lube Downtown');
        $shopWebsiteUrl = env('DEMO_SHOP_WEBSITE_URL', 'https://rapidlube.test');

        $tenant = Tenant::updateOrCreate(
            ['owner_email' => $shopEmail],
            [
                'name' => $shopName,
                'slug' => Str::slug($shopName),
                'owner_email' => $shopEmail,
                'owner_phone' => '+1 555 010 2200',
                'business_name' => $shopName,
                'business_email' => $shopEmail,
                'business_phone' => '+1 555 010 2200',
                'shop_name' => $shopName,
                'business_type' => 'Oil Change & Quick Service',
                'owner_name' => 'Demo Shop Owner',
                'email' => $shopEmail,
                'phone' => '+1 555 010 2200',
                'website_url' => $shopWebsiteUrl,
                'address' => '1450 Service Bay Road',
                'city' => 'Houston',
                'state' => 'Texas',
                'country' => 'USA',
                'status' => TenantStatus::Approved->value,
                'approved_at' => now(),
                'approved_by' => User::query()->where('role', User::SUPER_ADMIN)->value('id'),
                'onboarding_completed_at' => now(),
                'onboarding_status' => 'completed',
                'rejected_reason' => null,
            ]
        );

        $user = User::updateOrCreate(
            ['email' => $shopEmail],
            [
                'name' => 'Demo Shop Owner',
                'password' => $shopPassword,
                'tenant_id' => $tenant->id,
                'role' => User::TENANT_ADMIN,
                'phone' => '+1 555 010 2200',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $user->assignPrimaryRole(User::TENANT_ADMIN, $tenant->id);
    }
}
