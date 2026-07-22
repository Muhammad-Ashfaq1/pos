<?php

namespace App\Http\Controllers\Employee;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
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

        return view('employee.dashboard', array_merge($this->productMixStats(), [
            'trend_labels' => $trendLabels,
            'trend_sales' => $trendSales,
            'trend_estimates' => $trendEstimates,
        ]));
    }

    public function productMix(): JsonResponse
    {
        return response()->json($this->productMixStats());
    }

    /**
     * @return array{orders_completed_today: int, orders_incomplete_today: int, products_available: int}
     */
    private function productMixStats(): array
    {
        $today = Carbon::today();

        return [
            'orders_completed_today' => Order::query()
                ->whereDate('created_at', $today)
                ->where('status', Order::STATUS_PAID)
                ->count(),
            'orders_incomplete_today' => Order::query()
                ->whereDate('created_at', $today)
                ->whereIn('status', [
                    Order::STATUS_PENDING,
                    Order::STATUS_PARTIALLY_PAID,
                    Order::STATUS_ESTIMATE,
                ])
                ->count(),
            'products_available' => Product::query()
                ->where('is_active', true)
                ->count(),
        ];
    }

    public function newOrder(): View
    {
        return view('employee.order.new-order');
    }
}
