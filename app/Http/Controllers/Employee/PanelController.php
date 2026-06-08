<?php

namespace App\Http\Controllers\Employee;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PanelController
{
    public function dashboard(): View
    {
        $today = Carbon::today();

        $ordersCompletedToday = Order::query()
            ->whereDate('created_at', $today)
            ->where('status', Order::STATUS_PAID)
            ->count();

        $ordersIncompleteToday = Order::query()
            ->whereDate('created_at', $today)
            ->whereIn('status', [
                Order::STATUS_PENDING,
                Order::STATUS_PARTIALLY_PAID,
                Order::STATUS_ESTIMATE,
            ])
            ->count();

        $productsAvailable = Product::query()
            ->where('is_active', true)
            ->count();

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

        return view('employee.dashboard', [
            'orders_completed_today' => $ordersCompletedToday,
            'orders_incomplete_today' => $ordersIncompleteToday,
            'products_available' => $productsAvailable,
            'trend_labels' => $trendLabels,
            'trend_sales' => $trendSales,
            'trend_estimates' => $trendEstimates,
        ]);
    }

    public function newOrder(): View
    {
        return view('employee.order.new-order');
    }
}
