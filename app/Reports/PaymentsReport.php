<?php

namespace App\Reports;

use App\Models\OrderPayment;
use App\Reports\Support\ReportOptions;
use App\Support\Currency;
use Illuminate\Database\Eloquent\Builder;

/**
 * Payments / Collections report over the append-only order_payments ledger.
 * Positive rows are collections, negative rows are refunds.
 */
class PaymentsReport extends ReportDefinition
{
    public function key(): string
    {
        return 'payments';
    }

    public function label(): string
    {
        return 'Payments';
    }

    protected function model(): string
    {
        return OrderPayment::class;
    }

    protected function baseQuery(): Builder
    {
        return OrderPayment::query()
            ->with(['order:id,order_number', 'creator:id,name']);
    }

    public function dateColumnOptions(): array
    {
        return ['created_at' => 'Payment Date'];
    }

    public function filters(): array
    {
        return [
            [
                'key' => 'payment_method',
                'label' => 'Payment Method',
                'type' => 'select',
                'options' => ['cash' => 'Cash', 'card' => 'Card', 'check' => 'Check'],
                'apply' => fn (Builder $q, $v) => $q->where('payment_method', $v),
            ],
            [
                'key' => 'type',
                'label' => 'Type',
                'type' => 'select',
                'options' => ['collection' => 'Collection', 'refund' => 'Refund'],
                'apply' => fn (Builder $q, $v) => $v === 'refund'
                    ? $q->where('amount', '<', 0)
                    : $q->where('amount', '>=', 0),
            ],
            [
                'key' => 'created_by',
                'label' => 'Collector',
                'type' => 'select',
                'options' => fn () => ReportOptions::staff(),
                'apply' => fn (Builder $q, $v) => $q->where('created_by', (int) $v),
            ],
        ];
    }

    public function sortable(): array
    {
        return [
            'created_at' => 'order_payments.created_at',
            'amount' => 'order_payments.amount',
            'payment_method' => 'order_payments.payment_method',
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'created_at', 'label' => 'Date', 'value' => fn (OrderPayment $p) => $p->created_at?->format('d M Y h:i A') ?? '—'],
            ['key' => 'order_number', 'label' => 'Order #', 'value' => fn (OrderPayment $p) => $p->order?->order_number ?? '—'],
            ['key' => 'payment_method', 'label' => 'Method', 'value' => fn (OrderPayment $p) => ucfirst((string) $p->payment_method)],
            ['key' => 'collector', 'label' => 'Collector', 'value' => fn (OrderPayment $p) => $p->creator?->name ?? '—'],
            ['key' => 'type', 'label' => 'Type', 'align' => 'center', 'value' => fn (OrderPayment $p) => ((float) $p->amount) < 0 ? 'Refund' : 'Collection'],
            ['key' => 'amount', 'label' => 'Amount', 'align' => 'end', 'value' => fn (OrderPayment $p) => Currency::format((float) $p->amount)],
        ];
    }

    public function summary(Builder $query): array
    {
        $row = (clone $query)
            ->toBase()
            ->select([])
            ->reorder()
            ->selectRaw('COALESCE(SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END), 0) as collected')
            ->selectRaw('COALESCE(SUM(CASE WHEN amount < 0 THEN amount ELSE 0 END), 0) as refunded')
            ->selectRaw('COALESCE(SUM(amount), 0) as net')
            ->first();

        return [
            ['label' => 'Collected', 'value' => Currency::format((float) ($row->collected ?? 0))],
            ['label' => 'Refunded', 'value' => Currency::format(abs((float) ($row->refunded ?? 0)))],
            ['label' => 'Net', 'value' => Currency::format((float) ($row->net ?? 0))],
        ];
    }

    protected function applySearch(Builder $query, string $term): void
    {
        $query->whereHas('order', fn (Builder $o) => $o->where('order_number', 'like', "%{$term}%"));
    }
}
