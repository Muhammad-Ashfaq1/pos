<?php

namespace Tests\Feature;

use App\Enums\TenantStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\OrdersRepository;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderReturnTotalTest extends TestCase
{
    use RefreshDatabase;

    private function createTenant(string $name = 'Auto Shop'): Tenant
    {
        $tenant = Tenant::create([
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'owner_name' => 'Shop Owner',
            'owner_email' => 'owner@example.test',
            'owner_phone' => '+1 555 100 1000',
            'business_name' => $name,
            'business_email' => 'owner@example.test',
            'business_phone' => '+1 555 100 1000',
            'shop_name' => $name,
            'email' => 'owner@example.test',
            'phone' => '+1 555 100 1000',
            'status' => TenantStatus::Approved->value,
            'settings' => ['return_days_after_purchase' => 30],
        ]);

        return $tenant;
    }

    public function test_order_total_decreases_after_partial_return(): void
    {
        $tenant = $this->createTenant('Auto Shop 1');

        /** @var User $user */
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);

        $product1 = (new Product)->forceFill([
            'tenant_id' => $tenant->id,
            'name' => 'Brake Pads',
            'product_type' => Product::TYPE_PART,
            'sale_price' => 50.00,
            'current_stock' => 10,
            'track_inventory' => true,
        ]);
        $product1->save();

        $product2 = (new Product)->forceFill([
            'tenant_id' => $tenant->id,
            'name' => 'Oil Filter',
            'product_type' => Product::TYPE_PART,
            'sale_price' => 20.00,
            'current_stock' => 10,
            'track_inventory' => true,
        ]);
        $product2->save();

        $order = (new Order)->forceFill([
            'tenant_id' => $tenant->id,
            'order_number' => 'ORD-1001',
            'status' => Order::STATUS_PAID,
            'total_quantity' => 3,
            'subtotal_amount' => 120.00, // 2x50 + 1x20
            'discount_amount' => 0.00,
            'service_fee_amount' => 0.00,
            'tax_amount' => 0.00,
            'total_amount' => 120.00,
            'payment_amount' => 120.00,
            'payment_method' => 'cash',
            'paid_at' => now(),
            'created_by' => $user->id,
        ]);
        $order->save();

        $item1 = (new OrderItem)->forceFill([
            'tenant_id' => $tenant->id,
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'product_name' => $product1->name,
            'quantity' => 2,
            'unit_price' => 50.00,
            'line_total' => 100.00,
        ]);
        $item1->save();

        $item2 = (new OrderItem)->forceFill([
            'tenant_id' => $tenant->id,
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'product_name' => $product2->name,
            'quantity' => 1,
            'unit_price' => 20.00,
            'line_total' => 20.00,
        ]);
        $item2->save();

        $repository = app(OrdersRepository::class);

        // Return 1 of item1 ($50 refund)
        $result = $repository->processReturn($order, [
            'return_reason' => 'Customer changed mind',
            'refund_method' => 'cash',
            'refund_amount' => 50.00,
            'return_items' => [
                [
                    'order_item_id' => $item1->id,
                    'quantity' => 1,
                ],
            ],
        ], $user);

        $this->assertTrue($result['success']);

        $order->refresh();

        // Verify order total and amounts decreased by the refund
        $this->assertEquals(70.00, (float) $order->total_amount);
        $this->assertEquals(70.00, (float) $order->payment_amount);
        $this->assertEquals(70.00, (float) $order->subtotal_amount);
        $this->assertEquals(2, $order->total_quantity);
        $this->assertEquals(Order::STATUS_PAID, $order->status);

        // Verify inventory stock was restored
        $product1->refresh();
        $this->assertEquals(11, $product1->current_stock);

        // Verify order item quantity and line_total updated
        $item1->refresh();
        $this->assertEquals(1, $item1->quantity);
        $this->assertEquals(50.00, (float) $item1->line_total);
    }

    public function test_order_total_becomes_zero_and_status_returned_after_full_return(): void
    {
        $tenant = $this->createTenant('Auto Shop 2');

        /** @var User $user */
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);

        $product = (new Product)->forceFill([
            'tenant_id' => $tenant->id,
            'name' => 'Spark Plug',
            'product_type' => Product::TYPE_PART,
            'sale_price' => 15.00,
            'current_stock' => 5,
            'track_inventory' => true,
        ]);
        $product->save();

        $order = (new Order)->forceFill([
            'tenant_id' => $tenant->id,
            'order_number' => 'ORD-1002',
            'status' => Order::STATUS_PAID,
            'total_quantity' => 2,
            'subtotal_amount' => 30.00,
            'discount_amount' => 0.00,
            'service_fee_amount' => 0.00,
            'tax_amount' => 0.00,
            'total_amount' => 30.00,
            'payment_amount' => 30.00,
            'payment_method' => 'cash',
            'paid_at' => now(),
            'created_by' => $user->id,
        ]);
        $order->save();

        $item = (new OrderItem)->forceFill([
            'tenant_id' => $tenant->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 15.00,
            'line_total' => 30.00,
        ]);
        $item->save();

        $repository = app(OrdersRepository::class);

        // Return all 2 items ($30 refund)
        $result = $repository->processReturn($order, [
            'return_reason' => 'Defective item',
            'refund_method' => 'cash',
            'refund_amount' => 30.00,
            'return_items' => [
                [
                    'order_item_id' => $item->id,
                    'quantity' => 2,
                ],
            ],
        ], $user);

        $this->assertTrue($result['success']);

        $order->refresh();

        // Verify order total and amounts are now 0 and status is returned
        $this->assertEquals(0.00, (float) $order->total_amount);
        $this->assertEquals(0.00, (float) $order->payment_amount);
        $this->assertEquals(0.00, (float) $order->subtotal_amount);
        $this->assertEquals(0, $order->total_quantity);
        $this->assertEquals(Order::STATUS_RETURNED, $order->status);

        // Stock restored
        $product->refresh();
        $this->assertEquals(7, $product->current_stock);

        // Order item quantity and line total reduced to 0
        $item->refresh();
        $this->assertEquals(0, $item->quantity);
        $this->assertEquals(0.00, (float) $item->line_total);
    }

    public function test_return_reduces_order_item_quantity_and_increases_inventory_stock(): void
    {
        // Scenario from user:
        // Original order has item with quantity 5 (remaining stock 10).
        // 2 items are returned -> order item quantity becomes 3, inventory becomes 12.
        $tenant = $this->createTenant('Auto Shop 3');

        /** @var User $user */
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);

        $product = (new Product)->forceFill([
            'tenant_id' => $tenant->id,
            'name' => 'Synthetic Motor Oil 5W-30',
            'product_type' => Product::TYPE_PART,
            'sale_price' => 20.00,
            'current_stock' => 10, // 15 originally, 5 ordered, 10 left in stock
            'track_inventory' => true,
        ]);
        $product->save();

        $order = (new Order)->forceFill([
            'tenant_id' => $tenant->id,
            'order_number' => 'ORD-1003',
            'status' => Order::STATUS_PAID,
            'total_quantity' => 5,
            'subtotal_amount' => 100.00, // 5 x 20.00
            'discount_amount' => 0.00,
            'service_fee_amount' => 0.00,
            'tax_amount' => 0.00,
            'total_amount' => 100.00,
            'payment_amount' => 100.00,
            'payment_method' => 'cash',
            'paid_at' => now(),
            'created_by' => $user->id,
        ]);
        $order->save();

        $item = (new OrderItem)->forceFill([
            'tenant_id' => $tenant->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 5,
            'unit_price' => 20.00,
            'line_total' => 100.00,
        ]);
        $item->save();

        $repository = app(OrdersRepository::class);

        // Return 2 items ($40 refund)
        $result = $repository->processReturn($order, [
            'return_reason' => 'Customer bought extra bottles',
            'refund_method' => 'cash',
            'refund_amount' => 40.00,
            'return_items' => [
                [
                    'order_item_id' => $item->id,
                    'quantity' => 2,
                ],
            ],
        ], $user);

        $this->assertTrue($result['success']);

        $order->refresh();
        $item->refresh();
        $product->refresh();

        // 1. Order item quantity updated from 5 to 3
        $this->assertEquals(3, $item->quantity);
        $this->assertEquals(60.00, (float) $item->line_total);

        // 2. Product inventory stock updated from 10 to 12
        $this->assertEquals(12, $product->current_stock);

        // 3. Order totals updated
        $this->assertEquals(3, $order->total_quantity);
        $this->assertEquals(60.00, (float) $order->total_amount);
        $this->assertEquals(60.00, (float) $order->payment_amount);
        $this->assertEquals(60.00, (float) $order->subtotal_amount);

        // Perform second consecutive return: return 1 more item ($20 refund)
        $secondResult = $repository->processReturn($order, [
            'return_reason' => 'Customer returned one more',
            'refund_method' => 'cash',
            'refund_amount' => 20.00,
            'return_items' => [
                [
                    'order_item_id' => $item->id,
                    'quantity' => 1,
                ],
            ],
        ], $user);

        $this->assertTrue($secondResult['success']);

        $order->refresh();
        $item->refresh();
        $product->refresh();

        // Order item quantity updated from 3 to 2
        $this->assertEquals(2, $item->quantity);
        $this->assertEquals(40.00, (float) $item->line_total);

        // Product stock updated from 12 to 13
        $this->assertEquals(13, $product->current_stock);

        // Order totals updated
        $this->assertEquals(2, $order->total_quantity);
        $this->assertEquals(40.00, (float) $order->total_amount);
        $this->assertEquals(40.00, (float) $order->payment_amount);
        $this->assertEquals(40.00, (float) $order->subtotal_amount);
    }
}
