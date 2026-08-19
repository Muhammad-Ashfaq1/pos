<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use App\Repositories\Interface\OrderRepositoryInterface;
use App\Support\Tenancy\TenantContext;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

#[Signature('demo:seed-al-rukn-al-thaki {--days=30 : Number of days to backfill} {--orders-per-day=4 : Orders created per day}')]
#[Description('Seed demo orders, invoices, estimates, and returns for Al Rukn Al Thaki / employee11@obtainsolutions.com')]
class SeedAlRuknAlThakiDemo extends Command
{
    private const TENANT_SLUG = 'al-rukn-al-thaki';

    private const EMPLOYEE_EMAIL = 'employee11@obtainsolutions.com';

    public function handle(OrderRepositoryInterface $orders): int
    {
        $tenant = Tenant::query()->where('slug', self::TENANT_SLUG)->first();

        if (! $tenant) {
            $this->error('Tenant "'.self::TENANT_SLUG.'" not found. Run php artisan db:seed first.');

            return self::FAILURE;
        }

        $employee = User::query()->where('email', self::EMPLOYEE_EMAIL)->first();

        if (! $employee) {
            $this->error(self::EMPLOYEE_EMAIL.' not found. Run php artisan db:seed first.');

            return self::FAILURE;
        }

        $days = max(1, (int) $this->option('days'));
        $ordersPerDay = max(1, (int) $this->option('orders-per-day'));

        app(TenantContext::class)->initialize($tenant);

        $products = Product::query()->where('is_active', true)->get();
        if ($products->isEmpty()) {
            $this->error('No active products for tenant #'.$tenant->id.'. Run TenantCatalogSeeder first.');
            app(TenantContext::class)->end();

            return self::FAILURE;
        }

        Product::query()->where('track_inventory', true)->update(['current_stock' => 100000]);

        $customers = $this->customersWithVehicles($tenant, $employee->id);
        $created = 0;

        for ($d = $days - 1; $d >= 0; $d--) {
            $day = Carbon::now()->subDays($d);

            for ($i = 0; $i < $ordersPerDay; $i++) {
                if ($this->makeOrder($orders, $products, $customers, $employee, $day, $i)) {
                    $created++;
                }
            }
        }

        $returns = $this->seedReturns($orders, $employee);

        app(TenantContext::class)->end();

        $this->info("Al Rukn Al Thaki (tenant #{$tenant->id}): seeded {$created} orders and {$returns} returns.");
        $this->line('Employee login: '.self::EMPLOYEE_EMAIL.' / '.(string) env('TENANT_DEMO_PASSWORD', 'password'));

        return self::SUCCESS;
    }

    /**
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Customer>  $customers
     */
    private function makeOrder(
        OrderRepositoryInterface $orders,
        Collection $products,
        Collection $customers,
        User $employee,
        Carbon $day,
        int $index
    ): bool {
        $customer = $customers->random();
        $vehicle = $customer->vehicles->first();

        $items = $products->shuffle()->take(random_int(1, 3))->map(fn (Product $p) => [
            'product_id' => $p->id,
            'quantity' => random_int(1, 4),
        ])->values()->all();

        $typeRoll = ($day->dayOfYear + $index) % 10;
        $isEstimate = $typeRoll === 9;
        $isInvoice = ! $isEstimate && $typeRoll >= 6;
        $method = ['cash', 'card', 'check'][random_int(0, 2)];

        $payload = [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle?->id,
            'items' => $items,
            'notes' => 'Al Rukn Al Thaki demo order.',
        ];

        if ($isEstimate) {
            $payload['is_estimate'] = true;
        } else {
            if ($isInvoice) {
                $payload['is_invoice'] = true;
                $payload['invoice_date'] = $day->toDateString();
            }

            $paymentRoll = random_int(1, 100);
            $payload['payment'] = match (true) {
                $paymentRoll <= 75 => ['method' => $method, 'amount' => 100000],
                $paymentRoll <= 90 => ['method' => $method, 'amount' => 1],
                default => ['method' => $method, 'amount' => 0],
            };
        }

        try {
            $result = $orders->store($payload, $employee);
        } catch (\Throwable $e) {
            $this->warn('Order seed failed: '.$e->getMessage());

            return false;
        }

        $this->backdate((int) $result['data']['id'], $day, $isInvoice, $employee->id);

        return true;
    }

    private function seedReturns(OrderRepositoryInterface $orders, User $employee): int
    {
        $candidates = Order::query()
            ->where('status', Order::STATUS_PAID)
            ->where('is_invoice', false)
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', now()->subDays(14))
            ->where(function ($query): void {
                $query->whereNull('notes')
                    ->orWhere('notes', 'not like', '%returned_items%');
            })
            ->with('items')
            ->orderByDesc('id')
            ->take(6)
            ->get();

        $processed = 0;

        foreach ($candidates as $index => $order) {
            if ($order->items->isEmpty()) {
                continue;
            }

            $firstItem = $order->items->first();
            $isPartial = $index % 2 === 0;
            $returnQty = $isPartial ? min(1, (int) $firstItem->quantity) : (int) $order->items->sum('quantity');

            $returnItems = $isPartial
                ? [['order_item_id' => $firstItem->id, 'quantity' => $returnQty]]
                : $order->items->map(fn ($item) => [
                    'order_item_id' => $item->id,
                    'quantity' => (int) $item->quantity,
                ])->all();

            $refundAmount = $isPartial
                ? round((float) $firstItem->unit_price * $returnQty, 2)
                : round($order->items->sum(fn ($item) => (float) $item->unit_price * (int) $item->quantity), 2);

            $totalItems = (int) $order->items->sum('quantity');
            if ($order->discount_amount > 0 && $totalItems > 0) {
                $discountShare = $isPartial
                    ? round((float) $order->discount_amount * ($returnQty / $totalItems), 2)
                    : (float) $order->discount_amount;
                $refundAmount = round($refundAmount - $discountShare, 2);
            }

            try {
                $orders->processReturn($order, [
                    'return_reason' => 'Demo return — customer changed mind.',
                    'refund_method' => 'cash',
                    'refund_amount' => max(0.01, $refundAmount),
                    'return_items' => $returnItems,
                ], $employee);
                $processed++;
            } catch (\Throwable $e) {
                $this->warn("Return seed failed for order #{$order->id}: ".$e->getMessage());
            }

            if ($processed >= 4) {
                break;
            }
        }

        return $processed;
    }

    private function backdate(int $orderId, Carbon $day, bool $isInvoice, int $userId): void
    {
        $when = $day->copy()->setTime(random_int(8, 19), random_int(0, 59), random_int(0, 59));

        $order = Order::query()->find($orderId);
        if (! $order) {
            return;
        }

        $order->forceFill([
            'created_at' => $when,
            'updated_at' => $when,
            'created_by' => $userId,
            'updated_by' => $userId,
            'paid_at' => $order->paid_at ? $when : null,
            'invoice_date' => $isInvoice ? $day->toDateString() : $order->invoice_date,
        ])->saveQuietly();

        $order->payments()->update([
            'created_at' => $when,
            'updated_at' => $when,
            'created_by' => $userId,
        ]);
    }

    /**
     * @return Collection<int, Customer>
     */
    private function customersWithVehicles(Tenant $tenant, int $userId): Collection
    {
        $existing = Customer::query()->has('vehicles')->with('vehicles')->take(8)->get();

        if ($existing->count() >= 4) {
            return $existing;
        }

        for ($i = $existing->count(); $i < 6; $i++) {
            $customer = new Customer;
            $customer->forceFill([
                'tenant_id' => $tenant->id,
                'customer_type' => Customer::TYPE_REGISTERED,
                'name' => 'Al Rukn Customer '.($i + 1),
                'phone' => '+971 50 '.str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT),
                'created_by' => $userId,
                'updated_by' => $userId,
            ])->save();

            Vehicle::query()->create([
                'customer_id' => $customer->id,
                'plate_number' => 'SHJ-'.strtoupper(substr(md5((string) $customer->id), 0, 5)),
                'make' => ['Toyota', 'Nissan', 'Hyundai'][$i % 3],
                'model' => ['Camry', 'Altima', 'Elantra'][$i % 3],
                'year' => 2018 + ($i % 6),
                'is_default' => true,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        }

        return Customer::query()->has('vehicles')->with('vehicles')->take(8)->get();
    }
}
