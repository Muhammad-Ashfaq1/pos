<?php

namespace App\Repositories;

use App\Models\Discount;
use App\Repositories\Interface\DiscountRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class DiscountsRepository implements DiscountRepositoryInterface
{
    public function index(): View
    {
        return view('tenant.ecommerce.discounts.index', [
            'listingUrl' => route('tenant.ecommerce.discounts.listing'),
            'editUrlTemplate' => route('tenant.ecommerce.discounts.edit', ['discount' => '__DISCOUNT__']),
            'discountTypes' => Discount::typeOptions(),
            'appliesToOptions' => Discount::appliesToOptions(),
        ]);
    }

    public function store(array $data, ?Discount $discount = null, ?Authenticatable $user = null): array
    {
        $isUpdate = $discount !== null;
        $userId = $user?->getAuthIdentifier();
        $payload = $this->buildPayload($data);

        if ($isUpdate) {
            $discount->fill($payload);
            $discount->forceFill(['updated_by' => $userId])->save();
        } else {
            $discount = new Discount($payload);
            $discount->forceFill([
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
            $discount->save();
        }

        return [
            'success' => true,
            'message' => $isUpdate ? 'Discount updated successfully.' : 'Discount created successfully.',
            'data' => $this->transformDiscount($discount, $user),
        ];
    }

    public function destroy(Discount $discount): array
    {
        $discount->delete();

        return [
            'success' => true,
            'message' => 'Discount deleted successfully.',
        ];
    }

    public function getDiscountsListing(array $filters, ?Authenticatable $user = null): array
    {
        $start = (int) ($filters['start'] ?? 0);
        $length = (int) ($filters['length'] ?? 10);
        $search = data_get($filters, 'search.value', '');
        $status = $filters['status'] ?? '';
        $discountType = $filters['discount_type'] ?? '';
        $appliesTo = $filters['applies_to'] ?? '';
        $sort = $filters['sort'] ?? 'latest';

        $baseQuery = Discount::query();
        $filteredQuery = Discount::query()
            ->search($search)
            ->when($status !== '', function (Builder $query) use ($status): void {
                $query->where('is_active', $status === '1');
            })
            ->when($discountType !== '', function (Builder $query) use ($discountType): void {
                $query->where('discount_type', $discountType);
            })
            ->when($appliesTo !== '', function (Builder $query) use ($appliesTo): void {
                $query->where('applies_to', $appliesTo);
            });

        $this->applyOrdering($filteredQuery, $filters, $sort);

        $recordsTotal = (clone $baseQuery)->count();
        $recordsFiltered = (clone $filteredQuery)->count();
        $discounts = $filteredQuery
            ->skip($start)
            ->take($length)
            ->get();

        return [
            'draw' => (int) ($filters['draw'] ?? 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $this->transformDiscounts($discounts, $user),
        ];
    }

    public function getDiscountFormData(Discount $discount, ?Authenticatable $user = null): array
    {
        return $this->transformDiscount($discount, $user);
    }

    private function buildPayload(array $data): array
    {
        $discountType = $data['discount_type'];

        return [
            'name' => trim((string) $data['name']),
            'code' => $this->normalizeNullableUpperString($data['code'] ?? null),
            'description' => $this->normalizeNullableString($data['description'] ?? null),
            'discount_type' => $discountType,
            'applies_to' => $data['applies_to'],
            'value' => $this->normalizeMoney($data['value']),
            'max_discount_amount' => ($data['max_discount_amount'] ?? null) !== null && $data['max_discount_amount'] !== ''
                ? $this->normalizeMoney($data['max_discount_amount'])
                : null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'usage_limit' => ($data['usage_limit'] ?? null) !== null && $data['usage_limit'] !== ''
                ? (int) $data['usage_limit']
                : null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_combinable' => (bool) ($data['is_combinable'] ?? false),
            'requires_reason' => (bool) ($data['requires_reason'] ?? false),
            'requires_manager_approval' => (bool) ($data['requires_manager_approval'] ?? false),
        ];
    }

    private function applyOrdering(Builder $query, array $filters, string $fallbackSort): void
    {
        $orderColumnIndex = data_get($filters, 'order.0.column');
        $orderDirection = data_get($filters, 'order.0.dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $columns = $filters['columns'] ?? [];
        $orderColumn = is_numeric($orderColumnIndex)
            ? data_get($columns, (int) $orderColumnIndex . '.data')
            : null;

        $sortableColumns = [
            'name' => fn (Builder $builder, string $direction) => $builder->orderBy('name', $direction),
            'code' => fn (Builder $builder, string $direction) => $builder->orderBy('code', $direction),
            'discount_type_label' => fn (Builder $builder, string $direction) => $builder->orderBy('discount_type', $direction),
            'applies_to_label' => fn (Builder $builder, string $direction) => $builder->orderBy('applies_to', $direction),
            'value' => fn (Builder $builder, string $direction) => $builder->orderBy('value', $direction),
            'starts_at' => fn (Builder $builder, string $direction) => $builder->orderBy('starts_at', $direction),
            'ends_at' => fn (Builder $builder, string $direction) => $builder->orderBy('ends_at', $direction),
            'created_at' => fn (Builder $builder, string $direction) => $builder->orderBy('created_at', $direction),
        ];

        if (is_string($orderColumn) && array_key_exists($orderColumn, $sortableColumns)) {
            $sortableColumns[$orderColumn]($query, $orderDirection);
            $query->orderBy('id');

            return;
        }

        match ($fallbackSort) {
            'name' => $query->orderBy('name')->orderBy('id'),
            'value_high_low' => $query->orderByDesc('value')->orderBy('name')->orderBy('id'),
            'starts_at' => $query->orderBy('starts_at')->orderBy('name')->orderBy('id'),
            default => $query->latest(),
        };
    }

    private function transformDiscounts(Collection $discounts, ?Authenticatable $user = null): array
    {
        return $discounts
            ->map(fn (Discount $discount) => $this->transformDiscount($discount, $user))
            ->all();
    }

    private function transformDiscount(Discount $discount, ?Authenticatable $user = null): array
    {
        $discountTypes = Discount::typeOptions();
        $appliesToOptions = Discount::appliesToOptions();

        return [
            'id' => $discount->id,
            'name' => $discount->name,
            'code' => $discount->code,
            'description' => $discount->description,
            'discount_type' => $discount->discount_type,
            'discount_type_label' => $discountTypes[$discount->discount_type] ?? ucfirst((string) $discount->discount_type),
            'applies_to' => $discount->applies_to,
            'applies_to_label' => $appliesToOptions[$discount->applies_to] ?? ucfirst((string) $discount->applies_to),
            'value' => (string) $discount->value,
            'value_label' => $discount->discount_type === Discount::TYPE_PERCENTAGE
                ? rtrim(rtrim(number_format((float) $discount->value, 2, '.', ''), '0'), '.').'%'
                : '$'.number_format((float) $discount->value, 2),
            'max_discount_amount' => $discount->max_discount_amount !== null ? (string) $discount->max_discount_amount : null,
            'max_discount_amount_label' => $discount->max_discount_amount !== null
                ? '$'.number_format((float) $discount->max_discount_amount, 2)
                : null,
            'starts_at' => $discount->starts_at?->format('Y-m-d\TH:i'),
            'starts_at_label' => $discount->starts_at?->format('d M Y h:i A'),
            'ends_at' => $discount->ends_at?->format('Y-m-d\TH:i'),
            'ends_at_label' => $discount->ends_at?->format('d M Y h:i A'),
            'usage_limit' => $discount->usage_limit,
            'is_active' => $discount->is_active,
            'status_label' => $discount->is_active ? 'Active' : 'Inactive',
            'status_badge_class' => $discount->is_active ? 'bg-label-success' : 'bg-label-secondary',
            'is_combinable' => $discount->is_combinable,
            'combinable_label' => $discount->is_combinable ? 'Combinable' : 'Exclusive',
            'combinable_badge_class' => $discount->is_combinable ? 'bg-label-info' : 'bg-label-warning',
            'requires_reason' => $discount->requires_reason,
            'requires_manager_approval' => $discount->requires_manager_approval,
            'created_at' => $discount->created_at?->format('d M Y'),
            'can_update' => $user?->can('update', $discount) ?? false,
            'can_delete' => $user?->can('delete', $discount) ?? false,
            'edit_url' => $user?->can('update', $discount)
                ? route('tenant.ecommerce.discounts.edit', $discount)
                : null,
            'delete_url' => $user?->can('delete', $discount)
                ? route('tenant.ecommerce.discounts.destroy', $discount)
                : null,
        ];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function normalizeNullableUpperString(mixed $value): ?string
    {
        $value = $this->normalizeNullableString($value);

        return $value !== null ? strtoupper($value) : null;
    }

    private function normalizeMoney(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
