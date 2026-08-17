<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\DiscountGroup;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Service;
use App\Models\SubCategory;
use App\Models\Tenant;
use App\Models\Vehicle;
use App\Support\Currency;
use App\Support\DashboardDateRange;
use App\Support\SalesMixQueries;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the analytics payload for the tenant admin dashboard. Every query runs
 * inside the active tenant context, so the BelongsToTenant global scope keeps the
 * numbers isolated to the current shop. Time-based metrics are scoped to the
 * selected date range; catalog/inventory counts reflect current state.
 */
class TenantDashboardService
{
    private DashboardDateRange $range;

    public function metrics(?Tenant $tenant, ?DashboardDateRange $range = null): array
    {
        $this->range = $range ?? DashboardDateRange::fromRequest('month');

        $currency = Currency::code($tenant);
        $symbol = Currency::symbol($currency);

        return [
            'currency' => $currency,
            'currencySymbol' => $symbol,
            'range' => [
                'period' => $this->range->period,
                'label' => $this->range->label,
                'start' => $this->range->start->toDateString(),
                'end' => $this->range->end->toDateString(),
            ],
            'cards' => $this->cards(),
            'revenueTrend' => $this->revenueTrend(),
            'ordersByStatus' => $this->ordersByStatus(),
            'paymentMethods' => $this->paymentMethods(),
            'topProducts' => $this->topProducts(),
            'salesByCategory' => $this->salesByCategory(),
            'customersByType' => $this->customersByType(),
            'revenueBreakdown' => $this->revenueBreakdown(),
            'recentOrders' => $this->recentOrders($symbol),
            'lowStock' => $this->lowStock(),
            'application' => $this->application($tenant, $currency),
        ];
    }

    /** Constrain an order query to the active date range via created_at. */
    private function inRange(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [$this->range->start, $this->range->end]);
    }

    private function cards(): array
    {
        $sales = fn () => Order::query()->where('status', '!=', Order::STATUS_ESTIMATE);

        $totalSales = (float) $this->inRange($sales())->sum('total_amount');
        $collected = (float) $this->inRange($sales())->sum('payment_amount');
        $ordersTotal = $this->inRange($sales())->count();

        // Compare this window's sales against the equal-length window before it.
        $prev = $this->range->previous();
        $salesPrev = (float) $sales()
            ->whereBetween('created_at', [$prev->start, $prev->end])
            ->sum('total_amount');

        $lowStockCount = Product::query()
            ->where('track_inventory', true)
            ->whereColumn('current_stock', '<=', 'reorder_level')
            ->count();

        $estimatesTotal = $this->inRange(Order::query()->where('status', Order::STATUS_ESTIMATE))->count();
        $estimatesValue = (float) $this->inRange(Order::query()->where('status', Order::STATUS_ESTIMATE))->sum('total_amount');

        $itemsSold = (int) OrderItem::query()
            ->whereHas('order', fn ($q) => $this->inRange($q->where('status', '!=', Order::STATUS_ESTIMATE)))
            ->sum('quantity');

        return [
            'total_sales' => round($totalSales, 2),
            'collected' => round($collected, 2),
            'outstanding' => round(max(0, $totalSales - $collected), 2),
            'orders_total' => $ordersTotal,
            'orders_this_month' => $ordersTotal,
            'sales_this_month' => round($totalSales, 2),
            'sales_month_change' => $this->percentChange($totalSales, $salesPrev),
            'new_customers' => $this->inRange(Customer::query())->count(),
            'avg_order_value' => round($ordersTotal > 0 ? $totalSales / $ordersTotal : 0, 2),
            'customers_total' => Customer::query()->count(),
            'products_total' => Product::query()->count(),
            'low_stock_count' => $lowStockCount,
            'items_sold' => $itemsSold,
            'estimates_total' => $estimatesTotal,
            'estimates_value' => round($estimatesValue, 2),
        ];
    }

    private function revenueTrend(): array
    {
        $daily = $this->range->isDailyTrend();
        $keyFormat = $daily ? 'Y-m-d' : 'Y-m';
        $labelFormat = $daily ? 'd M' : 'M Y';

        // Build the ordered list of buckets that span the selected range.
        $buckets = collect();
        $cursor = $daily ? $this->range->start->copy()->startOfDay() : $this->range->start->copy()->startOfMonth();
        $last = $this->range->end;

        while ($cursor->lessThanOrEqualTo($last)) {
            $buckets->push($cursor->copy());
            $daily ? $cursor->addDay() : $cursor->addMonthNoOverflow();
        }

        $orders = $this->inRange(Order::query()->where('status', '!=', Order::STATUS_ESTIMATE))
            ->get(['total_amount', 'created_at']);

        $grouped = $orders->groupBy(fn (Order $o) => $o->created_at->format($keyFormat));

        return [
            'labels' => $buckets->map(fn (Carbon $b) => $b->format($labelFormat))->all(),
            'revenue' => $buckets->map(fn (Carbon $b) => round((float) ($grouped->get($b->format($keyFormat))?->sum('total_amount') ?? 0), 2))->all(),
            'orders' => $buckets->map(fn (Carbon $b) => $grouped->get($b->format($keyFormat))?->count() ?? 0)->all(),
        ];
    }

    private function ordersByStatus(): array
    {
        $counts = $this->inRange(Order::query())
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $labels = [
            Order::STATUS_PAID => 'Paid',
            Order::STATUS_PARTIALLY_PAID => 'Partially Paid',
            Order::STATUS_PENDING => 'Pending',
            Order::STATUS_ESTIMATE => 'Estimate',
        ];

        return collect($labels)
            ->map(fn (string $label, string $key) => [
                'label' => $label,
                'count' => (int) ($counts[$key] ?? 0),
            ])
            ->values()
            ->all();
    }

    private function paymentMethods(): array
    {
        return $this->inRange(Order::query())
            ->where('status', '!=', Order::STATUS_ESTIMATE)
            ->whereNotNull('payment_method')
            ->selectRaw('payment_method, COUNT(*) as orders, SUM(payment_amount) as amount')
            ->groupBy('payment_method')
            ->orderByDesc('amount')
            ->get()
            ->map(fn ($row) => [
                'label' => ucfirst((string) $row->payment_method),
                'orders' => (int) $row->orders,
                'amount' => round((float) $row->amount, 2),
            ])
            ->all();
    }

    private function topProducts(): array
    {
        return SalesMixQueries::topProducts($this->range, 7);
    }

    private function salesByCategory(): array
    {
        return SalesMixQueries::salesByCategory($this->range, 6);
    }

    private function customersByType(): array
    {
        $counts = Customer::query()
            ->selectRaw('customer_type, COUNT(*) as aggregate')
            ->groupBy('customer_type')
            ->pluck('aggregate', 'customer_type');

        return collect(Customer::typeOptions())
            ->map(fn (string $label, string $key) => [
                'label' => $label,
                'count' => (int) ($counts[$key] ?? 0),
            ])
            ->values()
            ->all();
    }

    private function revenueBreakdown(): array
    {
        $row = $this->inRange(Order::query())
            ->where('status', '!=', Order::STATUS_ESTIMATE)
            ->selectRaw('SUM(subtotal_amount) as subtotal, SUM(discount_amount) as discount, SUM(tax_amount) as tax, SUM(service_fee_amount) as fees')
            ->first();

        return [
            'subtotal' => round((float) ($row->subtotal ?? 0), 2),
            'discount' => round((float) ($row->discount ?? 0), 2),
            'tax' => round((float) ($row->tax ?? 0), 2),
            'fees' => round((float) ($row->fees ?? 0), 2),
        ];
    }

    private function recentOrders(string $symbol): Collection
    {
        return $this->inRange(Order::query())
            ->with('customer:id,name')
            ->latest()
            ->limit(8)
            ->get(['id', 'order_number', 'customer_id', 'status', 'total_amount', 'payment_amount', 'created_at'])
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer' => $order->customer?->name ?? 'Walk-in',
                'status' => $order->status,
                'status_label' => $this->statusLabel($order->status),
                'status_class' => $this->statusClass($order->status),
                'total' => $symbol.number_format((float) $order->total_amount, 2),
                'created_at' => $order->created_at?->format('d M Y, H:i'),
            ]);
    }

    private function lowStock(): Collection
    {
        return Product::query()
            ->where('track_inventory', true)
            ->whereColumn('current_stock', '<=', 'reorder_level')
            ->orderBy('current_stock')
            ->limit(8)
            ->get(['id', 'name', 'sku', 'current_stock', 'reorder_level', 'unit'])
            ->map(fn (Product $p) => [
                'name' => $p->name,
                'sku' => $p->sku,
                'current_stock' => (int) $p->current_stock,
                'reorder_level' => (int) $p->reorder_level,
                'unit' => $p->unit,
                'is_out' => (int) $p->current_stock <= 0,
            ]);
    }

    private function application(?Tenant $tenant, string $currency): array
    {
        return [
            'name' => $tenant?->display_name ?? $tenant?->name ?? 'Shop',
            'status' => $tenant?->status?->value ?? 'unknown',
            'onboarding' => $tenant?->onboarding_state ?? 'not_started',
            'currency' => $currency,
            'timezone' => (string) ($tenant?->setting('regional.timezone', 'UTC') ?? 'UTC'),
            'created_at' => $tenant?->created_at?->format('d M Y'),
            'team_members' => $tenant?->users()->count() ?? 0,
            'catalog' => [
                'Categories' => Category::query()->count(),
                'Sub Categories' => SubCategory::query()->count(),
                'Product Types' => ProductType::query()->count(),
                'Products' => Product::query()->count(),
                'Services' => Service::query()->count(),
                'Discounts' => Discount::query()->count(),
                'Discount Groups' => DiscountGroup::query()->count(),
                'Customers' => Customer::query()->count(),
                'Vehicles' => Vehicle::query()->count(),
                'Orders' => Order::query()->where('status', '!=', Order::STATUS_ESTIMATE)->count(),
                'Estimates' => Order::query()->where('status', Order::STATUS_ESTIMATE)->count(),
            ],
        ];
    }

    private function percentChange(float $current, float $previous): float
    {
        if ($previous <= 0.0) {
            return $current > 0.0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            Order::STATUS_PAID => 'Paid',
            Order::STATUS_PARTIALLY_PAID => 'Partially Paid',
            Order::STATUS_ESTIMATE => 'Estimate',
            default => 'Pending',
        };
    }

    private function statusClass(string $status): string
    {
        return match ($status) {
            Order::STATUS_PAID => 'bg-label-success',
            Order::STATUS_PARTIALLY_PAID => 'bg-label-warning',
            Order::STATUS_ESTIMATE => 'bg-label-info',
            default => 'bg-label-secondary',
        };
    }
}
