<?php

namespace App\Http\Controllers\Employee;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Support\Currency;
use App\Support\DashboardDateRange;
use App\Support\ProductMixCards;
use App\Support\SalesMixQueries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PanelController
{
    public function dashboard(Request $request): View
    {
        $range = $this->resolveProductMixRange(
            $request->query('period'),
            $request->query('start'),
            $request->query('end'),
        );

        ['labels' => $trendLabels, 'sales' => $trendSales, 'estimates' => $trendEstimates] = $this->trendForRange($range);

        return view('employee.dashboard', array_merge($this->productMixStats($range, $request->user()), [
            'product_mix_period' => $range->period,
            'product_mix_period_label' => $range->label,
            'dashboard_range' => $this->rangePayload($range),
            'trend_labels' => $trendLabels,
            'trend_sales' => $trendSales,
            'trend_estimates' => $trendEstimates,
        ]));
    }

    public function productMix(Request $request): JsonResponse
    {
        $range = $this->resolveProductMixRange(
            $request->query('period'),
            $request->query('start'),
            $request->query('end'),
        );

        return response()->json(array_merge(
            $this->productMixStats($range, $request->user()),
            ['dashboard_range' => $this->rangePayload($range)],
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function productMixStats(DashboardDateRange $range, mixed $user = null): array
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

        $topProducts = SalesMixQueries::topProducts($range, 5);
        $salesByCategory = SalesMixQueries::salesByCategory($range, 5);

        $trackedProducts = Product::query()->where('track_inventory', true);
        $availableProducts = (int) Product::query()->where('is_active', true)->count();
        $lowStockProducts = (int) (clone $trackedProducts)
            ->whereColumn('current_stock', '<=', 'reorder_level')
            ->count();
        $totalStockUnits = (int) (clone $trackedProducts)->sum('current_stock');
        $topSellerQty = (int) (($topProducts[0]['qty'] ?? 0));
        $topSellerName = (string) ($topProducts[0]['name'] ?? 'No sales yet');

        $stats = [
            'period' => $range->period,
            'period_label' => $periodLabel,
            'currency_symbol' => $symbol,
            'total_sales' => round($totalSales, 2),
            'outstanding' => round($outstanding, 2),
            'orders_completed' => $completed,
            'orders_incomplete' => $incomplete,
            'items_sold' => $itemsSold,
            'available_products' => $availableProducts,
            'top_selling_products' => $topSellerQty,
            'low_stock_products' => $lowStockProducts,
            'total_stock_units' => $totalStockUnits,
            'meta' => ProductMixCards::meta([
                'total_sales' => $outstanding > 0
                    ? 'Due '.$symbol.number_format($outstanding, 2)
                    : null,
                'top_selling_products' => $topSellerName,
            ]),
            'top_products' => $topProducts,
            'sales_by_category' => $salesByCategory,
        ];

        $selectedKeys = ProductMixCards::selectedFor($user instanceof User ? $user : null);
        $stats['selected_card_keys'] = $selectedKeys;
        $stats['summary_cards'] = ProductMixCards::summaryCards($selectedKeys, $stats);
        $stats['trend'] = $this->trendForRange($range);

        return $stats;
    }

    private function resolveProductMixRange(?string $period, ?string $start = null, ?string $end = null): DashboardDateRange
    {
        $period = is_string($period) ? strtolower(trim($period)) : 'today';
        $now = Carbon::now();

        if ($period === 'custom') {
            return DashboardDateRange::fromRequest('custom', $start, $end);
        }

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

    /**
     * @return array{labels: list<string>, sales: list<float>, estimates: list<float>}
     */
    private function trendForRange(DashboardDateRange $range): array
    {
        $orders = Order::query()
            ->withinRange($range)
            ->get(['total_amount', 'status', 'created_at']);

        if ($range->isDailyTrend()) {
            $days = collect();
            $cursor = $range->start->copy()->startOfDay();

            while ($cursor->lte($range->end)) {
                $days->push($cursor->copy());
                $cursor->addDay();
            }

            $byDay = $orders->groupBy(fn ($o) => $o->created_at->format('Y-m-d'));

            return [
                'labels' => $days->map(fn ($d) => $d->format('d M'))->all(),
                'sales' => $days->map(fn ($d) => round((float) ($byDay->get($d->format('Y-m-d'))?->where('status', '!=', Order::STATUS_ESTIMATE)->sum('total_amount') ?? 0), 2))->all(),
                'estimates' => $days->map(fn ($d) => round((float) ($byDay->get($d->format('Y-m-d'))?->where('status', Order::STATUS_ESTIMATE)->sum('total_amount') ?? 0), 2))->all(),
            ];
        }

        $months = collect();
        $cursor = $range->start->copy()->startOfMonth();

        while ($cursor->lte($range->end)) {
            $months->push($cursor->copy());
            $cursor->addMonth();
        }

        $byMonth = $orders->groupBy(fn ($o) => $o->created_at->format('Y-m'));

        return [
            'labels' => $months->map(fn ($d) => $d->format('M Y'))->all(),
            'sales' => $months->map(fn ($d) => round((float) ($byMonth->get($d->format('Y-m'))?->where('status', '!=', Order::STATUS_ESTIMATE)->sum('total_amount') ?? 0), 2))->all(),
            'estimates' => $months->map(fn ($d) => round((float) ($byMonth->get($d->format('Y-m'))?->where('status', Order::STATUS_ESTIMATE)->sum('total_amount') ?? 0), 2))->all(),
        ];
    }

    /**
     * @return array{period: string, label: string, start: string, end: string}
     */
    private function rangePayload(DashboardDateRange $range): array
    {
        return [
            'period' => $range->period,
            'label' => $range->label,
            'start' => $range->start->toDateString(),
            'end' => $range->end->toDateString(),
        ];
    }

    public function newOrder(): View
    {
        return view('employee.order.new-order');
    }
}
