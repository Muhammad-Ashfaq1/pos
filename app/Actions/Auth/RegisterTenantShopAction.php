<?php

namespace App\Actions\Auth;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterTenantShopAction
{
    public function execute(array $payload): User
    {
        return DB::transaction(function () use ($payload): User {
            $tenant = Tenant::create([
                'shop_name' => $payload['shop_name'],
                'business_type' => $payload['business_type'] ?? null,
                'owner_name' => $payload['name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'] ?? null,
                'website_url' => $payload['website_url'] ?? null,
                'address' => $payload['address'] ?? null,
                'city' => $payload['city'] ?? null,
                'state' => $payload['state'] ?? null,
                'country' => $payload['country'] ?? null,
                'status' => TenantStatus::Pending->value,
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

            $user->syncRoles([User::TENANT_ADMIN]);

            return $user;
        });
    }
}
