<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Discount;
use App\Models\DiscountGroup;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Repositories\Interface\OrderRepositoryInterface;
use App\Services\CreditService;
use App\Support\Currency;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrdersRepository implements OrderRepositoryInterface
{
    public function __construct(
        private readonly CreditService $creditService,
    ) {}

    public function listing(array $filters = []): array
    {
        $tab = $filters['tab'] ?? 'all';
        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['per_page'] ?? 12);

        $query = $this->makeListingQuery($filters, $tab)
            ->with([
                'customer:id,name,phone,email,customer_type',
                'vehicle:id,plate_number,registration_number,make,model,year',
            ])
            ->withCount('items');

        $total = $query->count();
        $orders = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'counts' => [
                'today' => $this->makeListingQuery($filters, 'today', false)->count(),
                'all' => $this->makeListingQuery($filters, 'all', false)->count(),
                'pending' => $this->makeListingQuery($filters, 'pending', false)->count(),
                'estimates' => $this->makeListingQuery($filters, 'estimates', false)->count(),
            ],
            'orders' => $orders->map(fn (Order $order) => $this->transformListingOrder($order))->values(),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
                'has_more' => $page * $perPage < $total,
            ],
        ];
    }

    public function details(Order $order): array
    {
        $order->load([
            'customer:id,name,phone,email,customer_type,credit_balance',
            'vehicle:id,plate_number,registration_number,make,model,year',
            'items' => fn ($query) => $query->orderBy('id'),
            'payments' => fn ($query) => $query->orderBy('id'),
            'payments.creator:id,name',
        ]);

        return $this->transformDetailedOrder($order);
    }

    public function store(array $data, ?Authenticatable $user = null): array
    {
        $userId = $user?->getAuthIdentifier();

        $order = DB::transaction(function () use ($data, $userId): Order {
            $requestedItems = collect($data['items']);
            $requestedProductIds = $requestedItems
                ->pluck('product_id')
                ->map(fn ($productId) => (int) $productId)
                ->unique()
                ->values();
            $requestedQuantityByProduct = $requestedItems
                ->groupBy(fn ($item) => (int) $item['product_id'])
                ->map(fn (Collection $items) => $items->sum(fn ($item) => (int) $item['quantity']));

            $isEstimate = ! empty($data['is_estimate']);

            $products = Product::query()
                ->with('discount:id,name,code,discount_type,applies_to,value,max_discount_amount,is_active,starts_at,ends_at')
                ->whereIn('id', $requestedProductIds->all())
                ->where('is_active', true)
                ->when(! $isEstimate, fn ($q) => $q->lockForUpdate())
                ->get()
                ->keyBy('id');

            $customer = ! empty($data['customer_id'])
                ? Customer::query()
                    ->with('discountGroup:id,name,type,value,min_limit,is_active')
                    ->find((int) $data['customer_id'])
                : null;

            if ($products->count() !== $requestedProductIds->count()) {
                throw ValidationException::withMessages([
                    'items' => 'One or more selected products are no longer available.',
                ]);
            }

            if (! $isEstimate) {
                $this->validateStockAvailability($products, $requestedQuantityByProduct);
            }

            $totalQuantity = 0;
            $subtotalAmount = 0.0;
            $productDiscountAmount = 0.0;
            $orderItems = [];
            $productDiscountDetails = [];
            $taxLines = [];

            foreach ($requestedItems as $requestedItem) {
                $product = $products->get((int) $requestedItem['product_id']);
                $quantity = (int) $requestedItem['quantity'];
                $unitPrice = round((float) $product->sale_price, 2);
                $lineSubtotal = round($unitPrice * $quantity, 2);
                $lineDiscount = $this->productDiscountAmount($product->discount, $unitPrice, $quantity);
                $lineTotal = round(max($lineSubtotal - $lineDiscount, 0), 2);

                $totalQuantity += $quantity;
                $subtotalAmount += $lineSubtotal;
                $productDiscountAmount += $lineDiscount;

                if ($lineDiscount > 0 && $product->discount) {
                    $productDiscountDetails[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'discount_id' => $product->discount->id,
                        'discount_name' => $product->discount->name,
                        'discount_type' => $product->discount->discount_type,
                        'discount_value' => (float) $product->discount->value,
                        'quantity' => $quantity,
                        'amount' => $lineDiscount,
                    ];
                }

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit' => $product->unit,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];

                $taxLines[] = [
                    'type' => 'Product',
                    'name' => $product->name,
                    'quantity' => $quantity,
                    'base' => $lineTotal,
                    'tax_percentage' => (float) ($product->tax_percentage ?? 0),
                ];
            }

            $subtotalAmount = round($subtotalAmount, 2);
            $productDiscountAmount = round($productDiscountAmount, 2);
            $afterProductDiscounts = round(max($subtotalAmount - $productDiscountAmount, 0), 2);
            $serviceFees = $this->serviceFees($data['service_fees'] ?? []);
            $serviceFeeAmount = $serviceFees['amount'];
            foreach ($serviceFees['tax_lines'] as $serviceTaxLine) {
                $taxLines[] = $serviceTaxLine;
            }

            $customerDiscountBase = round($afterProductDiscounts + $serviceFeeAmount, 2);
            $customerDiscount = $this->customerDiscount($customer?->discountGroup, $customerDiscountBase);
            $customerDiscountAmount = round($customerDiscount['amount'], 2);
            $discountAmount = round(min($subtotalAmount + $serviceFeeAmount, $productDiscountAmount + $customerDiscountAmount), 2);
            $tax = $this->taxSummary($taxLines, $customerDiscountAmount);
            $taxAmount = $tax['amount'];
            $taxBaseAmount = $tax['base'];
            $totalAmount = round(max($subtotalAmount + $serviceFeeAmount - $discountAmount, 0) + $taxAmount, 2);

            if ($isEstimate) {
                $cashPayment = 0.0;
                $paymentMethod = null;
                $creditsApplied = 0.0;
                $paymentAmount = 0.0;
                $status = Order::STATUS_ESTIMATE;
                $changeAmount = 0.0;
                $isPaid = false;
            } else {
                $cashPayment = round((float) data_get($data, 'payment.amount', 0), 2);
                $paymentMethod = data_get($data, 'payment.method');
                $creditsRequested = round((float) data_get($data, 'payment.credits_applied', 0), 2);
                $availableCredit = $customer ? round((float) $customer->credit_balance, 2) : 0.0;
                // Store credit can never exceed the customer's balance or the order total.
                $creditsApplied = round(min(max($creditsRequested, 0), $availableCredit, $totalAmount), 2);
                // payment_amount tracks ALL value paid toward the order (cash + credit).
                $paymentAmount = round($cashPayment + $creditsApplied, 2);
                $status = match (true) {
                    $paymentAmount >= $totalAmount => Order::STATUS_PAID,
                    $paymentAmount > 0 => Order::STATUS_PARTIALLY_PAID,
                    default => Order::STATUS_PENDING,
                };
                $isPaid = $status === Order::STATUS_PAID;
                $changeAmount = round(max($paymentAmount - $totalAmount, 0), 2);
            }

            $discountDetails = [
                'product_discount_amount' => $productDiscountAmount,
                'customer_discount_amount' => $customerDiscount['amount'],
                'customer_discount_eligible' => $customerDiscount['eligible'],
                'customer_discount_reason' => $customerDiscount['reason'],
                'product_discounts' => $productDiscountDetails,
                'customer_discount' => $customerDiscount['group'],
                'tax' => [
                    'base_amount' => $taxBaseAmount,
                    'amount' => $taxAmount,
                    'lines' => $tax['lines'],
                ],
            ];

            $order = Order::query()->create([
                'order_number' => $this->makeOrderNumber($isEstimate ? 'EST' : 'ORD'),
                'customer_id' => $data['customer_id'] ?? null,
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'discount_group_id' => $customerDiscount['group']['id'] ?? null,
                'discount_details' => $discountDetails,
                'status' => $status,
                'total_quantity' => $totalQuantity,
                'subtotal_amount' => $subtotalAmount,
                'discount_amount' => $discountAmount,
                'service_fee_amount' => $serviceFeeAmount,
                'service_fee_details' => $serviceFees['details'],
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'payment_amount' => $paymentAmount,
                'credit_applied' => $creditsApplied,
                'change_amount' => $changeAmount,
                'paid_at' => $isPaid ? now() : null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $order->items()->createMany($orderItems);

            if (! $isEstimate && $cashPayment > 0) {
                $order->payments()->create([
                    'tenant_id' => $order->tenant_id,
                    'amount' => $cashPayment,
                    'payment_method' => $paymentMethod,
                    'created_by' => $userId,
                ]);
            }

            if (! $isEstimate && $creditsApplied > 0 && $customer) {
                $this->creditService->redeem($customer, $creditsApplied, $order, $userId);
                $order->payments()->create([
                    'tenant_id' => $order->tenant_id,
                    'amount' => $creditsApplied,
                    'payment_method' => 'store_credit',
                    'created_by' => $userId,
                ]);
            }

            if (! $isEstimate) {
                $this->deductTrackedStock($products, $requestedQuantityByProduct, $userId);
            }

            if (! $isEstimate && $isPaid) {
                $this->applyVisitAndEarn($order, $customer, $userId);
            }

            return $order->load('items');
        });

        return [
            'success' => true,
            'message' => "Order {$order->order_number} saved successfully.",
            'data' => $this->transformOrder($order),
        ];
    }

    private function productDiscountAmount(?Discount $discount, float $unitPrice, int $quantity): float
    {
        if (! $this->discountIsActive($discount)) {
            return 0.0;
        }

        $lineSubtotal = round($unitPrice * $quantity, 2);

        if ($discount->discount_type === Discount::TYPE_PERCENTAGE) {
            $amount = round($lineSubtotal * ((float) $discount->value / 100), 2);

            if ($discount->max_discount_amount !== null) {
                $amount = min($amount, (float) $discount->max_discount_amount);
            }

            return round(min($amount, $lineSubtotal), 2);
        }

        if ($discount->discount_type === Discount::TYPE_FIXED) {
            return round(min((float) $discount->value * $quantity, $lineSubtotal), 2);
        }

        return 0.0;
    }

    private function discountIsActive(?Discount $discount): bool
    {
        if (! $discount || ! $discount->is_active || $discount->applies_to !== Discount::APPLIES_TO_ITEM) {
            return false;
        }

        if ($discount->starts_at && $discount->starts_at->isFuture()) {
            return false;
        }

        if ($discount->ends_at && $discount->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    private function customerDiscount(?DiscountGroup $group, float $baseAmount): array
    {
        $empty = [
            'amount' => 0.0,
            'eligible' => false,
            'reason' => null,
            'group' => null,
        ];

        if (! $group || ! $group->is_active) {
            return $empty;
        }

        $payload = [
            'id' => $group->id,
            'name' => $group->name,
            'type' => $group->type,
            'value' => (float) $group->value,
            'min_limit' => (float) $group->min_limit,
        ];

        if ($baseAmount <= 0) {
            return [
                'amount' => 0.0,
                'eligible' => false,
                'reason' => 'No eligible amount remains after item discounts.',
                'group' => $payload,
            ];
        }

        if ((float) $group->min_limit > 0 && $baseAmount < (float) $group->min_limit) {
            return [
                'amount' => 0.0,
                'eligible' => false,
                'reason' => 'Minimum amount not reached.',
                'group' => $payload,
            ];
        }

        $amount = match ($group->type) {
            'percentage' => round($baseAmount * ((float) $group->value / 100), 2),
            'fixed' => round((float) $group->value, 2),
            default => 0.0,
        };

        return [
            'amount' => round(min($amount, $baseAmount), 2),
            'eligible' => true,
            'reason' => null,
            'group' => $payload,
        ];
    }

    /**
     * Runs once when an order first becomes paid: grants store credit per the
     * customer's group earn rule and updates visit statistics.
     */
    private function applyVisitAndEarn(Order $order, ?Customer $customer, ?int $userId): void
    {
        if (! $customer) {
            return;
        }

        // Pre-tax net spend is the qualifying base for earning credit.
        $netSpend = round(
            (float) $order->subtotal_amount + (float) $order->service_fee_amount - (float) $order->discount_amount,
            2
        );

        // Re-fetch the group with all columns — store()/addPayment() eager-load
        // it with a restricted column list that omits the credit-earn fields.
        $group = $customer->discount_group_id
            ? DiscountGroup::query()->find($customer->discount_group_id)
            : null;

        $earned = $group ? $group->creditEarnedFor($netSpend) : 0.0;

        if ($earned > 0) {
            $this->creditService->earn(
                $customer,
                $earned,
                $order,
                'Credit earned on order '.$order->order_number,
                $userId
            );
            $order->forceFill(['credit_earned' => $earned])->save();
        }

        // Visit stats (lock the row to avoid lost updates under concurrency).
        $locked = Customer::query()->lockForUpdate()->find($customer->getKey());

        if ($locked) {
            $locked->forceFill([
                'total_visits' => (int) $locked->total_visits + 1,
                'lifetime_value' => round((float) $locked->lifetime_value + (float) $order->total_amount, 2),
                'last_visit_at' => now(),
            ])->save();
        }
    }

    private function serviceFees(array $requestedFees): array
    {
        $requestedFees = collect($requestedFees)
            ->filter(fn ($fee) => is_array($fee) && in_array($fee['type'] ?? null, ['service', 'manual'], true))
            ->values();

        if ($requestedFees->isEmpty()) {
            return [
                'amount' => 0.0,
                'details' => null,
                'tax_lines' => [],
            ];
        }

        $serviceIds = $requestedFees
            ->pluck('service_id')
            ->filter()
            ->map(fn ($serviceId) => (int) $serviceId)
            ->unique()
            ->values();

        $services = Service::query()
            ->select(['id', 'name', 'title_en', 'title_ar', 'code', 'standard_price', 'tax_percentage'])
            ->whereIn('id', $serviceIds->all())
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        if ($services->count() !== $serviceIds->count()) {
            throw ValidationException::withMessages([
                'service_fees' => 'One or more selected services are no longer available.',
            ]);
        }

        $details = [];

        foreach ($requestedFees as $fee) {
            if (($fee['type'] ?? null) === 'service') {
                $service = $services->get((int) ($fee['service_id'] ?? 0));

                if (! $service) {
                    continue;
                }

                $amount = round((float) $service->standard_price, 2);
                $details[] = [
                    'type' => 'service',
                    'service_id' => $service->id,
                    'name' => $service->localized_title,
                    'code' => $service->code,
                    'amount' => $amount,
                    'tax_percentage' => (float) ($service->tax_percentage ?? 0),
                ];

                continue;
            }

            $amount = round((float) ($fee['amount'] ?? 0), 2);
            if ($amount <= 0) {
                continue;
            }

            $service = ! empty($fee['service_id'])
                ? $services->get((int) $fee['service_id'])
                : null;
            $name = trim((string) ($fee['name'] ?? ''));

            $details[] = [
                'type' => 'manual',
                'service_id' => $service?->id,
                'name' => $name !== '' ? $name : ($service?->localized_title ?? __('services.manual_service_fee')),
                'code' => $service?->code,
                'service_name' => $service?->localized_title,
                'amount' => $amount,
                'tax_percentage' => (float) ($service?->tax_percentage ?? 0),
            ];
        }

        $amount = round(collect($details)->sum(fn ($fee) => (float) $fee['amount']), 2);
        $taxLines = collect($details)
            ->map(fn (array $fee) => [
                'type' => __('services.service'),
                'name' => $fee['name'] ?? __('services.service_fee'),
                'quantity' => 1,
                'base' => (float) ($fee['amount'] ?? 0),
                'tax_percentage' => (float) ($fee['tax_percentage'] ?? 0),
            ])
            ->values()
            ->all();

        return [
            'amount' => $amount,
            'details' => $details === [] ? null : $details,
            'tax_lines' => $taxLines,
        ];
    }

    private function taxSummary(array $lines, float $customerDiscountAmount): array
    {
        $activeLines = collect($lines)
            ->map(function (array $line): array {
                return [
                    'type' => (string) ($line['type'] ?? 'Line'),
                    'name' => (string) ($line['name'] ?? 'Line'),
                    'quantity' => (float) ($line['quantity'] ?? 1),
                    'base' => round(max((float) ($line['base'] ?? 0), 0), 2),
                    'tax_percentage' => round(max((float) ($line['tax_percentage'] ?? 0), 0), 2),
                ];
            })
            ->filter(fn (array $line) => $line['base'] > 0)
            ->values();

        if ($activeLines->isEmpty()) {
            return [
                'base' => 0.0,
                'amount' => 0.0,
                'lines' => [],
            ];
        }

        $baseBeforeCustomerDiscount = round($activeLines->sum(fn (array $line) => $line['base']), 2);
        $discountToAllocate = round(min(max($customerDiscountAmount, 0), $baseBeforeCustomerDiscount), 2);
        $remainingDiscount = $discountToAllocate;
        $lastIndex = $activeLines->count() - 1;
        $details = [];

        foreach ($activeLines as $index => $line) {
            $base = (float) $line['base'];
            $allocatedDiscount = $index === $lastIndex
                ? $remainingDiscount
                : round($discountToAllocate * ($base / $baseBeforeCustomerDiscount), 2);
            $allocatedDiscount = round(min(max($allocatedDiscount, 0), $base, $remainingDiscount), 2);
            $taxableAmount = round(max($base - $allocatedDiscount, 0), 2);
            $taxPercentage = (float) $line['tax_percentage'];
            $taxAmount = round($taxableAmount * ($taxPercentage / 100), 2);
            $remainingDiscount = round(max($remainingDiscount - $allocatedDiscount, 0), 2);

            $details[] = [
                'type' => $line['type'],
                'name' => $line['name'],
                'quantity' => $line['quantity'],
                'tax_percentage' => $taxPercentage,
                'base_amount' => $base,
                'discount_amount' => $allocatedDiscount,
                'taxable_amount' => $taxableAmount,
                'tax_amount' => $taxAmount,
            ];
        }

        return [
            'base' => round(collect($details)->sum(fn (array $line) => (float) $line['taxable_amount']), 2),
            'amount' => round(collect($details)->sum(fn (array $line) => (float) $line['tax_amount']), 2),
            'lines' => $details,
        ];
    }

    private function makeOrderNumber(string $prefix = 'ORD'): string
    {
        do {
            $orderNumber = $prefix.'-'.now()->format('Ymd-His').'-'.random_int(100, 999);
        } while (Order::query()->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    private function validateStockAvailability(Collection $products, Collection $requestedQuantityByProduct): void
    {
        foreach ($requestedQuantityByProduct as $productId => $requestedQuantity) {
            $product = $products->get((int) $productId);

            if (! $product || ! $product->track_inventory) {
                continue;
            }

            $currentStock = (int) $product->current_stock;

            if ($currentStock >= (int) $requestedQuantity) {
                continue;
            }

            throw ValidationException::withMessages([
                'items' => "{$product->name} has only {$currentStock} unit(s) in stock.",
            ]);
        }
    }

    private function deductTrackedStock(Collection $products, Collection $requestedQuantityByProduct, mixed $userId): void
    {
        foreach ($requestedQuantityByProduct as $productId => $requestedQuantity) {
            $product = $products->get((int) $productId);

            if (! $product || ! $product->track_inventory) {
                continue;
            }

            $product->forceFill([
                'current_stock' => max((int) $product->current_stock - (int) $requestedQuantity, 0),
                'updated_by' => $userId,
            ])->save();
        }
    }

    private function transformOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'total_quantity' => $order->total_quantity,
            'subtotal_amount' => (float) $order->subtotal_amount,
            'discount_amount' => (float) $order->discount_amount,
            'discount_details' => $order->discount_details,
            'service_fee_amount' => (float) $order->service_fee_amount,
            'service_fee_details' => $order->service_fee_details,
            'tax_amount' => (float) $order->tax_amount,
            'total_amount' => (float) $order->total_amount,
            'payment_method' => $order->payment_method,
            'payment_amount' => (float) $order->payment_amount,
            'change_amount' => (float) $order->change_amount,
            'paid_at' => $order->paid_at?->toISOString(),
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
            ])->values(),
        ];
    }

    private function makeListingQuery(array $filters, string $tab, bool $withSort = true): Builder
    {
        $query = Order::query();

        $this->applyListingFilters($query, $filters, $tab);

        if ($withSort) {
            $this->applyListingSort($query, $filters['sort'] ?? 'latest');
        }

        return $query;
    }

    private function applyListingFilters(Builder $query, array $filters, string $tab): void
    {
        $search = trim((string) ($filters['q'] ?? ''));

        if ($search !== '') {
            $this->applyListingSearch($query, $search, $filters['search_fields'] ?? []);
        }

        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        if ($tab === 'estimates') {
            $query->where('status', Order::STATUS_ESTIMATE);
        } else {
            $query->where('status', '!=', Order::STATUS_ESTIMATE);

            if ($tab === 'today') {
                $query->whereDate('created_at', now()->toDateString());
            }

            if ($tab === 'pending') {
                $query->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PARTIALLY_PAID]);
            }
        }
    }

    private function applyListingSearch(Builder $query, string $search, array $searchFields): void
    {
        $searchFields = collect($searchFields)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($searchFields === []) {
            $query->where(function (Builder $builder) use ($search): void {
                $this->applyDefaultListingSearch($builder, $search);
            });

            return;
        }

        $query->where(function (Builder $builder) use ($search, $searchFields): void {
            foreach ($searchFields as $field) {
                $this->applyListingSearchField($builder, (string) $field, $search);
            }
        });
    }

    private function applyDefaultListingSearch(Builder $builder, string $search): void
    {
        $builder
            ->where('order_number', 'like', "%{$search}%")
            ->orWhere('status', 'like', "%{$search}%")
            ->orWhereHas('customer', function (Builder $customerQuery) use ($search): void {
                $customerQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('customer_type', 'like', "%{$search}%");
            })
            ->orWhereHas('vehicle', function (Builder $vehicleQuery) use ($search): void {
                $vehicleQuery
                    ->where('plate_number', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('make', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            })
            ->orWhereHas('items', function (Builder $itemQuery) use ($search): void {
                $itemQuery
                    ->where('product_name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
    }

    private function applyListingSearchField(Builder $builder, string $field, string $search): void
    {
        match ($field) {
            'order_number' => $builder->orWhere('order_number', 'like', "%{$search}%"),
            'customer_id' => $builder->orWhere('customer_id', 'like', "%{$search}%"),
            'paid_status' => $builder->orWhere('status', 'like', "%{$search}%"),
            'retailer' => $this->applyRetailerSearch($builder, $search),
            'time' => $this->applyTimeSearch($builder, $search),
            'date' => $this->applyDateSearch($builder, $search),
            default => null,
        };
    }

    private function applyRetailerSearch(Builder $builder, string $search): void
    {
        $builder->orWhereHas('customer', function (Builder $customerQuery) use ($search): void {
            $customerQuery->where('customer_type', 'like', "%{$search}%");
        });

        $normalizedSearch = str($search)->lower()->toString();

        if (
            str_contains($normalizedSearch, 'retail')
            || (strlen($normalizedSearch) >= 3 && str_contains('retail', $normalizedSearch))
        ) {
            $builder->orWhereNotNull('id');
        }
    }

    private function applyTimeSearch(Builder $builder, string $search): void
    {
        $builder->orWhere('created_at', 'like', "%{$search}%");

        try {
            $time = Carbon::parse($search)->format('H:i');
            $builder->orWhereTime('created_at', 'like', "{$time}%");
        } catch (\Throwable) {
            //
        }
    }

    private function applyDateSearch(Builder $builder, string $search): void
    {
        $builder->orWhere('created_at', 'like', "%{$search}%");

        if (! preg_match('/\d/', $search)) {
            return;
        }

        try {
            $builder->orWhereDate('created_at', Carbon::parse($search)->toDateString());
        } catch (\Throwable) {
            //
        }
    }

    private function applyListingSort(Builder $query, string $sort): void
    {
        match ($sort) {
            'customer_name' => $query
                ->orderBy(
                    Customer::query()
                        ->select('name')
                        ->whereColumn('customers.id', 'orders.customer_id')
                        ->limit(1)
                )
                ->orderBy('id'),
            'date_opened' => $query->orderByDesc('created_at')->orderByDesc('id'),
            'order_id' => $query->orderBy('id'),
            'order_total' => $query->orderByDesc('total_amount')->orderByDesc('created_at'),
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'amount_desc' => $query->orderByDesc('total_amount')->orderByDesc('created_at'),
            'amount_asc' => $query->orderBy('total_amount')->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    private function transformListingOrder(Order $order): array
    {
        $status = $this->paymentAwareStatus($order);
        $customerName = trim((string) ($order->customer?->name ?? '')) ?: 'Walk-In Customer';
        $vehicleLabel = trim(collect([
            $order->vehicle?->year,
            $order->vehicle?->make,
            $order->vehicle?->model,
        ])->filter()->implode(' '));

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $customerName,
            'vehicle_label' => $vehicleLabel,
            'plate_number' => $order->vehicle?->plate_number,
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'status_class' => $this->listingStatusClass($status),
            'total_amount' => (float) $order->total_amount,
            'total_amount_label' => $this->moneyLabel((float) $order->total_amount),
            'created_at_label' => 'Retail | '.$order->created_at?->format('M j, h:i A'),
            'items_count' => (int) ($order->items_count ?? 0),
        ];
    }

    private function transformDetailedOrder(Order $order): array
    {
        $totalAmount = (float) $order->total_amount;
        $paymentAmount = (float) $order->payment_amount;
        $status = $this->paymentAwareStatus($order);
        $servicePriceAmount = $this->serviceChargeAmount($order, 'service');
        $manualServiceFeeAmount = $this->serviceChargeAmount($order, 'manual');
        $balanceDue = $status === Order::STATUS_PAID
            ? 0.0
            : max($totalAmount - $paymentAmount, 0);

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'status_class' => $this->listingStatusClass($status),
            'customer_id' => $order->customer_id,
            'customer_email' => $order->customer?->email,
            'customer_name' => trim((string) ($order->customer?->name ?? '')) ?: 'Walk-In Customer',
            'customer_credit_balance' => (float) ($order->customer?->credit_balance ?? 0),
            'customer_credit_balance_label' => $this->moneyLabel((float) ($order->customer?->credit_balance ?? 0)),
            'payment_method_label' => filled($order->payment_method)
                ? str((string) $order->payment_method)->replace('_', ' ')->title()->toString()
                : 'N/A',
            'subtotal_amount' => (float) $order->subtotal_amount,
            'subtotal_amount_label' => $this->moneyLabel((float) $order->subtotal_amount),
            'discount_amount' => (float) $order->discount_amount,
            'discount_amount_label' => $this->moneyLabel((float) $order->discount_amount),
            'service_fee_amount' => (float) $order->service_fee_amount,
            'service_fee_amount_label' => $this->moneyLabel((float) $order->service_fee_amount),
            'service_fee_details' => $order->service_fee_details,
            'service_price_amount' => $servicePriceAmount,
            'service_price_amount_label' => $this->moneyLabel($servicePriceAmount),
            'manual_service_fee_amount' => $manualServiceFeeAmount,
            'manual_service_fee_amount_label' => $this->moneyLabel($manualServiceFeeAmount),
            'tax_amount' => (float) $order->tax_amount,
            'tax_amount_label' => $this->moneyLabel((float) $order->tax_amount),
            'tax_lines' => $this->taxDetailLines($order),
            'total_amount' => $totalAmount,
            'total_amount_label' => $this->moneyLabel($totalAmount),
            'payment_amount' => $paymentAmount,
            'payment_amount_label' => $this->moneyLabel($paymentAmount),
            'credit_applied' => (float) $order->credit_applied,
            'credit_applied_label' => $this->moneyLabel((float) $order->credit_applied),
            'credit_earned' => (float) $order->credit_earned,
            'credit_earned_label' => $this->moneyLabel((float) $order->credit_earned),
            'balance_due' => $balanceDue,
            'balance_due_label' => $this->moneyLabel($balanceDue),
            'items_count' => (int) $order->items->sum('quantity'),
            'created_at_label' => $order->created_at?->format('M j, Y h:i A'),
            'items' => $order->items
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'quantity' => (float) $item->quantity,
                    'quantity_label' => number_format((float) $item->quantity, 3),
                    'unit_price_label' => $this->moneyLabel((float) $item->unit_price),
                    'line_total_label' => $this->moneyLabel((float) $item->line_total),
                    'tax_detail_label' => $item->product_name.' (x'.number_format((float) $item->quantity, 3).')',
                ])
                ->values(),
            'payment_history' => $order->payments->map(fn ($payment) => [
                'id' => $payment->id,
                'amount' => (float) $payment->amount,
                'amount_label' => $this->moneyLabel((float) $payment->amount),
                'payment_method' => $payment->payment_method,
                'payment_method_label' => str((string) $payment->payment_method)->replace('_', ' ')->title()->toString(),
                'created_at_label' => $payment->created_at?->format('M j, Y h:i A'),
                'collector_name' => $payment->creator?->name ?? 'System',
                'is_refund' => (float) $payment->amount < 0,
                'type_label' => (float) $payment->amount < 0 ? 'Refund' : 'Payment',
            ])->values()->all(),
        ];
    }

    private function serviceChargeAmount(Order $order, string $type): float
    {
        $details = $order->service_fee_details;

        if (! is_array($details) || $details === []) {
            return $type === 'manual' ? (float) $order->service_fee_amount : 0.0;
        }

        return round(collect($details)
            ->filter(fn ($fee) => is_array($fee) && ($fee['type'] ?? null) === $type)
            ->sum(fn ($fee) => (float) ($fee['amount'] ?? 0)), 2);
    }

    private function taxDetailLines(Order $order): array
    {
        $lines = data_get($order->discount_details, 'tax.lines', []);

        if (! is_array($lines) || $lines === []) {
            return [];
        }

        return collect($lines)
            ->filter(fn ($line) => is_array($line) && (float) ($line['tax_amount'] ?? 0) > 0)
            ->map(fn (array $line) => [
                'label' => $this->taxLineLabel($line),
                'rate_label' => rtrim(rtrim(number_format((float) ($line['tax_percentage'] ?? 0), 2, '.', ''), '0'), '.').'%',
                'taxable_amount_label' => $this->moneyLabel((float) ($line['taxable_amount'] ?? 0)),
                'tax_amount_label' => $this->moneyLabel((float) ($line['tax_amount'] ?? 0)),
            ])
            ->values()
            ->all();
    }

    private function taxLineLabel(array $line): string
    {
        $name = trim((string) ($line['name'] ?? 'Taxable line'));
        $type = trim((string) ($line['type'] ?? ''));
        $quantity = (float) ($line['quantity'] ?? 1);
        $quantityLabel = $quantity > 1 ? ' x'.number_format($quantity, 0) : '';

        return trim($type.' - '.$name.$quantityLabel, ' -');
    }

    private function moneyLabel(float $amount): string
    {
        return Currency::format($amount);
    }

    private function timeSinceLabel(\Carbon\Carbon $date): string
    {
        $diff = $date->diff(now());

        if ($diff->y > 0) {
            return $diff->y === 1 ? '1 year ago' : $diff->y . ' years ago';
        }

        if ($diff->m > 0) {
            return $diff->m === 1 ? '1 month ago' : $diff->m . ' months ago';
        }

        if ($diff->d > 0) {
            return $diff->d === 1 ? '1 day ago' : $diff->d . ' days ago';
        }

        if ($diff->h > 0) {
            return $diff->h === 1 ? '1 hour ago' : $diff->h . ' hours ago';
        }

        if ($diff->i > 0) {
            return $diff->i === 1 ? '1 minute ago' : $diff->i . ' minutes ago';
        }

        return 'Just now';
    }

    private function paymentAwareStatus(Order $order): string
    {
        $status = (string) $order->status;
        $totalAmount = (float) $order->total_amount;
        $paymentAmount = (float) $order->payment_amount;

        if ($status === Order::STATUS_PAID) {
            return Order::STATUS_PAID;
        }

        if ($totalAmount > 0 && $paymentAmount >= $totalAmount) {
            return Order::STATUS_PAID;
        }

        if ($paymentAmount > 0 && $paymentAmount < $totalAmount) {
            return Order::STATUS_PARTIALLY_PAID;
        }

        return $status;
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            Order::STATUS_PARTIALLY_PAID => 'Partially Paid',
            Order::STATUS_RETURNED => 'Returned',
            default => str($status)->replace('_', ' ')->title()->toString(),
        };
    }

    private function listingStatusClass(string $status): string
    {
        return match ($status) {
            Order::STATUS_PAID => 'success',
            Order::STATUS_PARTIALLY_PAID => 'warning',
            Order::STATUS_PENDING => 'warning',
            Order::STATUS_ESTIMATE => 'info',
            Order::STATUS_RETURNED => 'danger',
            default => 'secondary',
        };
    }

    public function addPayment(Order $order, array $paymentData, ?Authenticatable $user = null): array
    {
        $userId = $user?->getAuthIdentifier();

        $order = DB::transaction(function () use ($order, $paymentData, $userId): Order {
            if ($order->status === Order::STATUS_PAID) {
                throw ValidationException::withMessages([
                    'payment' => 'This order is already fully paid.',
                ]);
            }

            $cashPayment = round((float) ($paymentData['payment_amount'] ?? 0), 2);
            $paymentMethod = $paymentData['payment_method'] ?? 'cash';
            $creditsRequested = round((float) ($paymentData['credits_applied'] ?? 0), 2);

            $customer = $order->customer_id ? $order->customer()->first() : null;
            $totalAmount = (float) $order->total_amount;
            $balanceDue = round(max($totalAmount - (float) $order->payment_amount, 0), 2);
            $availableCredit = $customer ? round((float) $customer->credit_balance, 2) : 0.0;
            // Credit applied is capped at the balance still owed and the wallet balance.
            $creditsApplied = round(min(max($creditsRequested, 0), $availableCredit, $balanceDue), 2);
            $paymentAmountAdded = round($cashPayment + $creditsApplied, 2);

            if ($paymentAmountAdded <= 0) {
                throw ValidationException::withMessages([
                    'payment_amount' => 'Payment amount must be greater than zero.',
                ]);
            }

            if ($order->status === Order::STATUS_ESTIMATE) {
                // Converting Estimate to Order
                $order->load('items');
                $productIds = $order->items->pluck('product_id')->filter()->unique()->values();
                $requestedQuantityByProduct = $order->items
                    ->groupBy('product_id')
                    ->map(fn ($items) => $items->sum('quantity'));

                $products = Product::query()
                    ->whereIn('id', $productIds->all())
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($products->count() !== $productIds->count()) {
                    throw ValidationException::withMessages([
                        'payment' => 'One or more items in the estimate are no longer available in the catalog.',
                    ]);
                }

                // Validate stock availability
                $this->validateStockAvailability($products, $requestedQuantityByProduct);

                // Deduct stock
                $this->deductTrackedStock($products, $requestedQuantityByProduct, $userId);

                // Convert status and numbering
                $newOrderNumber = $this->makeOrderNumber('ORD');
                $status = match (true) {
                    $paymentAmountAdded >= $totalAmount => Order::STATUS_PAID,
                    default => Order::STATUS_PARTIALLY_PAID,
                };

                $order->forceFill([
                    'order_number' => $newOrderNumber,
                    'status' => $status,
                    'payment_method' => $paymentMethod,
                    'payment_amount' => $paymentAmountAdded,
                    'credit_applied' => round((float) $order->credit_applied + $creditsApplied, 2),
                    'change_amount' => round(max($paymentAmountAdded - $totalAmount, 0), 2),
                    'paid_at' => $status === Order::STATUS_PAID ? now() : null,
                    'updated_by' => $userId,
                ])->save();
            } else {
                // Standard Payment Top-up
                $newPaymentAmount = round((float) $order->payment_amount + $paymentAmountAdded, 2);
                $status = match (true) {
                    $newPaymentAmount >= $totalAmount => Order::STATUS_PAID,
                    default => Order::STATUS_PARTIALLY_PAID,
                };

                $order->forceFill([
                    'status' => $status,
                    'payment_method' => $paymentMethod,
                    'payment_amount' => $newPaymentAmount,
                    'credit_applied' => round((float) $order->credit_applied + $creditsApplied, 2),
                    'change_amount' => round(max($newPaymentAmount - $totalAmount, 0), 2),
                    'paid_at' => $status === Order::STATUS_PAID ? now() : null,
                    'updated_by' => $userId,
                ])->save();
            }

            if ($cashPayment > 0) {
                $order->payments()->create([
                    'tenant_id' => $order->tenant_id,
                    'amount' => $cashPayment,
                    'payment_method' => $paymentMethod,
                    'created_by' => $userId,
                ]);
            }

            if ($creditsApplied > 0 && $customer) {
                $this->creditService->redeem($customer, $creditsApplied, $order, $userId);
                $order->payments()->create([
                    'tenant_id' => $order->tenant_id,
                    'amount' => $creditsApplied,
                    'payment_method' => 'store_credit',
                    'created_by' => $userId,
                ]);
            }

            if ($order->status === Order::STATUS_PAID) {
                $this->applyVisitAndEarn($order, $customer, $userId);
            }

            return $order->load('items');
        });

        return [
            'success' => true,
            'message' => 'Payment added successfully. Order status is now '.$this->statusLabel($order->status).'.',
            'data' => $this->transformOrder($order),
        ];
    }

    public function returnsListing(array $filters = [], int $returnDays = 30): array
    {
        $search = trim((string) ($filters['q'] ?? ''));
        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['per_page'] ?? 12);

        $query = Order::query()
            ->whereIn('status', [Order::STATUS_PAID, Order::STATUS_PARTIALLY_PAID])
            ->where('paid_at', '>=', now()->subDays($returnDays))
            ->where('status', '!=', 'returned')
            ->with([
                'customer:id,name,phone,email',
                'vehicle:id,plate_number,make,model',
                'items',
            ])
            ->withCount('items');

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function (Builder $customerQuery) use ($search): void {
                        $customerQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('vehicle', function (Builder $vehicleQuery) use ($search): void {
                        $vehicleQuery
                            ->where('plate_number', 'like', "%{$search}%")
                            ->orWhere('make', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    });
            });
        }

        $total = $query->count();
        
        $orders = $query
            ->orderByDesc('paid_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        // Filter out orders where all items have been fully returned
        $orders = $orders->filter(function (Order $order) {
            $alreadyReturnedQuantities = $this->getAlreadyReturnedQuantities($order);
            $totalReturned = array_sum($alreadyReturnedQuantities);
            $totalOriginal = $order->items->sum('quantity');
            return $totalReturned < $totalOriginal;
        });

        return [
            'orders' => $orders->map(fn (Order $order) => $this->transformReturnableOrder($order, $returnDays))->values(),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'has_more' => ($page * $perPage) < $total,
            ],
        ];
    }

    public function returnsHistory(array $filters = []): array
    {
        $search = trim((string) ($filters['q'] ?? ''));
        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['per_page'] ?? 12);

        $query = Order::query()
            ->where(function ($query) {
                $query->where('status', Order::STATUS_RETURNED)
                    ->orWhereNotNull('notes');
            })
            ->where(function ($query) {
                $query->whereJsonLength('notes', '>', 0)
                    ->orWhere('status', Order::STATUS_RETURNED);
            })
            ->with([
                'customer:id,name,phone,email',
                'vehicle:id,plate_number,make,model',
                'payments' => fn ($query) => $query->orderBy('id'),
            ])
            ->withCount('items');

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function (Builder $customerQuery) use ($search): void {
                        $customerQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('vehicle', function (Builder $vehicleQuery) use ($search): void {
                        $vehicleQuery
                            ->where('plate_number', 'like', "%{$search}%")
                            ->orWhere('make', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    });
            });
        }

        $total = $query->count();
        
        $orders = $query
            ->orderByDesc('updated_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'orders' => $orders->map(fn (Order $order) => $this->transformReturnedOrder($order))->values(),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'has_more' => ($page * $perPage) < $total,
            ],
        ];
    }

    private function getAlreadyReturnedQuantities(Order $order): array
    {
        $notes = $order->notes ? json_decode($order->notes, true) : [];
        if (!is_array($notes)) {
            return [];
        }

        $returnedQuantities = [];
        foreach ($notes as $note) {
            if (isset($note['returned_items']) && is_array($note['returned_items'])) {
                foreach ($note['returned_items'] as $itemId => $qty) {
                    $itemId = (int) $itemId;
                    $qty = (int) $qty;
                    if (!isset($returnedQuantities[$itemId])) {
                        $returnedQuantities[$itemId] = 0;
                    }
                    $returnedQuantities[$itemId] += $qty;
                }
            }
        }

        return $returnedQuantities;
    }

    public function processReturn(Order $order, array $data, ?Authenticatable $user = null): array
    {
        $userId = $user?->getAuthIdentifier();
        $returnItems = $data['return_items'] ?? [];
        $refundAmount = round((float) ($data['refund_amount'] ?? 0), 2);

        $order = DB::transaction(function () use ($order, $data, $returnItems, $refundAmount, $userId): Order {
            // Load order items
            $order->load('items');

            // Get already returned quantities from order notes
            $alreadyReturnedQuantities = $this->getAlreadyReturnedQuantities($order);

            // Validate return items and calculate refund based on actual item prices
            $itemQuantitiesToReturn = [];
            $calculatedRefundAmount = 0;
            $totalReturnedItems = 0;

            foreach ($returnItems as $returnItem) {
                $orderItemId = (int) $returnItem['order_item_id'];
                $returnQty = (int) $returnItem['quantity'];

                $orderItem = $order->items->firstWhere('id', $orderItemId);
                if (!$orderItem) {
                    throw ValidationException::withMessages([
                        'return_items' => "Order item with ID {$orderItemId} not found.",
                    ]);
                }

                $alreadyReturned = $alreadyReturnedQuantities[$orderItemId] ?? 0;
                $availableToReturn = $orderItem->quantity - $alreadyReturned;

                if ($returnQty > $availableToReturn) {
                    throw ValidationException::withMessages([
                        'return_items' => "Cannot return more than {$availableToReturn} items for {$orderItem->product_name}. Already returned: {$alreadyReturned}, Original quantity: {$orderItem->quantity}.",
                    ]);
                }

                $itemQuantitiesToReturn[$orderItemId] = $returnQty;
                $totalReturnedItems += $returnQty;

                // Calculate refund based on actual item unit price (excluding tax and service fee)
                $calculatedRefundAmount += round($orderItem->unit_price * $returnQty, 2);
            }

            // Calculate proportional discount only (service fee is not refundable for partial returns)
            $totalOrderItems = $order->items->sum('quantity');
            $proportionalDiscount = $order->discount_amount > 0
                ? round($order->discount_amount * ($totalReturnedItems / $totalOrderItems), 2)
                : 0;

            // Subtract proportional discount only
            $calculatedRefundAmount = round($calculatedRefundAmount - $proportionalDiscount, 2);

            // Validate refund amount matches calculated amount (allow small difference due to rounding)
            if (abs($refundAmount - $calculatedRefundAmount) > 0.05) {
                throw ValidationException::withMessages([
                    'refund_amount' => "Refund amount mismatch. Expected: {$calculatedRefundAmount}, Provided: {$refundAmount}",
                ]);
            }

            // Update order status to returned if all items are returned
            if ($totalReturnedItems >= $totalOrderItems) {
                $order->forceFill([
                    'status' => 'returned',
                    'updated_by' => $userId,
                ])->save();
            }

            // Create a refund payment record (negative amount)
            $order->payments()->create([
                'tenant_id' => $order->tenant_id,
                'amount' => -$refundAmount,
                'payment_method' => $data['refund_method'],
                'created_by' => $userId,
            ]);

            // Store return details in order notes
            $returnDetails = [
                'return_reason' => $data['return_reason'],
                'refund_method' => $data['refund_method'],
                'refund_amount' => $refundAmount,
                'returned_items' => $itemQuantitiesToReturn,
                'returned_at' => now()->toIso8601String(),
            ];

            $existingNotes = $order->notes ? json_decode($order->notes, true) : [];
            if (!is_array($existingNotes)) {
                $existingNotes = [];
            }
            $existingNotes[] = $returnDetails;

            $order->forceFill([
                'notes' => json_encode($existingNotes),
                'updated_by' => $userId,
            ])->save();

            // Restore stock for tracked items (only for returned quantities)
            foreach ($itemQuantitiesToReturn as $orderItemId => $returnQty) {
                $orderItem = $order->items->firstWhere('id', $orderItemId);
                if (!$orderItem) continue;

                $product = Product::query()
                    ->where('id', $orderItem->product_id)
                    ->where('track_inventory', true)
                    ->first();

                if ($product) {
                    $product->forceFill([
                        'current_stock' => (int) $product->current_stock + $returnQty,
                        'updated_by' => $userId,
                    ])->save();
                }
            }

            return $order->load('items', 'payments');
        });

        return [
            'success' => true,
            'message' => "Order {$order->order_number} returned successfully. Refund amount: {$this->moneyLabel($refundAmount)}.",
            'data' => $this->transformOrder($order),
        ];
    }

    private function transformReturnableOrder(Order $order, int $returnDays): array
    {
        $daysSincePayment = $order->paid_at ? $order->paid_at->diffInDays(now()) : 0;
        $isEligible = $daysSincePayment <= $returnDays;
        $refundableAmount = $order->subtotal_amount + $order->service_fee_amount - $order->discount_amount;
        $totalOrderItems = $order->items->sum('quantity');

        // Human-readable time since payment
        $timeSincePaymentLabel = $order->paid_at ? $this->timeSinceLabel($order->paid_at) : 'N/A';

        $alreadyReturnedQuantities = $this->getAlreadyReturnedQuantities($order);

        $items = $order->items->map(function ($item) use ($alreadyReturnedQuantities) {
            $returnedQuantity = $alreadyReturnedQuantities[$item->id] ?? 0;
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'sku' => $item->sku,
                'quantity' => (int) $item->quantity,
                'returned_quantity' => $returnedQuantity,
                'available_quantity' => (int) $item->quantity - $returnedQuantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
                'unit_price_label' => $this->moneyLabel((float) $item->unit_price),
                'line_total_label' => $this->moneyLabel((float) $item->line_total),
            ];
        })->values();

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => trim((string) ($order->customer?->name ?? '')) ?: 'Walk-In Customer',
            'customer_phone' => $order->customer?->phone,
            'vehicle_label' => trim(collect([
                $order->vehicle?->year,
                $order->vehicle?->make,
                $order->vehicle?->model,
            ])->filter()->implode(' ')),
            'plate_number' => $order->vehicle?->plate_number,
            'total_amount' => (float) $order->total_amount,
            'total_amount_label' => $this->moneyLabel((float) $order->total_amount),
            'refundable_amount' => max($refundableAmount, 0),
            'refundable_amount_label' => $this->moneyLabel(max($refundableAmount, 0)),
            'paid_at' => $order->paid_at?->toISOString(),
            'paid_at_label' => $order->paid_at?->format('M j, Y h:i A'),
            'days_since_payment' => $daysSincePayment,
            'time_since_payment_label' => $timeSincePaymentLabel,
            'is_eligible' => $isEligible,
            'return_policy_days' => $returnDays,
            'items_count' => (int) ($order->items_count ?? 0),
            'items' => $items,
            'service_fee_amount' => (float) $order->service_fee_amount,
            'discount_amount' => (float) $order->discount_amount,
            'total_items' => $totalOrderItems,
        ];
    }

    private function transformReturnedOrder(Order $order): array
    {
        $refundPayment = $order->payments->first(fn ($payment) => $payment->amount < 0);
        $refundAmount = $refundPayment ? abs((float) $refundPayment->amount) : 0;
        $refundMethod = $refundPayment ? $refundPayment->payment_method : null;
        $refundMethodLabel = $refundMethod ? str($refundMethod)->replace('_', ' ')->title()->toString() : 'N/A';
        $refundedAt = $refundPayment ? $refundPayment->created_at : $order->updated_at;

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => trim((string) ($order->customer?->name ?? '')) ?: 'Walk-In Customer',
            'customer_phone' => $order->customer?->phone,
            'vehicle_label' => trim(collect([
                $order->vehicle?->year,
                $order->vehicle?->make,
                $order->vehicle?->model,
            ])->filter()->implode(' ')),
            'plate_number' => $order->vehicle?->plate_number,
            'total_amount' => (float) $order->total_amount,
            'total_amount_label' => $this->moneyLabel((float) $order->total_amount),
            'refund_amount' => $refundAmount,
            'refund_amount_label' => $this->moneyLabel($refundAmount),
            'refund_method' => $refundMethod,
            'refund_method_label' => $refundMethodLabel,
            'refunded_at' => $refundedAt?->toISOString(),
            'refunded_at_label' => $refundedAt?->format('M j, Y h:i A'),
            'items_count' => (int) ($order->items_count ?? 0),
        ];
    }
}
