<?php

namespace Tests\Feature\Customer;

use App\Enums\TenantStatus;
use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use App\Models\Tenant;
use App\Services\CreditService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CustomerPortalCreditTest extends TestCase
{
    use RefreshDatabase;

    private function makeTenant(string $slug): Tenant
    {
        return Tenant::create([
            'name' => ucwords(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'owner_name' => 'Owner',
            'owner_email' => "owner@{$slug}.test",
            'owner_phone' => '+1 555 000 0000',
            'business_name' => $slug,
            'business_email' => "biz@{$slug}.test",
            'business_phone' => '+1 555 000 0000',
            'shop_name' => $slug,
            'email' => "shop@{$slug}.test",
            'phone' => '+1 555 000 0000',
            'status' => TenantStatus::Approved->value,
            'approved_at' => now(),
            'onboarding_status' => 'in_progress',
        ]);
    }

    private function makeCustomer(Tenant $tenant, array $overrides = []): Customer
    {
        $customer = new Customer;
        $customer->forceFill(array_merge([
            'tenant_id' => $tenant->id,
            'customer_type' => Customer::TYPE_REGISTERED,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secret123',
            'portal_enabled' => true,
            'password_set_at' => now(),
            'email_verified_at' => now(),
            'credit_balance' => 0,
        ], $overrides));
        $customer->save();

        return $customer;
    }

    public function test_credit_service_earns_redeems_and_records_ledger(): void
    {
        $tenant = $this->makeTenant('shop-a');
        app(TenantContext::class)->initialize($tenant);
        $customer = $this->makeCustomer($tenant);

        $service = app(CreditService::class);

        $service->earn($customer, 20.0, null, 'Welcome bonus');
        $this->assertSame(20.0, (float) $customer->fresh()->credit_balance);

        $service->redeem($customer, 5.0, null);
        $this->assertSame(15.0, (float) $customer->fresh()->credit_balance);

        $service->adjust($customer, -3.0, 'Correction');
        $this->assertSame(12.0, (float) $customer->fresh()->credit_balance);

        $ledger = CustomerCreditTransaction::where('customer_id', $customer->id)->orderBy('id')->get();
        $this->assertSame(['earn', 'redeem', 'adjust'], $ledger->pluck('type')->all());
        $this->assertSame(12.0, (float) $ledger->last()->balance_after);
    }

    public function test_cannot_redeem_more_than_balance(): void
    {
        $tenant = $this->makeTenant('shop-a');
        app(TenantContext::class)->initialize($tenant);
        $customer = $this->makeCustomer($tenant, ['credit_balance' => 4]);

        $this->expectException(ValidationException::class);
        app(CreditService::class)->redeem($customer, 10.0, null);
    }

    public function test_login_is_scoped_to_the_shop_and_isolated_across_tenants(): void
    {
        $shopA = $this->makeTenant('shop-a');
        $shopB = $this->makeTenant('shop-b');
        $this->makeCustomer($shopA, ['email' => 'shared@example.com', 'credit_balance' => 30]);
        $this->makeCustomer($shopB, ['email' => 'shared@example.com', 'credit_balance' => 99]);

        // Correct shop + password works and returns that shop's balance.
        $response = $this->postJson('/api/v1/customer/login', [
            'shop' => 'shop-a',
            'email' => 'shared@example.com',
            'password' => 'secret123',
        ]);
        $response->assertOk()->assertJsonPath('data.credit_balance', 30);
        $this->assertNotEmpty($response->json('token'));

        // Wrong password is rejected.
        $this->postJson('/api/v1/customer/login', [
            'shop' => 'shop-a',
            'email' => 'shared@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(422);
    }

    public function test_authenticated_endpoints_return_scoped_data(): void
    {
        $tenant = $this->makeTenant('shop-a');
        app(TenantContext::class)->initialize($tenant);
        $customer = $this->makeCustomer($tenant);
        app(CreditService::class)->earn($customer, 25.0, null, 'Seed');

        $token = $customer->createToken('test')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->getJson('/api/v1/customer/me', $headers)
            ->assertOk()
            ->assertJsonPath('data.email', 'jane@example.com');

        $this->getJson('/api/v1/customer/credits', $headers)
            ->assertOk()
            ->assertJsonPath('meta.balance', 25)
            ->assertJsonCount(1, 'data');
    }

    public function test_register_creates_portal_customer_and_blocks_duplicates(): void
    {
        $this->makeTenant('shop-a');

        $this->postJson('/api/v1/customer/register', [
            'shop' => 'shop-a',
            'name' => 'New Person',
            'email' => 'new@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertCreated()->assertJsonPath('data.email', 'new@example.com');

        // Second registration with the same email at the same shop is rejected.
        $this->postJson('/api/v1/customer/register', [
            'shop' => 'shop-a',
            'name' => 'New Person',
            'email' => 'new@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertStatus(422);
    }
}
