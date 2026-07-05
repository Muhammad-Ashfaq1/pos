<?php

namespace App\Reports;

use App\Models\Order;
use App\Reports\Support\ReportOptions;
use App\Support\Currency;
use Illuminate\Database\Eloquent\Builder;

/**
 * Sales / Orders report. Real sales only — estimates are excluded.
 */
class SalesReport extends ReportDefinition
{
    public function key(): string
    {
        return 'sales';
    }

    public function label(): string
    {
        return __('admin.reports.sales');
    }

    protected function model(): string
    {
        return Order::class;
    }

    protected function baseQuery(): Builder
    {
        return Order::query()
            ->with('customer:id,name,phone')
            ->withCount('items')
            ->where('status', '!=', Order::STATUS_ESTIMATE);
    }

    public function dateColumn(): string
    {
        return 'created_at';
    }

    public function dateColumnOptions(): array
    {
        return [
            'created_at' => __('admin.reports.order_date'),
            'paid_at' => __('admin.reports.paid_date'),
        ];
    }

    public function filters(): array
    {
        return [
            [
                'key' => 'status',
                'label' => __('admin.common.status'),
                'type' => 'select',
                'options' => [
                    Order::STATUS_PENDING => __('admin.common.pending'),
                    Order::STATUS_PARTIALLY_PAID => __('admin.reports.partially_paid'),
                    Order::STATUS_PAID => __('admin.reports.paid'),
                    Order::STATUS_RETURNED => __('admin.reports.returned'),
                ],
                'apply' => fn (Builder $q, $v) => $q->where('status', $v),
            ],
            [
                'key' => 'payment_method',
                'label' => __('admin.orders.payment_method'),
                'type' => 'select',
                'options' => [
                    'cash' => __('admin.common.cash'),
                    'card' => __('admin.common.card'),
                    'check' => __('admin.common.check'),
                ],
                'apply' => fn (Builder $q, $v) => $q->where('payment_method', $v),
            ],
            [
                'key' => 'created_by',
                'label' => __('admin.reports.retailer'),
                'type' => 'select',
                'options' => fn () => ReportOptions::staff(),
                'apply' => fn (Builder $q, $v) => $q->where('created_by', (int) $v),
            ],
            [
                'key' => 'amount_min',
                'label' => __('admin.reports.min_total'),
                'type' => 'number',
                'apply' => fn (Builder $q, $v) => $q->where('total_amount', '>=', (float) $v),
            ],
            [
                'key' => 'amount_max',
                'label' => __('admin.reports.max_total'),
                'type' => 'number',
                'apply' => fn (Builder $q, $v) => $q->where('total_amount', '<=', (float) $v),
            ],
        ];
    }

    public function sortable(): array
    {
        return [
            'order_number' => 'orders.order_number',
            'status' => 'orders.status',
            'total' => 'orders.total_amount',
            'created_at' => 'orders.created_at',
            'paid_at' => 'orders.paid_at',
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'order_number', 'label' => __('admin.reports.order_number'), 'value' => fn (Order $o) => $o->order_number],
            ['key' => 'created_at', 'label' => __('admin.common.date'), 'value' => fn (Order $o) => $o->created_at?->format('d M Y h:i A') ?? '—'],
            ['key' => 'customer', 'label' => __('admin.common.customer'), 'value' => fn (Order $o) => $o->customer?->name ?? '—'],
            ['key' => 'status', 'label' => __('admin.common.status'), 'value' => fn (Order $o) => $this->statusLabel((string) $o->status)],
            ['key' => 'items', 'label' => __('admin.orders.items'), 'align' => 'center', 'value' => fn (Order $o) => (int) ($o->items_count ?? 0)],
            ['key' => 'subtotal', 'label' => __('admin.orders.subtotal'), 'align' => 'end', 'value' => fn (Order $o) => Currency::format((float) $o->subtotal_amount)],
            ['key' => 'discount', 'label' => __('admin.common.discount'), 'align' => 'end', 'value' => fn (Order $o) => Currency::format((float) $o->discount_amount)],
            ['key' => 'tax', 'label' => __('admin.common.tax'), 'align' => 'end', 'value' => fn (Order $o) => Currency::format((float) $o->tax_amount)],
            ['key' => 'total', 'label' => __('admin.common.total'), 'align' => 'end', 'value' => fn (Order $o) => Currency::format((float) $o->total_amount)],
            ['key' => 'paid', 'label' => __('admin.reports.paid'), 'align' => 'end', 'value' => fn (Order $o) => Currency::format((float) $o->payment_amount)],
            ['key' => 'balance', 'label' => __('admin.reports.balance'), 'align' => 'end', 'value' => fn (Order $o) => Currency::format(max((float) $o->total_amount - (float) $o->payment_amount, 0))],
        ];
    }

    public function summary(Builder $query): array
    {
        // toBase()->select([]) drops the base query's orders.* + items_count
        // columns so the aggregate-only SELECT is valid under only_full_group_by.
        $row = (clone $query)
            ->toBase()
            ->select([])
            ->reorder()
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as gross')
            ->selectRaw('COALESCE(SUM(payment_amount), 0) as collected')
            ->selectRaw('COALESCE(SUM(CASE WHEN total_amount > payment_amount THEN total_amount - payment_amount ELSE 0 END), 0) as outstanding')
            ->first();

        return [
            ['label' => __('admin.common.orders'), 'value' => (string) (int) ($row->orders_count ?? 0)],
            ['label' => __('admin.reports.gross_sales'), 'value' => Currency::format((float) ($row->gross ?? 0))],
            ['label' => __('admin.tenant_dashboard.collected'), 'value' => Currency::format((float) ($row->collected ?? 0))],
            ['label' => __('admin.tenant_dashboard.outstanding'), 'value' => Currency::format((float) ($row->outstanding ?? 0))],
        ];
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            Order::STATUS_PENDING => __('admin.common.pending'),
            Order::STATUS_PARTIALLY_PAID => __('admin.reports.partially_paid'),
            Order::STATUS_PAID => __('admin.reports.paid'),
            Order::STATUS_RETURNED => __('admin.reports.returned'),
            default => ucwords(str_replace('_', ' ', $status)),
        };
    }
    protected function applySearch(Builder $query, string $term): void
    {
        $query->where(function (Builder $q) use ($term): void {
            $q->where('orders.order_number', 'like', "%{$term}%")
                ->orWhereHas('customer', fn (Builder $c) => $c->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%"));
        });
    }
}
