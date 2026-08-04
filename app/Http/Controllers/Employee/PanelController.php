<?php

namespace App\Http\Controllers\Employee;

use App\Models\Order;
use App\Models\OrderItem;
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
     *     orders_completed_today: int,
     *     orders_incomplete_today: int,
     *     products_available: int,
     *     meta: array{orders_completed_today: string, orders_incomplete_today: string, products_available: string}
     * }
     */
    private function productMixStats(DashboardDateRange $range): array
    {
        $ordersInRange = Order::query()->withinRange($range);

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

        $productsAvailable = (int) OrderItem::query()
            ->whereHas('order', function ($query) use ($range) {
                $query->withinRange($range);
            })
            ->distinct()
            ->count('product_id');

        $periodLabel = $range->label;

        return [
            'period' => $range->period,
            'period_label' => $periodLabel,
            'orders_completed_today' => $completed,
            'orders_incomplete_today' => $incomplete,
            'products_available' => $productsAvailable,
            'meta' => [
                'orders_completed_today' => 'Completed '.$periodLabel,
                'orders_incomplete_today' => 'Incomplete '.$periodLabel,
                'products_available' => 'In orders '.$periodLabel,
            ],
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
