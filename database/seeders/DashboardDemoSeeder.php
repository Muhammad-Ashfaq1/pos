<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use App\Repositories\Interface\OrderRepositoryInterface;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Backfills ~30 days of orders (varied payment methods, statuses and amounts)
 * for every tenant so the admin dashboard cards, trend chart, status/payment
 * breakdowns and "recent orders" all have data to display across date filters.
 *
 * Orders are created through OrdersRepository (real totals/tax/discounts) and
 * then backdated to a day within the window. Idempotent: a tenant that already
 * has enough recent orders is skipped.
 */
class DashboardDemoSeeder extends Seeder
{
    private const DAYS = 30;

    private const MIN_ORDERS_PER_DAY = 2;

    private const MAX_ORDERS_PER_DAY = 6;

    public function run(): void
    {
        $orders = app(OrderRepositoryInterface::class);

        Tenant::query()->orderBy('id')->each(function (Tenant $tenant) use ($orders): void {
            app(TenantContext::class)->initialize($tenant);

            $products = Product::query()->where('is_active', true)->get();
            if ($products->isEmpty()) {
                return; // No catalog → nothing to sell.
            }

            $windowStart = Carbon::now()->subDays(self::DAYS - 1)->startOfDay();
            $recentCount = Order::query()->where('created_at', '>=', $windowStart)->count();
            if ($recentCount >= self::DAYS * self::MIN_ORDERS_PER_DAY) {
                $this->command?->info("Tenant #{$tenant->id} already has {$recentCount} recent orders — skipping.");
                app(TenantContext::class)->end();

                return;
            }

            // Avoid stock-validation failures during the backfill.
            Product::query()->where('track_inventory', true)->update(['current_stock' => 100000]);

            $staff = User::query()->where('tenant_id', $tenant->id)->orderBy('id')->first();
            $customers = $this->customersWithVehicles($tenant, $staff?->id);

            $created = 0;
            for ($d = self::DAYS - 1; $d >= 0; $d--) {
                $day = Carbon::now()->subDays($d);
                $count = random_int(self::MIN_ORDERS_PER_DAY, self::MAX_ORDERS_PER_DAY);

                for ($i = 0; $i < $count; $i++) {
                    $this->makeOrder($orders, $products, $customers, $staff, $day);
                    $created++;
                }
            }

            $this->command?->info("Tenant #{$tenant->id} ({$tenant->slug}): seeded {$created} orders across ".self::DAYS.' days.');
            app(TenantContext::class)->end();
        });
    }

    /**
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Customer>  $customers
     */
    private function makeOrder(OrderRepositoryInterface $orders, $products, $customers, ?User $staff, Carbon $day): void
    {
        $customer = $customers->random();
        $vehicle = $customer->vehicles->first();

        $items = $products->shuffle()->take(random_int(1, 3))->map(fn (Product $p) => [
            'product_id' => $p->id,
            'quantity' => random_int(1, 4),
        ])->values()->all();

        // Vary the outcome so the status + payment-method breakdowns are realistic.
        $roll = random_int(1, 100);
        $isEstimate = $roll > 90;                 // ~10% estimates
        $isInvoice = ! $isEstimate && $roll > 70; // ~20% invoices
        $method = ['cash', 'card', 'check'][random_int(0, 2)];

        $payload = [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle?->id,
            'items' => $items,
        ];

        if ($isEstimate) {
            $payload['is_estimate'] = true;
        } else {
            if ($isInvoice) {
                $payload['is_invoice'] = true;
                $payload['invoice_date'] = $day->toDateString();
            }

            $paymentRoll = random_int(1, 100);

            // 70% paid in full, 18% partial, 12% pending.
            $payload['payment'] = match (true) {
                $paymentRoll <= 70 => ['method' => $method, 'amount' => 100000],
                $paymentRoll <= 88 => ['method' => $method, 'amount' => 1],   // tiny → partially paid
                default => ['method' => $method, 'amount' => 0],       // pending
            };
        }

        try {
            $result = $orders->store($payload, $staff);
        } catch (\Throwable) {
            return; // Skip any order that fails validation rather than aborting the seed.
        }

        $this->backdate((int) $result['data']['id'], $day, $isInvoice);
    }

    /** Move an order (and its non-estimate paid_at) to a random time on the given day. */
    private function backdate(int $orderId, Carbon $day, bool $isInvoice = false): void
    {
        $when = $day->copy()->setTime(random_int(8, 19), random_int(0, 59), random_int(0, 59));

        $order = Order::query()->find($orderId);
        if (! $order) {
            return;
        }

        $order->forceFill([
            'created_at' => $when,
            'updated_at' => $when,
            'paid_at' => $order->paid_at ? $when : null,
            'invoice_date' => $isInvoice ? $day->toDateString() : $order->invoice_date,
        ])->saveQuietly();

        $order->payments()->update(['created_at' => $when, 'updated_at' => $when]);
    }

    /**
     * Ensure the tenant has a handful of customers (mixed types) that each own a
     * vehicle, returning them with the vehicle relation loaded.
     */
    private function customersWithVehicles(Tenant $tenant, ?int $userId)
    {
        $existing = Customer::query()->has('vehicles')->with('vehicles')->take(8)->get();

        if ($existing->count() >= 4) {
            return $existing;
        }

        $types = [Customer::TYPE_REGISTERED, Customer::TYPE_WALK_IN, Customer::TYPE_CORPORATE];

        for ($i = $existing->count(); $i < 6; $i++) {
            $customer = new Customer;
            $customer->forceFill([
                'tenant_id' => $tenant->id,
                'customer_type' => $types[$i % count($types)],
                'name' => 'Demo Customer '.($i + 1),
                'phone' => '+1 555 9'.str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
                'created_by' => $userId,
                'updated_by' => $userId,
            ])->save();

            Vehicle::query()->create([
                'customer_id' => $customer->id,
                'plate_number' => 'DEMO-'.strtoupper(substr(md5((string) $customer->id), 0, 5)),
                'make' => ['Toyota', 'Honda', 'Ford', 'Nissan'][$i % 4],
                'model' => ['Corolla', 'Civic', 'Focus', 'Altima'][$i % 4],
                'year' => 2016 + ($i % 8),
                'is_default' => true,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        }

        return Customer::query()->has('vehicles')->with('vehicles')->take(8)->get();
    }
}
