<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Interface\OrderRepositoryInterface;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Seeds a clear mix of sales documents per tenant:
 * estimates (EST-…), POS orders (ORD-…), and invoices (INV-…).
 *
 * Uses OrdersRepository so totals/tax/payments match production.
 * Idempotent: tops each document type up to the target counts.
 */
class SalesDocumentsDemoSeeder extends Seeder
{
    private const TARGET_ESTIMATES = 5;

    private const TARGET_ORDERS = 8;

    private const TARGET_INVOICES = 8;

    public function run(): void
    {
        $orders = app(OrderRepositoryInterface::class);

        Tenant::query()->orderBy('id')->each(function (Tenant $tenant) use ($orders): void {
            app(TenantContext::class)->initialize($tenant);

            $products = Product::query()->where('is_active', true)->get();
            if ($products->isEmpty()) {
                $this->command?->warn("Tenant #{$tenant->id}: no products — skip sales documents.");
                app(TenantContext::class)->end();

                return;
            }

            Product::query()->where('track_inventory', true)->update(['current_stock' => 100000]);

            $staff = User::query()->where('tenant_id', $tenant->id)->orderBy('id')->first();
            $customers = $this->customersWithVehicles($tenant, $staff?->id);

            if ($customers->isEmpty()) {
                $this->command?->warn("Tenant #{$tenant->id}: no customers — skip sales documents.");
                app(TenantContext::class)->end();

                return;
            }

            $created = [
                'estimates' => $this->seedType($orders, $products, $customers, $staff, 'estimate', self::TARGET_ESTIMATES),
                'orders' => $this->seedType($orders, $products, $customers, $staff, 'order', self::TARGET_ORDERS),
                'invoices' => $this->seedType($orders, $products, $customers, $staff, 'invoice', self::TARGET_INVOICES),
            ];

            $this->command?->info(
                "Tenant #{$tenant->id} ({$tenant->slug}): sales docs +"
                ."{$created['estimates']} estimates, +{$created['orders']} orders, +{$created['invoices']} invoices."
            );

            app(TenantContext::class)->end();
        });
    }

    /**
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Customer>  $customers
     */
    private function seedType(
        OrderRepositoryInterface $orders,
        Collection $products,
        Collection $customers,
        ?User $staff,
        string $type,
        int $target
    ): int {
        $existing = match ($type) {
            'estimate' => Order::query()->where('status', Order::STATUS_ESTIMATE)->count(),
            'invoice' => Order::query()->where('is_invoice', true)->where('status', '!=', Order::STATUS_ESTIMATE)->count(),
            default => Order::query()
                ->where('is_invoice', false)
                ->where('status', '!=', Order::STATUS_ESTIMATE)
                ->count(),
        };

        $needed = max(0, $target - $existing);
        $created = 0;

        for ($i = 0; $i < $needed; $i++) {
            $day = Carbon::now()->subDays(random_int(0, 21));

            if ($this->makeDocument($orders, $products, $customers, $staff, $type, $day)) {
                $created++;
            }
        }

        return $created;
    }

    /**
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Customer>  $customers
     */
    private function makeDocument(
        OrderRepositoryInterface $orders,
        Collection $products,
        Collection $customers,
        ?User $staff,
        string $type,
        Carbon $day
    ): bool {
        $customer = $customers->random();
        $vehicle = $customer->vehicles->first();

        $items = $products->shuffle()->take(random_int(1, 3))->map(fn (Product $p) => [
            'product_id' => $p->id,
            'quantity' => random_int(1, 3),
        ])->values()->all();

        $payload = [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle?->id,
            'items' => $items,
            'notes' => match ($type) {
                'estimate' => 'Demo estimate seeded for UI / PDF testing.',
                'invoice' => 'Demo invoice seeded for invoices listing.',
                default => 'Demo POS order seeded for orders listing.',
            },
        ];

        if ($type === 'estimate') {
            $payload['is_estimate'] = true;
        } else {
            if ($type === 'invoice') {
                $payload['is_invoice'] = true;
                $payload['invoice_date'] = $day->toDateString();
            }

            $method = ['cash', 'card', 'check'][random_int(0, 2)];
            $paymentRoll = random_int(1, 100);

            // ~65% paid, ~20% partial, ~15% pending
            $payload['payment'] = match (true) {
                $paymentRoll <= 65 => ['method' => $method, 'amount' => 100000],
                $paymentRoll <= 85 => ['method' => $method, 'amount' => 1],
                default => ['method' => $method, 'amount' => 0],
            };
        }

        try {
            $result = $orders->store($payload, $staff);
        } catch (\Throwable $e) {
            $this->command?->warn("Failed to seed {$type}: ".$e->getMessage());

            return false;
        }

        $this->backdate((int) $result['data']['id'], $day, $type === 'invoice');

        return true;
    }

    private function backdate(int $orderId, Carbon $day, bool $isInvoice): void
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
     * @return Collection<int, Customer>
     */
    private function customersWithVehicles(Tenant $tenant, ?int $userId): Collection
    {
        $existing = Customer::query()->has('vehicles')->with('vehicles')->take(10)->get();

        if ($existing->count() >= 3) {
            return $existing;
        }

        for ($i = $existing->count(); $i < 4; $i++) {
            $customer = new Customer;
            $customer->forceFill([
                'tenant_id' => $tenant->id,
                'customer_type' => Customer::TYPE_REGISTERED,
                'name' => 'Sales Doc Customer '.($i + 1),
                'email' => sprintf('sales.doc%d.t%d@obtainsolutions.com', $i + 1, $tenant->id),
                'phone' => '+1 555 7'.str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
                'created_by' => $userId,
                'updated_by' => $userId,
            ])->save();

            $customer->vehicles()->create([
                'plate_number' => 'SD-'.strtoupper(substr(md5((string) $customer->id), 0, 5)),
                'make' => 'Toyota',
                'model' => 'Camry',
                'year' => 2020,
                'is_default' => true,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        }

        return Customer::query()->has('vehicles')->with('vehicles')->take(10)->get();
    }
}
