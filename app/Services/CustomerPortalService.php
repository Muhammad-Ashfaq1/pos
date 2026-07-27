<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Tenant;
use App\Notifications\CustomerPortalLinkNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Account lifecycle for the customer portal: self-registration, staff invites,
 * and password-reset tokens. Shared by the API (web panel + Flutter) and the
 * tenant staff UI so there is one implementation of each rule.
 */
class CustomerPortalService
{
    public const TOKEN_TTL_MINUTES = 60;

    public function findTenantBySlug(string $slug): ?Tenant
    {
        return Tenant::query()->where('slug', $slug)->first();
    }

    /**
     * Find a portal-login candidate for a shop. Bypasses the tenant global
     * scope because no tenancy is initialized on guest endpoints.
     */
    public function findCustomerForLogin(Tenant $tenant, string $email): ?Customer
    {
        return Customer::query()
            ->forTenant($tenant->getKey())
            ->where('email', $email)
            ->first();
    }

    /**
     * Public self-registration. Creates a new portal-enabled customer for the
     * shop, or claims an existing record that has no portal login yet.
     */
    public function register(Tenant $tenant, array $data): Customer
    {
        $existing = $this->findCustomerForLogin($tenant, $data['email']);

        if ($existing && $existing->hasPortalAccess()) {
            throw ValidationException::withMessages([
                'email' => 'An account with this email already exists at this shop. Please sign in instead.',
            ]);
        }

        $customer = $existing ?? new Customer;
        $customer->forceFill([
            'tenant_id' => $tenant->getKey(),
            'customer_type' => $existing->customer_type ?? Customer::TYPE_REGISTERED,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? $customer->phone,
            'password' => $data['password'],
            'portal_enabled' => true,
            'password_set_at' => now(),
            'email_verified_at' => $customer->email_verified_at ?? now(),
        ])->save();

        return $customer->refresh();
    }

    /**
     * Enable portal access for an existing customer (staff action) and email
     * them an activation link. Returns the plain token for testing/links.
     */
    public function invite(Customer $customer): string
    {
        if (blank($customer->email)) {
            throw ValidationException::withMessages([
                'email' => 'This customer has no email address to send an invite to.',
            ]);
        }

        $customer->forceFill(['portal_enabled' => true])->save();

        $token = $this->generateResetToken($customer);
        $customer->notify(new CustomerPortalLinkNotification(
            $this->buildLink($customer, $token),
            $this->shopName($customer),
            isInvite: true,
        ));

        return $token;
    }

    /**
     * Begin a forgot-password flow. Silently no-ops for unknown/disabled
     * accounts so the endpoint cannot be used to enumerate customers.
     */
    public function sendResetLink(Tenant $tenant, string $email): void
    {
        $customer = $this->findCustomerForLogin($tenant, $email);

        if (! $customer || ! $customer->portal_enabled) {
            return;
        }

        $token = $this->generateResetToken($customer);
        $customer->notify(new CustomerPortalLinkNotification(
            $this->buildLink($customer, $token),
            $this->shopName($customer),
            isInvite: false,
        ));
    }

    /**
     * Complete an invite or reset: validate the token then set the password.
     */
    public function resetPassword(Tenant $tenant, string $email, string $token, string $password): Customer
    {
        $customer = $this->findCustomerForLogin($tenant, $email);

        if (! $customer || blank($customer->reset_token) || ! Hash::check($token, $customer->reset_token)) {
            throw ValidationException::withMessages([
                'token' => 'This link is invalid. Please request a new one.',
            ]);
        }

        if ($customer->reset_token_expires_at && Carbon::parse($customer->reset_token_expires_at)->isPast()) {
            throw ValidationException::withMessages([
                'token' => 'This link has expired. Please request a new one.',
            ]);
        }

        $customer->forceFill([
            'password' => $password,
            'portal_enabled' => true,
            'password_set_at' => now(),
            'email_verified_at' => $customer->email_verified_at ?? now(),
            'reset_token' => null,
            'reset_token_expires_at' => null,
        ])->save();

        return $customer->refresh();
    }

    /**
     * Logged-in customer changing their own password (portal profile).
     */
    public function changePassword(Customer $customer, string $currentPassword, string $newPassword): Customer
    {
        if (! Hash::check($currentPassword, (string) $customer->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Your current password is incorrect.',
            ]);
        }

        $customer->forceFill([
            'password' => $newPassword,
            'password_set_at' => now(),
        ])->save();

        return $customer->refresh();
    }

    private function generateResetToken(Customer $customer): string
    {
        $token = Str::random(64);

        $customer->forceFill([
            'reset_token' => Hash::make($token),
            'reset_token_expires_at' => now()->addMinutes(self::TOKEN_TTL_MINUTES),
        ])->save();

        return $token;
    }

    private function buildLink(Customer $customer, string $token): string
    {
        return url('/portal/reset').'?'.http_build_query([
            'shop' => $customer->tenant?->slug,
            'email' => $customer->email,
            'token' => $token,
        ]);
    }

    private function shopName(Customer $customer): string
    {
        $customer->loadMissing('tenant');

        return $customer->tenant?->name
            ?? $customer->tenant?->shop_name
            ?? 'our shop';
    }
}
