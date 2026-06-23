<?php

namespace App\Reports;

use App\Models\Product;
use App\Reports\Support\ReportOptions;
use App\Support\Currency;
use Illuminate\Database\Eloquent\Builder;

/**
 * Products / Inventory report — catalog with stock levels and low-stock flags.
 */
class ProductsReport extends ReportDefinition
{
    public function key(): string
    {
        return 'products';
    }

    public function label(): string
    {
        return 'Products';
    }

    protected function model(): string
    {
        return Product::class;
    }

    protected function baseQuery(): Builder
    {
        return Product::query()->with('category:id,name');
    }

    public function dateColumnOptions(): array
    {
        return ['created_at' => 'Created Date'];
    }

    public function filters(): array
    {
        return [
            [
                'key' => 'category_id',
                'label' => 'Category',
                'type' => 'select',
                'options' => fn () => ReportOptions::categories(),
                'apply' => fn (Builder $q, $v) => $q->where('category_id', (int) $v),
            ],
            [
                'key' => 'is_active',
                'label' => 'Status',
                'type' => 'select',
                'options' => ['1' => 'Active', '0' => 'Inactive'],
                'apply' => fn (Builder $q, $v) => $q->where('is_active', (bool) (int) $v),
            ],
            [
                'key' => 'track_inventory',
                'label' => 'Tracked',
                'type' => 'select',
                'options' => ['1' => 'Tracked', '0' => 'Not Tracked'],
                'apply' => fn (Builder $q, $v) => $q->where('track_inventory', (bool) (int) $v),
            ],
            [
                'key' => 'low_stock',
                'label' => 'Low Stock Only',
                'type' => 'boolean',
                'apply' => fn (Builder $q, $v) => (bool) (int) $v
                    ? $q->where('track_inventory', true)->whereColumn('current_stock', '<=', 'minimum_stock_level')
                    : $q,
            ],
        ];
    }

    public function sortable(): array
    {
        return [
            'name' => 'products.name',
            'sku' => 'products.sku',
            'price' => 'products.sale_price',
            'stock' => 'products.current_stock',
            'created_at' => 'products.created_at',
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'name', 'label' => 'Product', 'value' => fn (Product $p) => $p->name],
            ['key' => 'sku', 'label' => 'SKU', 'value' => fn (Product $p) => $p->sku ?? '—'],
            ['key' => 'category', 'label' => 'Category', 'value' => fn (Product $p) => $p->category?->name ?? '—'],
            ['key' => 'price', 'label' => 'Price', 'align' => 'end', 'value' => fn (Product $p) => Currency::format((float) $p->sale_price)],
            ['key' => 'stock', 'label' => 'Stock', 'align' => 'center', 'value' => fn (Product $p) => $p->track_inventory ? (int) $p->current_stock : '—'],
            ['key' => 'min_stock', 'label' => 'Min', 'align' => 'center', 'value' => fn (Product $p) => $p->track_inventory ? (int) $p->minimum_stock_level : '—'],
            ['key' => 'low_stock', 'label' => 'Low Stock', 'align' => 'center', 'value' => fn (Product $p) => $p->track_inventory && $p->current_stock <= $p->minimum_stock_level ? 'Yes' : 'No'],
            ['key' => 'status', 'label' => 'Status', 'align' => 'center', 'value' => fn (Product $p) => $p->is_active ? 'Active' : 'Inactive'],
        ];
    }

    public function summary(Builder $query): array
    {
        $row = (clone $query)
            ->toBase()
            ->select([])
            ->reorder()
            ->selectRaw('COUNT(*) as products_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN track_inventory AND current_stock <= minimum_stock_level THEN 1 ELSE 0 END), 0) as low_stock_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN track_inventory THEN current_stock * cost_price ELSE 0 END), 0) as stock_value')
            ->first();

        return [
            ['label' => 'Products', 'value' => (string) (int) ($row->products_count ?? 0)],
            ['label' => 'Low Stock', 'value' => (string) (int) ($row->low_stock_count ?? 0)],
            ['label' => 'Stock Value', 'value' => Currency::format((float) ($row->stock_value ?? 0))],
        ];
    }

    protected function applySearch(Builder $query, string $term): void
    {
        $query->search($term);
    }
}
