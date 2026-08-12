<?php

namespace App\Http\Controllers\Employee;

use App\Models\Order;
use App\Models\OrderItem;
use App\Support\Currency;
use App\Support\DashboardDateRange;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PanelController
{
    public function dashboard(): View
    {
        // Weekly trend data for graph
        $days = collect(range(0, 6))
            ->map(fn (int $i) => Carbon::today()->subDays(6 - $i));

        $orders = Order::query()
            ->where('created_at', '>=', $days->first())
            ->get(['total_amount', 'status', 'created_at']);

        $byDay = $orders->groupBy(fn ($o) => $o->created_at->format('Y-m-d'));

        $trendLabels = $days->map(fn ($d) => $d->format('D'))->all();

        $trendSales = $days->map(fn ($d) => round((float) ($byDay->get($d->format('Y-m-d'))?->where('status', '!=', Order::STATUS_ESTIMATE)->sum('total_amount') ?? 0), 2))->all();

        $trendEstimates = $days->map(fn ($d) => round((float) ($byDay->get($d->format('Y-m-d'))?->where('status', Order::STATUS_ESTIMATE)->sum('total_amount') ?? 0), 2))->all();

        $range = DashboardDateRange::fromRequest('today');

        return view('employee.dashboard', array_merge($this->productMixStats($range), [
            'product_mix_period' => $range->period,
            'product_mix_period_label' => $range->label,
            'trend_labels' => $trendLabels,
            'trend_sales' => $trendSales,
            'trend_estimates' => $trendEstimates,
        ]));
    }

    public function productMix(Request $request): JsonResponse
    {
        $range = $this->resolveProductMixRange($request->query('period'));

        return response()->json($this->productMixStats($range));
    }

    /**
     * @return array{
     *     period: string,
     *     period_label: string,
     *     currency_symbol: string,
     *     total_sales: float,
     *     outstanding: float,
     *     orders_completed: int,
     *     orders_incomplete: int,
     *     items_sold: int,
     *     meta: array{total_sales: string, orders_completed: string, orders_incomplete: string, items_sold: string},
     *     top_products: list<array{name: string, qty: int, revenue: float}>,
     *     sales_by_category: list<array{name: string, revenue: float}>
     * }
     */
    private function productMixStats(DashboardDateRange $range): array
    {
        $ordersInRange = Order::query()->withinRange($range);
        $periodLabel = $range->label;
        $symbol = Currency::symbol();

        $totalSales = (float) (clone $ordersInRange)
            ->where('status', Order::STATUS_PAID)
            ->sum('total_amount');

        $completed = (clone $ordersInRange)
            ->where('status', Order::STATUS_PAID)
            ->count();

        $incomplete = (clone $ordersInRange)
            ->whereIn('status', [
                Order::STATUS_PENDING,
                Order::STATUS_PARTIALLY_PAID,
                Order::STATUS_ESTIMATE,
            ])
            ->count();

        $outstanding = (float) (clone $ordersInRange)
            ->where('status', '!=', Order::STATUS_ESTIMATE)
            ->selectRaw('COALESCE(SUM(CASE WHEN total_amount > payment_amount THEN total_amount - payment_amount ELSE 0 END), 0) as outstanding')
            ->value('outstanding');

        $itemsSold = (int) OrderItem::query()
            ->whereHas('order', function ($query) use ($range) {
                $query->withinRange($range)
                    ->where('status', '!=', Order::STATUS_ESTIMATE);
            })
            ->sum('quantity');

        $orderItemsInRange = fn ($query) => $query->withinRange($range)
            ->where('status', '!=', Order::STATUS_ESTIMATE);

        $topProducts = OrderItem::query()
            ->whereHas('order', $orderItemsInRange)
            ->selectRaw('product_name, SUM(quantity) as qty, SUM(line_total) as revenue')
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'name' => (string) $row->product_name,
                'qty' => (int) $row->qty,
                'revenue' => round((float) $row->revenue, 2),
            ])
            ->all();

        $salesByCategory = OrderItem::query()
            ->whereHas('order', $orderItemsInRange)
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as name, SUM(order_items.line_total) as revenue')
            ->groupBy('categories.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'name' => (string) $row->name,
                'revenue' => round((float) $row->revenue, 2),
            ])
            ->all();

        return [
            'period' => $range->period,
            'period_label' => $periodLabel,
            'currency_symbol' => $symbol,
            'total_sales' => round($totalSales, 2),
            'outstanding' => round($outstanding, 2),
            'orders_completed' => $completed,
            'orders_incomplete' => $incomplete,
            'items_sold' => $itemsSold,
            'meta' => [
                'total_sales' => $outstanding > 0
                    ? 'Due '.$symbol.number_format($outstanding, 2)
                    : 'Fully collected',
                'orders_completed' => 'Paid orders',
                'orders_incomplete' => 'Open orders',
                'items_sold' => 'Units moved',
            ],
            'top_products' => $topProducts,
            'sales_by_category' => $salesByCategory,
        ];
    }

    private function resolveProductMixRange(?string $period): DashboardDateRange
    {
        $period = is_string($period) ? strtolower(trim($period)) : 'today';
        $now = Carbon::now();

        return match ($period) {
            'yesterday' => DashboardDateRange::fromRequest('yesterday'),
            'week', 'this_week' => new DashboardDateRange(
                'week',
                $now->copy()->startOfWeek(),
                $now->copy()->endOfDay(),
                'This Week'
            ),
            'last_week' => new DashboardDateRange(
                'last_week',
                $now->copy()->subWeek()->startOfWeek(),
                $now->copy()->subWeek()->endOfWeek(),
                'Last Week'
            ),
            'month', 'this_month' => DashboardDateRange::fromRequest('month'),
            'last_month' => new DashboardDateRange(
                'last_month',
                $now->copy()->subMonthNoOverflow()->startOfMonth(),
                $now->copy()->subMonthNoOverflow()->endOfMonth(),
                'Last Month'
            ),
            'year' => DashboardDateRange::fromRequest('year'),
            default => DashboardDateRange::fromRequest('today'),
        };
    }

    public function newOrder(): View
    {
        return view('employee.order.new-order');
    }
}
