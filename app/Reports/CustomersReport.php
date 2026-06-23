<?php

namespace App\Reports;

use App\Models\Customer;
use App\Reports\Support\ReportOptions;
use App\Support\Currency;
use Illuminate\Database\Eloquent\Builder;

/**
 * Customers report — directory with visits, lifetime value and credit balance.
 */
class CustomersReport extends ReportDefinition
{
    public function key(): string
    {
        return 'customers';
    }

    public function label(): string
    {
        return 'Customers';
    }

    protected function model(): string
    {
        return Customer::class;
    }

    protected function baseQuery(): Builder
    {
        return Customer::query()->with('discountGroup:id,name');
    }

    public function dateColumnOptions(): array
    {
        return [
            'created_at' => 'Created Date',
            'last_visit_at' => 'Last Visit',
        ];
    }

    public function filters(): array
    {
        return [
            [
                'key' => 'customer_type',
                'label' => 'Type',
                'type' => 'select',
                'options' => Customer::typeOptions(),
                'apply' => fn (Builder $q, $v) => $q->where('customer_type', $v),
            ],
            [
                'key' => 'discount_group_id',
                'label' => 'Discount Group',
                'type' => 'select',
                'options' => fn () => ReportOptions::discountGroups(),
                'apply' => fn (Builder $q, $v) => $q->where('discount_group_id', (int) $v),
            ],
            [
                'key' => 'has_credit',
                'label' => 'Has Credit',
                'type' => 'boolean',
                'apply' => fn (Builder $q, $v) => (bool) (int) $v ? $q->where('credit_balance', '>', 0) : $q,
            ],
        ];
    }

    public function sortable(): array
    {
        return [
            'name' => 'customers.name',
            'visits' => 'customers.total_visits',
            'lifetime_value' => 'customers.lifetime_value',
            'credit_balance' => 'customers.credit_balance',
            'created_at' => 'customers.created_at',
        ];
    }

    public function columns(): array
    {
        $types = Customer::typeOptions();

        return [
            ['key' => 'name', 'label' => 'Customer', 'value' => fn (Customer $c) => $c->name],
            ['key' => 'type', 'label' => 'Type', 'value' => fn (Customer $c) => $types[$c->customer_type] ?? ucfirst((string) $c->customer_type)],
            ['key' => 'phone', 'label' => 'Phone', 'value' => fn (Customer $c) => $c->phone ?? '—'],
            ['key' => 'email', 'label' => 'Email', 'value' => fn (Customer $c) => $c->email ?? '—'],
            ['key' => 'visits', 'label' => 'Visits', 'align' => 'center', 'value' => fn (Customer $c) => (int) $c->total_visits],
            ['key' => 'lifetime_value', 'label' => 'Lifetime Value', 'align' => 'end', 'value' => fn (Customer $c) => Currency::format((float) $c->lifetime_value)],
            ['key' => 'credit_balance', 'label' => 'Credit', 'align' => 'end', 'value' => fn (Customer $c) => Currency::format((float) $c->credit_balance)],
            ['key' => 'created_at', 'label' => 'Created', 'value' => fn (Customer $c) => $c->created_at?->format('d M Y') ?? '—'],
        ];
    }

    public function summary(Builder $query): array
    {
        $row = (clone $query)
            ->toBase()
            ->select([])
            ->reorder()
            ->selectRaw('COUNT(*) as customers_count')
            ->selectRaw('COALESCE(SUM(lifetime_value), 0) as total_ltv')
            ->selectRaw('COALESCE(SUM(credit_balance), 0) as total_credit')
            ->first();

        return [
            ['label' => 'Customers', 'value' => (string) (int) ($row->customers_count ?? 0)],
            ['label' => 'Lifetime Value', 'value' => Currency::format((float) ($row->total_ltv ?? 0))],
            ['label' => 'Outstanding Credit', 'value' => Currency::format((float) ($row->total_credit ?? 0))],
        ];
    }

    protected function applySearch(Builder $query, string $term): void
    {
        $query->search($term);
    }
}
