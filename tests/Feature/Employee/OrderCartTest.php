<?php

namespace Tests\Feature\Employee;

use App\Enums\TenantStatus;
use App\Models\OrderCart;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Permissions\PermissionTeamScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OrderCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_save_load_and_clear_database_backed_cart(): void
    {
        $tenant = $this->makeTenant('cart-shop');
        $employee = User::create([
            'name' => 'Cashier',
            'email' => 'cashier@example.com',
            'password' => 'secret',
            'tenant_id' => $tenant->id,
            'role' => User::EMPLOYEE,
            'is_active' => true,
        ]);
        $employee->forceFill(['email_verified_at' => now()])->save();

        Permission::findOrCreate('orders.create', 'web');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        PermissionTeamScope::for($tenant->id, fn () => $employee->givePermissionTo('orders.create'));

        $payload = [
            'orders' => [
                [
                    'id' => 'draft-1',
                    'label' => 'Order 1',
                    'items' => [
                        [
                            'id' => 10,
                            'name' => 'Oil Filter',
                            'price' => 12.5,
                            'qty' => 2,
                            'discount' => null,
                            'tax_percentage' => 5,
                            'current_stock' => 8,
                            'track_inventory' => true,
                        ],
                    ],
                    'customer' => ['id' => 3, 'text' => 'Jane Doe'],
                    'vehicle' => ['id' => 7, 'text' => 'ABC-123'],
                    'serviceFees' => [],
                ],
            ],
            'active_order_id' => 'draft-1',
            'next_order_number' => 2,
        ];

        $this->actingAs($employee)
            ->postJson(route('employee.order.cart.save'), $payload)
            ->assertOk()
            ->assertJsonPath('data.active_order_id', 'draft-1')
            ->assertJsonPath('data.orders.0.items.0.qty', 2);

        $cart = OrderCart::withoutTenantScope()->first();
        $this->assertNotNull($cart);
        $this->assertSame($tenant->id, $cart->tenant_id);
        $this->assertSame($employee->id, $cart->user_id);

        $this->actingAs($employee)
            ->getJson(route('employee.order.cart.show'))
            ->assertOk()
            ->assertJsonPath('data.orders.0.items.0.name', 'Oil Filter')
            ->assertJsonPath('data.active_order_id', 'draft-1');

        $this->actingAs($employee)
            ->deleteJson(route('employee.order.cart.destroy'))
            ->assertOk()
            ->assertJsonPath('data.orders', []);

        $this->assertDatabaseMissing('order_carts', [
            'tenant_id' => $tenant->id,
            'user_id' => $employee->id,
        ]);
    }

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
}
