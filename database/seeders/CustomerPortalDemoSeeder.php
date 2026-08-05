<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\DiscountGroup;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use App\Repositories\Interface\OrderRepositoryInterface;
use App\Services\CreditService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds portal-ready customers with vehicles, paid orders (so store credit is
 * earned through the real checkout path) and a credit-earning customer group.
 *
 * Idempotent: re-running tops customers up to the target number of paid orders
 * rather than duplicating everything. Login password for every demo customer
 * is "password" at the tenant's slug (the "shop code" on the portal login).
 */
class CustomerPortalDemoSeeder extends Seeder
{
    private const PASSWORD = 'password';

    private const ORDERS_PER_CUSTOMER = 3;

    private const CUSTOMERS = [
        ['name' => 'Olivia Bennett', 'email' => 'olivia@obtainsolutions.com', 'phone' => '+1 555 880 0101',
            'vehicle' => ['make' => 'Toyota', 'model' => 'Corolla', 'year' => 2019, 'plate' => 'OLV-2019', 'color' => 'Silver']],
        ['name' => 'Marcus Lee', 'email' => 'marcus@obtainsolutions.com', 'phone' => '+1 555 880 0202',
            'vehicle' => ['make' => 'Honda', 'model' => 'Civic', 'year' => 2021, 'plate' => 'MAR-2021', 'color' => 'Blue']],
        ['name' => 'Priya Nair', 'email' => 'priya@obtainsolutions.com', 'phone' => '+1 555 880 0303',
            'vehicle' => ['make' => 'Ford', 'model' => 'Focus', 'year' => 2018, 'plate' => 'PRI-2018', 'color' => 'Red']],
    ];

    public function run(): void
    {
        $tenant = Tenant::query()->orderBy('id')->first();

        if (! $tenant) {
            $this->command?->warn('No tenant found — run the shop seeders first. Skipping portal demo.');

            return;
        }

        app(TenantContext::class)->initialize($tenant);

        $staff = User::query()->where('tenant_id', $tenant->id)->orderBy('id')->first();
        $products = Product::query()->where('is_active', true)->take(6)->get();

        if ($products->isEmpty()) {
            $this->command?->warn('No active products for tenant — run TenantCatalogSeeder first. Skipping portal demo.');

            return;
        }

        $this->loyaltyGroup($tenant);
        $orders = app(OrderRepositoryInterface::class);
        $credits = app(CreditService::class);

        foreach (self::CUSTOMERS as $index => $definition) {
            $customer = $this->portalCustomer($tenant, $definition);
            $vehicle = $this->vehicleFor($customer, $definition['vehicle'], $staff?->id);

            $existingOrders = $customer->orders()->where('status', '!=', Order::STATUS_ESTIMATE)->count();

            for ($n = $existingOrders; $n < self::ORDERS_PER_CUSTOMER; $n++) {
                $lineProducts = $products->shuffle()->take(2);
                $orders->store([
                    'customer_id' => $customer->id,
                    'vehicle_id' => $vehicle->id,
                    'items' => $lineProducts->map(fn (Product $p) => [
                        'product_id' => $p->id,
                        'quantity' => random_int(1, 3),
                    ])->values()->all(),
                    'payment' => ['method' => 'cash', 'amount' => 100000], // overpay; change is returned, order is paid
                ], $staff);
            }

            // Give the last customer a redeemed transaction + a manual top-up so the
            // ledger shows every transaction type in the portal.
            if ($index === array_key_last(self::CUSTOMERS)) {
                $fresh = $customer->fresh();
                $minRedeem = $credits->minRedeemBalance();
                $balance = round((float) $fresh->credit_balance, 2);

                // Unlock wallet if earned credit is still below the tenant minimum.
                if ($balance > 0 && $balance < $minRedeem) {
                    $credits->adjust(
                        $fresh,
                        round(($minRedeem - $balance) + 10, 2),
                        'Demo unlock top-up',
                        $staff?->id
                    );
                    $fresh = $customer->fresh();
                }

                if ($credits->canRedeem($fresh) && (float) $fresh->credit_balance >= 2) {
                    $credits->redeem($fresh, 2.0, null, $staff?->id);
                }

                $credits->adjust($customer->fresh(), 5.0, 'Goodwill bonus', $staff?->id);
            }

            $this->command?->info("Portal customer ready: {$customer->email} (balance ".number_format((float) $customer->fresh()->credit_balance, 2).')');
        }

        $this->command?->info('Portal web login → /login with email + password "'.self::PASSWORD.'" (no shop code).');
        $this->command?->info('Flutter/token API login also needs shop code: '.$tenant->slug.'.');
    }

    private function loyaltyGroup(Tenant $tenant): DiscountGroup
    {
        return DiscountGroup::query()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'slug' => 'portal-loyalty-gold',
            ],
            [
                'name' => 'Portal Loyalty Gold',
                'type' => 'percentage',
                'value' => 0,
                'min_limit' => 0,
                'is_active' => true,
                'earns_credit' => true,
                'credit_earn_type' => 'percentage',
                'credit_earn_rate' => 5,
                'credit_min_spend' => 0,
            ]
        );
    }

    private function portalCustomer(Tenant $tenant, array $definition): Customer
    {
        $customer = Customer::query()->where('email', $definition['email'])->first() ?? new Customer;

        $customer->forceFill([
            'tenant_id' => $tenant->id,
            'customer_type' => Customer::TYPE_REGISTERED,
            'discount_group_id' => $this->loyaltyGroup($tenant)->id,
            'name' => $definition['name'],
            'email' => $definition['email'],
            'phone' => $definition['phone'],
            'password' => self::PASSWORD,
            'portal_enabled' => true,
            'password_set_at' => now(),
            'email_verified_at' => now(),
        ]);

        if (! $customer->exists) {
            $customer->credit_balance = 0;
        }

        $customer->save();

        return $customer;
    }

    private function vehicleFor(Customer $customer, array $vehicle, ?int $userId): Vehicle
    {
        return Vehicle::query()->firstOrCreate(
            [
                'tenant_id' => $customer->tenant_id,
                'customer_id' => $customer->id,
                'plate_number' => $vehicle['plate'],
            ],
            [
                'tenant_id' => $customer->tenant_id,
                'make' => $vehicle['make'],
                'model' => $vehicle['model'],
                'year' => $vehicle['year'],
                'color' => $vehicle['color'],
                'registration_number' => Str::upper(Str::random(8)),
                'is_default' => true,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]
        );
    }
}
