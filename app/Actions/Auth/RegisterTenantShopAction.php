<?php

namespace App\Actions\Auth;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterTenantShopAction
{
    public function execute(array $payload): User
    {
        return DB::transaction(function () use ($payload): User {
            $tenant = Tenant::create([
                'name' => $payload['shop_name'],
                'slug' => Str::slug($payload['shop_name']).'-'.Str::lower(Str::random(6)),
                'owner_name' => $payload['name'],
                'owner_email' => $payload['email'],
                'owner_phone' => $payload['phone'] ?? null,
                'business_name' => $payload['shop_name'],
                'business_email' => $payload['email'],
                'business_phone' => $payload['phone'] ?? null,
                'address' => $payload['address'] ?? null,
                'city' => $payload['city'] ?? null,
                'state' => $payload['state'] ?? null,
                'country' => $payload['country'] ?? null,
                'status' => TenantStatus::Pending->value,
                'shop_name' => $payload['shop_name'],
                'business_type' => $payload['business_type'] ?? null,
                'email' => $payload['email'],
                'phone' => $payload['phone'] ?? null,
                'website_url' => $payload['website_url'] ?? null,
                'onboarding_status' => 'not_started',
            ]);

            $user = User::create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'password' => $payload['password'],
                'tenant_id' => $tenant->id,
                'role' => User::TENANT_ADMIN,
                'phone' => $payload['phone'] ?? null,
                'is_active' => false,
            ]);

            $user->assignPrimaryRole(User::TENANT_ADMIN, $tenant->id);

            return $user;
        });
    }
}
