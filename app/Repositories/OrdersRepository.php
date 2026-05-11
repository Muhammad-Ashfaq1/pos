<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Repositories\Interface\OrderRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrdersRepository implements OrderRepositoryInterface
{
    public function listing(array $filters = []): array
    {
        $tab = $filters['tab'] ?? 'all';

        $orders = $this->makeListingQuery($filters, $tab)
            ->with([
                'customer:id,name,phone,email,customer_type',
                'vehicle:id,plate_number,registration_number,make,model,year',
            ])
            ->withCount('items')
            ->limit(100)
            ->get();

        return [
            'counts' => [
                'today' => $this->makeListingQuery($filters, 'today', false)->count(),
                'all' => $this->makeListingQuery($filters, 'all', false)->count(),
                'pending' => $this->makeListingQuery($filters, 'pending', false)->count(),
            ],
            'orders' => $orders->map(fn (Order $order) => $this->transformListingOrder($order))->values(),
        ];
    }

    public function details(Order $order): array
    {
        $order->load([
            'customer:id,name,phone,email,customer_type',
            'vehicle:id,plate_number,registration_number,make,model,year',
            'items' => fn ($query) => $query->orderBy('id'),
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

            $products = Product::query()
                ->whereIn('id', $requestedProductIds->all())
                ->where('is_active', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $requestedProductIds->count()) {
                throw ValidationException::withMessages([
                    'items' => 'One or more selected products are no longer available.',
                ]);
            }

            $this->validateStockAvailability($products, $requestedQuantityByProduct);

            $totalQuantity = 0;
            $subtotalAmount = 0.0;
            $orderItems = [];

            foreach ($requestedItems as $requestedItem) {
                $product = $products->get((int) $requestedItem['product_id']);
                $quantity = (int) $requestedItem['quantity'];
                $unitPrice = round((float) $product->sale_price, 2);
                $lineTotal = round($unitPrice * $quantity, 2);

                $totalQuantity += $quantity;
                $subtotalAmount += $lineTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit' => $product->unit,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $subtotalAmount = round($subtotalAmount, 2);
            $paymentAmount = round((float) data_get($data, 'payment.amount', 0), 2);
            $status = match (true) {
                $paymentAmount >= $subtotalAmount => Order::STATUS_PAID,
                $paymentAmount > 0 => Order::STATUS_PARTIALLY_PAID,
                default => Order::STATUS_PENDING,
            };
            $isPaid = $status === Order::STATUS_PAID;
            $changeAmount = round(max($paymentAmount - $subtotalAmount, 0), 2);

            $order = Order::query()->create([
                'order_number' => $this->makeOrderNumber(),
                'customer_id' => $data['customer_id'] ?? null,
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'status' => $status,
                'total_quantity' => $totalQuantity,
                'subtotal_amount' => $subtotalAmount,
                'discount_amount' => 0,
                'service_fee_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $subtotalAmount,
                'payment_method' => data_get($data, 'payment.method'),
                'payment_amount' => $paymentAmount,
                'change_amount' => $changeAmount,
                'paid_at' => $isPaid ? now() : null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $order->items()->createMany($orderItems);
            $this->deductTrackedStock($products, $requestedQuantityByProduct, $userId);

            return $order->load('items');
        });

        return [
            'success' => true,
            'message' => "Order {$order->order_number} saved successfully.",
            'data' => $this->transformOrder($order),
        ];
    }

    private function makeOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . now()->format('Ymd-His') . '-' . random_int(100, 999);
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
            'service_fee_amount' => (float) $order->service_fee_amount,
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

        if ($tab === 'today') {
            $query->whereDate('created_at', now()->toDateString());
        }

        if ($tab === 'pending') {
            $query->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PARTIALLY_PAID]);
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
            'total_amount_label' => '$' . number_format((float) $order->total_amount, 2),
            'created_at_label' => 'Retail | ' . $order->created_at?->format('M j, h:i A'),
            'items_count' => (int) ($order->items_count ?? 0),
        ];
    }

    private function transformDetailedOrder(Order $order): array
    {
        $totalAmount = (float) $order->total_amount;
        $paymentAmount = (float) $order->payment_amount;
        $status = $this->paymentAwareStatus($order);
        $balanceDue = $status === Order::STATUS_PAID
            ? 0.0
            : max($totalAmount - $paymentAmount, 0);

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'status_class' => $this->listingStatusClass($status),
            'customer_name' => trim((string) ($order->customer?->name ?? '')) ?: 'Walk-In Customer',
            'payment_method_label' => filled($order->payment_method)
                ? str((string) $order->payment_method)->replace('_', ' ')->title()->toString()
                : 'N/A',
            'subtotal_amount_label' => $this->moneyLabel((float) $order->subtotal_amount),
            'discount_amount_label' => $this->moneyLabel((float) $order->discount_amount),
            'service_fee_amount_label' => $this->moneyLabel((float) $order->service_fee_amount),
            'tax_amount' => (float) $order->tax_amount,
            'tax_amount_label' => $this->moneyLabel((float) $order->tax_amount),
            'total_amount' => $totalAmount,
            'total_amount_label' => $this->moneyLabel($totalAmount),
            'payment_amount_label' => $this->moneyLabel($paymentAmount),
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
                    'tax_detail_label' => $item->product_name . ' (x' . number_format((float) $item->quantity, 3) . ')',
                ])
                ->values(),
        ];
    }

    private function moneyLabel(float $amount): string
    {
        return '$' . number_format($amount, 2);
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
            default => str($status)->replace('_', ' ')->title()->toString(),
        };
    }

    private function listingStatusClass(string $status): string
    {
        return match ($status) {
            Order::STATUS_PAID => 'success',
            Order::STATUS_PARTIALLY_PAID => 'warning',
            Order::STATUS_PENDING => 'warning',
            default => 'secondary',
        };
    }
}
