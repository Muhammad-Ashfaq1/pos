<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Catalog + preference helpers for employee dashboard Product Mix KPI cards.
 */
final class ProductMixCards
{
    public const MAX_SELECTED = 4;

    /**
     * @return list<string>
     */
    public static function defaults(): array
    {
        return [
            'total_sales',
            'orders_completed',
            'orders_incomplete',
            'items_sold',
        ];
    }

    /**
     * @return array<string, array{
     *     key: string,
     *     group: string,
     *     group_label: string,
     *     label: string,
     *     description: string,
     *     icon: string,
     *     tone: string,
     *     gradient: string,
     *     preview_meta: string,
     *     format: string
     * }>
     */
    public static function catalog(): array
    {
        return [
            'available_products' => [
                'key' => 'available_products',
                'group' => 'product',
                'group_label' => 'Product Metrics',
                'label' => 'Available Products',
                'description' => 'Active products in the catalog',
                'icon' => 'tabler-package',
                'tone' => 'primary',
                'gradient' => 'purple',
                'preview_meta' => 'Active catalog',
                'format' => 'number',
            ],
            'top_selling_products' => [
                'key' => 'top_selling_products',
                'group' => 'product',
                'group_label' => 'Product Metrics',
                'label' => 'Top Selling Products',
                'description' => 'Best seller for the selected period',
                'icon' => 'tabler-file-analytics',
                'tone' => 'success',
                'gradient' => 'blue',
                'preview_meta' => 'Top seller',
                'format' => 'number',
            ],
            'low_stock_products' => [
                'key' => 'low_stock_products',
                'group' => 'product',
                'group_label' => 'Product Metrics',
                'label' => 'Low Stock Products',
                'description' => 'Products at or below reorder level',
                'icon' => 'tabler-alert-triangle',
                'tone' => 'warning',
                'gradient' => 'orange',
                'preview_meta' => 'Need reorder',
                'format' => 'number',
            ],
            'total_stock_units' => [
                'key' => 'total_stock_units',
                'group' => 'product',
                'group_label' => 'Product Metrics',
                'label' => 'Total Products in Stock',
                'description' => 'Inventory units currently on hand',
                'icon' => 'tabler-packages',
                'tone' => 'info',
                'gradient' => 'violet',
                'preview_meta' => 'Units on hand',
                'format' => 'number',
            ],
            'total_sales' => [
                'key' => 'total_sales',
                'group' => 'order',
                'group_label' => 'Order Metrics',
                'label' => 'Sales',
                'description' => 'Paid sales for the selected period',
                'icon' => 'tabler-currency-dollar',
                'tone' => 'success',
                'gradient' => 'green',
                'preview_meta' => 'Fully collected',
                'format' => 'money',
            ],
            'orders_completed' => [
                'key' => 'orders_completed',
                'group' => 'order',
                'group_label' => 'Order Metrics',
                'label' => 'Completed Orders',
                'description' => 'Paid orders for the selected period',
                'icon' => 'tabler-calendar-event',
                'tone' => 'primary',
                'gradient' => 'blue',
                'preview_meta' => 'Paid orders',
                'format' => 'number',
            ],
            'orders_incomplete' => [
                'key' => 'orders_incomplete',
                'group' => 'order',
                'group_label' => 'Order Metrics',
                'label' => 'Incomplete Orders',
                'description' => 'Open, pending, and estimate orders',
                'icon' => 'tabler-user',
                'tone' => 'warning',
                'gradient' => 'pink',
                'preview_meta' => 'Open orders',
                'format' => 'number',
            ],
            'items_sold' => [
                'key' => 'items_sold',
                'group' => 'order',
                'group_label' => 'Order Metrics',
                'label' => 'Items Sold',
                'description' => 'Units sold in the selected period',
                'icon' => 'tabler-shopping-cart',
                'tone' => 'info',
                'gradient' => 'violet',
                'preview_meta' => 'Units moved',
                'format' => 'number',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::catalog());
    }

    /**
     * Chart color palette shared by dashboard product-mix visuals.
     *
     * @return list<string>
     */
    public static function chartPalette(): array
    {
        return ['#7367F0', '#28C76F', '#FF9F43', '#00CFE8', '#EA5455', '#A8AAAE', '#826AF9', '#FFB400'];
    }

    /**
     * @return list<string>
     */
    public static function selectedFor(?User $user): array
    {
        $stored = $user?->employee_product_mix_cards;

        if (! is_array($stored) || $stored === []) {
            return self::defaults();
        }

        $selected = self::sanitize($stored);

        return $selected !== [] ? $selected : self::defaults();
    }

    /**
     * @param  list<string>  $keys
     * @return list<string>
     */
    public static function sanitize(array $keys): array
    {
        $allowed = self::keys();

        return collect($keys)
            ->filter(fn ($key): bool => is_string($key) && in_array($key, $allowed, true))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Default card footnotes from the catalog, with optional per-key overrides.
     *
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    public static function meta(array $overrides = []): array
    {
        $meta = [];

        foreach (self::catalog() as $key => $card) {
            $meta[$key] = (string) ($card['preview_meta'] ?? '');
        }

        foreach ($overrides as $key => $value) {
            if ($value === null) {
                continue;
            }

            $meta[$key] = (string) $value;
        }

        return $meta;
    }

    /**
     * @return Collection<string, Collection<int, array<string, mixed>>>
     */
    public static function groupedCatalog(): Collection
    {
        $order = ['order' => 0, 'product' => 1];

        return collect(self::catalog())
            ->groupBy('group_label')
            ->sortBy(fn (Collection $cards): int => $order[$cards->first()['group'] ?? 'product'] ?? 99);
    }

    /**
     * @param  list<string>  $selectedKeys
     * @param  array<string, mixed>  $stats
     * @return list<array<string, mixed>>
     */
    public static function summaryCards(array $selectedKeys, array $stats): array
    {
        $catalog = self::catalog();
        $symbol = (string) ($stats['currency_symbol'] ?? Currency::symbol());
        $meta = is_array($stats['meta'] ?? null) ? $stats['meta'] : [];

        return collect($selectedKeys)
            ->map(function (string $key) use ($catalog, $stats, $meta, $symbol): ?array {
                $def = $catalog[$key] ?? null;
                if (! $def) {
                    return null;
                }

                $raw = $stats[$key] ?? 0;
                $format = $def['format'];

                return [
                    'key' => $key,
                    'label' => $def['label'],
                    'icon' => $def['icon'],
                    'tone' => $def['tone'],
                    'gradient' => $def['gradient'] ?? 'blue',
                    'preview_meta' => $def['preview_meta'] ?? '',
                    'format' => $format,
                    'value' => $format === 'money'
                        ? $symbol.number_format((float) $raw, 2)
                        : number_format((float) $raw),
                    'meta' => (string) ($meta[$key] ?? ''),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
