<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderItem;

/**
 * Shared top-product / category sales queries for admin + employee dashboards.
 * Callers keep their own limits and presentation; this only owns the SQL shape.
 */
final class SalesMixQueries
{
    /**
     * @return list<array{name: string, qty: int, revenue: float}>
     */
    public static function topProducts(DashboardDateRange $range, int $limit = 5): array
    {
        return OrderItem::query()
            ->whereHas('order', self::soldOrdersInRange($range))
            ->selectRaw('product_name, SUM(quantity) as qty, SUM(line_total) as revenue')
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->limit(max(1, $limit))
            ->get()
            ->map(fn ($row) => [
                'name' => (string) $row->product_name,
                'qty' => (int) $row->qty,
                'revenue' => round((float) $row->revenue, 2),
            ])
            ->all();
    }

    /**
     * @return list<array{name: string, revenue: float}>
     */
    public static function salesByCategory(DashboardDateRange $range, int $limit = 5): array
    {
        return OrderItem::query()
            ->whereHas('order', self::soldOrdersInRange($range))
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as name, SUM(order_items.line_total) as revenue')
            ->groupBy('categories.name')
            ->orderByDesc('revenue')
            ->limit(max(1, $limit))
            ->get()
            ->map(fn ($row) => [
                'name' => (string) $row->name,
                'revenue' => round((float) $row->revenue, 2),
            ])
            ->all();
    }

    /**
     * @return \Closure(\Illuminate\Database\Eloquent\Builder): void
     */
    private static function soldOrdersInRange(DashboardDateRange $range): \Closure
    {
        return function ($query) use ($range): void {
            $query->withinRange($range)
                ->where('status', '!=', Order::STATUS_ESTIMATE);
        };
    }
}
