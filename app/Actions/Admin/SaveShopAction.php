<?php

namespace App\Actions\Admin;

use App\Enums\TenantStatus;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SaveShopAction
{
    public function execute(array $payload, ?Tenant $tenant = null): array
    {
        $isUpdate = $tenant !== null;

        $tenant = DB::transaction(function () use ($payload, $tenant, $isUpdate): Tenant {
            $ownerName = $payload['owner_name'] ?? $payload['name'] ?? '';
            $ownerEmail = $payload['owner_email'] ?? $payload['email'] ?? '';

            $statusValue = match (true) {
                ! isset($payload['status']) => TenantStatus::Pending->value,
                $payload['status'] instanceof TenantStatus => $payload['status']->value,
                default => (string) $payload['status'],
            };

            $statusEnum = TenantStatus::tryFrom($statusValue) ?? TenantStatus::Pending;
            $allowsLogin = $statusEnum->allowsLogin();
            $planId = ! empty($payload['plan_id']) ? (int) $payload['plan_id'] : null;

            $tenantData = [
                'name' => $payload['shop_name'],
                'shop_name' => $payload['shop_name'],
                'business_name' => $payload['shop_name'],
                'owner_name' => $ownerName,
                'owner_email' => $ownerEmail,
                'email' => $ownerEmail,
                'business_email' => $ownerEmail,
                'phone' => $payload['phone'] ?? null,
                'owner_phone' => $payload['phone'] ?? null,
                'business_phone' => $payload['phone'] ?? null,
                'website_url' => $payload['website_url'] ?? null,
                'business_type' => $payload['business_type'] ?? null,
                'country' => $payload['country'] ?? null,
                'state' => $payload['state'] ?? null,
                'city' => $payload['city'] ?? null,
                'address' => $payload['address'] ?? null,
                'status' => $statusEnum->value,
                'plan_id' => $planId,
                'plan_expires_at' => $this->resolvePlanExpiry($payload, $tenant, $planId),
            ];

            if ($isUpdate) {
                $currentStatusValue = $tenant->status instanceof TenantStatus
                    ? $tenant->status->value
                    : (string) $tenant->status;

                if ($statusEnum->value !== $currentStatusValue) {
                    $tenantData['approved_at'] = $statusEnum === TenantStatus::Approved ? ($tenant->approved_at ?: now()) : null;
                    $tenantData['approved_by'] = $statusEnum === TenantStatus::Approved ? auth()->id() : null;
                    $tenantData['rejected_at'] = $statusEnum === TenantStatus::Rejected ? now() : null;
                    $tenantData['suspended_at'] = $statusEnum === TenantStatus::Suspended ? now() : null;
                }

                $tenant->fill($tenantData)->save();
            } else {
                $tenantData['slug'] = Str::slug($payload['shop_name']).'-'.Str::lower(Str::random(6));
                $tenantData['onboarding_status'] = $statusEnum === TenantStatus::Approved ? 'completed' : 'not_started';
                if ($statusEnum === TenantStatus::Approved) {
                    $tenantData['approved_at'] = now();
                    $tenantData['approved_by'] = Auth::id();
                }

                $tenant = Tenant::create($tenantData);
            }

            $adminUser = $tenant->adminUser ?: User::query()
                ->where('tenant_id', $tenant->id)
                ->where('role', User::TENANT_ADMIN)
                ->first();

            $userData = [
                'name' => $ownerName,
                'email' => $ownerEmail,
                'phone' => $payload['phone'] ?? null,
                'tenant_id' => $tenant->id,
                'role' => User::TENANT_ADMIN,
                'is_active' => $allowsLogin,
            ];

            if (! empty($payload['password'])) {
                $userData['password'] = Hash::make($payload['password']);
            }

            if ($adminUser) {
                $adminUser->fill($userData)->save();
            } else {
                if (empty($userData['password'])) {
                    $userData['password'] = Hash::make(Str::random(16));
                }
                $adminUser = User::create($userData);
            }

            $adminUser->assignPrimaryRole(User::TENANT_ADMIN, $tenant->id);

            return $tenant->fresh(['adminUser', 'plan']);
        });

        return [
            'success' => true,
            'message' => $isUpdate ? 'Shop details updated successfully.' : 'Shop created successfully.',
            'data' => $tenant,
        ];
    }

    private function resolvePlanExpiry(array $payload, ?Tenant $tenant, ?int $planId): ?string
    {
        if (! $planId) {
            return null;
        }

        if (! empty($payload['plan_expires_at'])) {
            return (string) $payload['plan_expires_at'];
        }

        $plan = Plan::query()->find($planId);

        if (! $plan) {
            return $tenant?->plan_expires_at?->toDateString();
        }

        if (! $tenant || (int) $tenant->plan_id !== $planId) {
            return now()->addDays($plan->duration_days)->toDateString();
        }

        return $tenant->plan_expires_at?->toDateString();
    }
}
