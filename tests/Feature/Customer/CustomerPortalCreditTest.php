<?php

namespace Tests\Feature\Customer;

use App\Enums\TenantStatus;
use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\Vehicle;
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
        $customer = $this->makeCustomer($tenant, ['credit_balance' => 50]);

        $service = app(CreditService::class);

        $service->earn($customer, 20.0, null, 'Welcome bonus');
        $this->assertSame(70.0, (float) $customer->fresh()->credit_balance);

        $service->redeem($customer, 5.0, null);
        $this->assertSame(65.0, (float) $customer->fresh()->credit_balance);

        $service->adjust($customer, -3.0, 'Correction');
        $this->assertSame(62.0, (float) $customer->fresh()->credit_balance);

        $ledger = CustomerCreditTransaction::where('customer_id', $customer->id)->orderBy('id')->get();
        $this->assertSame(['earn', 'redeem', 'adjust'], $ledger->pluck('type')->all());
        $this->assertSame(62.0, (float) $ledger->last()->balance_after);
    }

    public function test_cannot_redeem_more_than_balance(): void
    {
        $tenant = $this->makeTenant('shop-a');
        app(TenantContext::class)->initialize($tenant);
        $customer = $this->makeCustomer($tenant, ['credit_balance' => 50]);

        $this->expectException(ValidationException::class);
        app(CreditService::class)->redeem($customer, 60.0, null);
    }

    public function test_cannot_redeem_below_unlock_threshold(): void
    {
        $tenant = $this->makeTenant('shop-unlock');
        app(TenantContext::class)->initialize($tenant);
        $customer = $this->makeCustomer($tenant, [
            'email' => 'locked@example.com',
            'credit_balance' => 40,
        ]);

        $this->expectException(ValidationException::class);
        app(CreditService::class)->redeem($customer, 10.0, null);
    }

    public function test_can_partially_redeem_when_balance_meets_threshold(): void
    {
        $tenant = $this->makeTenant('shop-unlock-ok');
        app(TenantContext::class)->initialize($tenant);
        $customer = $this->makeCustomer($tenant, [
            'email' => 'ready@example.com',
            'credit_balance' => 50,
        ]);

        app(CreditService::class)->redeem($customer, 12.5, null);

        $this->assertSame(37.5, (float) $customer->fresh()->credit_balance);
    }

    public function test_tenant_credit_min_redeem_balance_defaults_to_fifty(): void
    {
        $tenant = $this->makeTenant('shop-default-min');

        $this->assertSame(50.0, $tenant->creditMinRedeemBalance());
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

        // Email + password (no shop) also works — same contract as the web form.
        $this->postJson('/api/v1/customer/login', [
            'email' => 'shared@example.com',
            'password' => 'secret123',
        ])->assertOk()->assertJsonPath('data.email', 'shared@example.com');

        // Wrong password is rejected.
        $this->postJson('/api/v1/customer/login', [
            'shop' => 'shop-a',
            'email' => 'shared@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(422);

        $this->postJson('/api/v1/customer/login', [
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

    public function test_dashboard_payload_includes_overview_cards(): void
    {
        $tenant = $this->makeTenant('shop-a');
        app(TenantContext::class)->initialize($tenant);
        $customer = $this->makeCustomer($tenant, [
            'credit_balance' => 20,
            'total_visits' => 2,
            'lifetime_value' => 80,
            'last_visit_at' => now()->subDay(),
        ]);
        app(CreditService::class)->earn($customer, 10.0, null, 'Seed');

        Vehicle::query()->create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'plate_number' => 'OLV-2019',
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2019,
            'is_default' => true,
        ]);

        $token = $customer->createToken('test')->plainTextToken;

        $this->getJson('/api/v1/customer/dashboard', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonPath('data.customer.email', 'jane@example.com')
            ->assertJsonPath('data.stats.vehicles_count', 1)
            ->assertJsonPath('data.stats.visits', 2)
            ->assertJsonCount(1, 'data.vehicles')
            ->assertJsonCount(1, 'data.recent_credits')
            ->assertJsonStructure([
                'data' => [
                    'credit' => [
                        'balance',
                        'balance_label',
                        'can_redeem',
                        'unlock_progress',
                        'remaining_to_unlock_label',
                    ],
                    'stats' => [
                        'average_spend_label',
                        'open_orders_count',
                        'paid_orders_count',
                        'last_visit_at_label',
                    ],
                    'recent_orders',
                ],
            ]);
    }

    public function test_customer_signs_in_through_the_shared_login_and_reaches_the_portal(): void
    {
        $tenant = $this->makeTenant('shop-a');
        app(TenantContext::class)->initialize($tenant);
        $this->makeCustomer($tenant, ['name' => 'Jane Doe']);

        // Same /login form as staff — no shop code, just email + password.
        $this->post(route('login.submit'), [
            'email' => 'jane@example.com',
            'password' => 'secret123',
        ])->assertRedirect(route('customer.dashboard'));

        $this->assertTrue(auth('customer')->check());

        // Server-rendered portal pages load with shared chrome + customer data.
        $this->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Jane Doe')
            ->assertSee('customer-portal.css', false)
            ->assertSee('layout-menu', false)
            ->assertSee('Overview')
            ->assertSee('Store Credit Balance');

        $this->get(route('customer.credits'))
            ->assertOk()
            ->assertSee('Credit History')
            ->assertSee('cp-hero', false);

        $this->get(route('customer.orders'))
            ->assertOk()
            ->assertSee('Service History');

        $this->get(route('customer.profile'))
            ->assertRedirect(route('account.profile'));

        $this->get(route('account.profile'))
            ->assertOk()
            ->assertSee('Save Changes')
            ->assertSee('Change Password');

        $this->get(route('account.password'))
            ->assertOk()
            ->assertSee('Change Password');
    }

    public function test_customer_can_view_own_vehicles_on_the_portal(): void
    {
        $tenant = $this->makeTenant('shop-a');
        app(TenantContext::class)->initialize($tenant);
        $customer = $this->makeCustomer($tenant, ['name' => 'Jane Doe']);

        Vehicle::query()->create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2019,
            'plate_number' => 'OLV-2019',
            'color' => 'Silver',
            'is_default' => true,
        ]);

        $this->post(route('login.submit'), [
            'email' => 'jane@example.com',
            'password' => 'secret123',
        ])->assertRedirect(route('customer.dashboard'));

        $this->get(route('customer.vehicles'))
            ->assertOk()
            ->assertSee('Vehicles')
            ->assertSee('2019 Toyota Corolla')
            ->assertSee('OLV-2019')
            ->assertSee('Default');
    }

    public function test_customer_can_change_password_while_logged_in(): void
    {
        $tenant = $this->makeTenant('shop-a');
        app(TenantContext::class)->initialize($tenant);
        $customer = $this->makeCustomer($tenant);

        $this->post(route('login.submit'), [
            'email' => 'jane@example.com',
            'password' => 'secret123',
        ])->assertRedirect(route('customer.dashboard'));

        $this->post(route('account.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'NewSecret123',
            'password_confirmation' => 'NewSecret123',
        ])->assertSessionHasErrors('current_password');

        $this->post(route('account.password.update'), [
            'current_password' => 'secret123',
            'password' => 'NewSecret123',
            'password_confirmation' => 'NewSecret123',
        ])->assertRedirect(route('account.password'))->assertSessionHas('success');

        $customer->refresh();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewSecret123', $customer->password));
        $this->assertFalse(\Illuminate\Support\Facades\Hash::check('secret123', $customer->password));
        $this->assertTrue(
            auth('customer')->attempt([
                'email' => 'jane@example.com',
                'password' => 'NewSecret123',
                'portal_enabled' => true,
            ])
        );
    }

    public function test_customer_can_download_own_order_pdf_only(): void
    {
        $tenant = $this->makeTenant('shop-a');
        app(TenantContext::class)->initialize($tenant);
        $customer = $this->makeCustomer($tenant);
        $other = $this->makeCustomer($tenant, [
            'name' => 'Other Person',
            'email' => 'other@example.com',
        ]);

        $ownOrder = new Order([
            'order_number' => 'ORD-TEST-001',
            'status' => Order::STATUS_PAID,
            'customer_id' => $customer->id,
            'total_quantity' => 1,
            'subtotal_amount' => 50,
            'discount_amount' => 0,
            'service_fee_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 50,
            'payment_amount' => 50,
        ]);
        $ownOrder->forceFill([
            'tenant_id' => $tenant->id,
            'paid_at' => now(),
        ])->saveQuietly();

        $otherOrder = new Order([
            'order_number' => 'ORD-TEST-002',
            'status' => Order::STATUS_PAID,
            'customer_id' => $other->id,
            'total_quantity' => 1,
            'subtotal_amount' => 25,
            'discount_amount' => 0,
            'service_fee_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 25,
            'payment_amount' => 25,
        ]);
        $otherOrder->forceFill([
            'tenant_id' => $tenant->id,
            'paid_at' => now(),
        ])->saveQuietly();

        $this->post(route('login.submit'), [
            'email' => 'jane@example.com',
            'password' => 'secret123',
        ])->assertRedirect(route('customer.dashboard'));

        $this->get(route('customer.orders.pdf', $ownOrder->id))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename=invoice-ORD-TEST-001.pdf');

        $this->get(route('customer.orders.pdf', $otherOrder->id))
            ->assertNotFound();
    }

    public function test_guest_is_redirected_from_portal_to_login(): void
    {
        $this->get(route('customer.dashboard'))->assertRedirect(route('login'));
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
