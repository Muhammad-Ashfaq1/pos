<?php

namespace Database\Seeders;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class ApprovedShopSeeder extends Seeder
{
    public function run(): void
    {
        $shopEmail = env('DEMO_SHOP_EMAIL', 'owner@rapidlube.test');
        $shopPassword = env('DEMO_SHOP_PASSWORD', 'password123');
        $shopName = env('DEMO_SHOP_NAME', 'Rapid Lube Downtown');
        $shopDomain = env('DEMO_SHOP_DOMAIN', 'rapidlube.localhost');

        $tenant = Tenant::updateOrCreate(
            ['email' => $shopEmail],
            [
                'shop_name' => $shopName,
                'business_type' => 'Oil Change & Quick Service',
                'owner_name' => 'Demo Shop Owner',
                'phone' => '+1 555 010 2200',
                'website_url' => 'https://' . $shopDomain,
                'address' => '1450 Service Bay Road',
                'city' => 'Houston',
                'state' => 'Texas',
                'country' => 'USA',
                'status' => TenantStatus::Approved->value,
                'approved_at' => now(),
                'approved_by' => User::query()->where('role', User::SUPER_ADMIN)->value('id'),
                'onboarding_status' => 'completed',
                'rejected_reason' => null,
            ]
        );

        $tenant->domains()->updateOrCreate(
            ['domain' => $shopDomain],
            ['tenant_id' => $tenant->id]
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

        $user->syncRoles([User::TENANT_ADMIN]);
    }
}
