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
        return 'Sales';
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
            'created_at' => 'Order Date',
            'paid_at' => 'Paid Date',
        ];
    }

    public function filters(): array
    {
        return [
            [
                'key' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'options' => [
                    Order::STATUS_PENDING => 'Pending',
                    Order::STATUS_PARTIALLY_PAID => 'Partially Paid',
                    Order::STATUS_PAID => 'Paid',
                    Order::STATUS_RETURNED => 'Returned',
                ],
                'apply' => fn (Builder $q, $v) => $q->where('status', $v),
            ],
            [
                'key' => 'payment_method',
                'label' => 'Payment Method',
                'type' => 'select',
                'options' => ['cash' => 'Cash', 'card' => 'Card', 'check' => 'Check'],
                'apply' => fn (Builder $q, $v) => $q->where('payment_method', $v),
            ],
            [
                'key' => 'created_by',
                'label' => 'Retailer',
                'type' => 'select',
                'options' => fn () => ReportOptions::staff(),
                'apply' => fn (Builder $q, $v) => $q->where('created_by', (int) $v),
            ],
            [
                'key' => 'amount_min',
                'label' => 'Min Total',
                'type' => 'number',
                'apply' => fn (Builder $q, $v) => $q->where('total_amount', '>=', (float) $v),
            ],
            [
                'key' => 'amount_max',
                'label' => 'Max Total',
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
            ['key' => 'order_number', 'label' => 'Order #', 'value' => fn (Order $o) => $o->order_number],
            ['key' => 'created_at', 'label' => 'Date', 'value' => fn (Order $o) => $o->created_at?->format('d M Y h:i A') ?? '—'],
            ['key' => 'customer', 'label' => 'Customer', 'value' => fn (Order $o) => $o->customer?->name ?? '—'],
            ['key' => 'status', 'label' => 'Status', 'value' => fn (Order $o) => ucwords(str_replace('_', ' ', (string) $o->status))],
            ['key' => 'items', 'label' => 'Items', 'align' => 'center', 'value' => fn (Order $o) => (int) ($o->items_count ?? 0)],
            ['key' => 'subtotal', 'label' => 'Subtotal', 'align' => 'end', 'value' => fn (Order $o) => Currency::format((float) $o->subtotal_amount)],
            ['key' => 'discount', 'label' => 'Discount', 'align' => 'end', 'value' => fn (Order $o) => Currency::format((float) $o->discount_amount)],
            ['key' => 'tax', 'label' => 'Tax', 'align' => 'end', 'value' => fn (Order $o) => Currency::format((float) $o->tax_amount)],
            ['key' => 'total', 'label' => 'Total', 'align' => 'end', 'value' => fn (Order $o) => Currency::format((float) $o->total_amount)],
            ['key' => 'paid', 'label' => 'Paid', 'align' => 'end', 'value' => fn (Order $o) => Currency::format((float) $o->payment_amount)],
            ['key' => 'balance', 'label' => 'Balance', 'align' => 'end', 'value' => fn (Order $o) => Currency::format(max((float) $o->total_amount - (float) $o->payment_amount, 0))],
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
            ['label' => 'Orders', 'value' => (string) (int) ($row->orders_count ?? 0)],
            ['label' => 'Gross Sales', 'value' => Currency::format((float) ($row->gross ?? 0))],
            ['label' => 'Collected', 'value' => Currency::format((float) ($row->collected ?? 0))],
            ['label' => 'Outstanding', 'value' => Currency::format((float) ($row->outstanding ?? 0))],
        ];
    }

    protected function applySearch(Builder $query, string $term): void
    {
        $query->where(function (Builder $q) use ($term): void {
            $q->where('orders.order_number', 'like', "%{$term}%")
                ->orWhereHas('customer', fn (Builder $c) => $c->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%"));
        });
    }
}
