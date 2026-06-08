<?php

namespace App\Repositories;

use App\Models\ProductType;
use App\Repositories\Interface\ProductTypeRepositoryInterface;
use App\Repositories\Support\Concerns\HandlesCatalogSlugs;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class ProductTypesRepository implements ProductTypeRepositoryInterface
{
    use HandlesCatalogSlugs;

    public function index(): View
    {
        return view('tenant.ecommerce.product-types.index', [
            'listingUrl' => route('tenant.ecommerce.product-types.listing'),
        ]);
    }

    public function store(array $data, ?ProductType $productType = null, ?Authenticatable $user = null): array
    {
        $isUpdate = $productType !== null;
        $userId = $user?->getAuthIdentifier();
        $data['slug'] = $this->makeUniqueSlug(
            ProductType::class,
            $data['slug'] ?? $data['name'] ?? '',
            $productType?->id,
            'product-type'
        );

        if ($isUpdate) {
            $productType->fill($data);
            $productType->forceFill(['updated_by' => $userId])->save();
        } else {
            $productType = new ProductType($data);
            $productType->forceFill([
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
            $productType->save();
        }

        return [
            'success' => true,
            'message' => $isUpdate ? 'Product type updated successfully.' : 'Product type created successfully.',
            'data' => $this->transformProductType($productType, $user),
        ];
    }

    public function destroy(ProductType $productType): array
    {
        if ($productType->products()->exists()) {
            return [
                'success' => false,
                'message' => 'This product type is assigned to one or more products and cannot be deleted.',
            ];
        }

        $productType->delete();

        return [
            'success' => true,
            'message' => 'Product type deleted successfully.',
        ];
    }

    public function getProductTypesListing(array $filters, ?Authenticatable $user = null): array
    {
        $start = (int) ($filters['start'] ?? 0);
        $length = (int) ($filters['length'] ?? 10);
        $search = data_get($filters, 'search.value', '');
        $status = $filters['status'] ?? '';
        $sort = $filters['sort'] ?? 'latest';

        $baseQuery = ProductType::query();
        $filteredQuery = ProductType::query()
            ->withCount('products')
            ->search($search)
            ->when($status !== '', function (Builder $query) use ($status): void {
                $query->where('is_active', $status === '1');
            });

        $this->applyOrdering($filteredQuery, $filters, $sort);

        $recordsTotal = (clone $baseQuery)->count();
        $recordsFiltered = (clone $filteredQuery)->count();
        $productTypes = $filteredQuery
            ->skip($start)
            ->take($length)
            ->get();

        return [
            'draw' => (int) ($filters['draw'] ?? 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $this->transformProductTypes($productTypes, $user),
        ];
    }

    private function applyOrdering(Builder $query, array $filters, string $fallbackSort): void
    {
        $orderColumnIndex = data_get($filters, 'order.0.column');
        $orderDirection = data_get($filters, 'order.0.dir', 'asc');
        $columns = $filters['columns'] ?? [];
        $orderColumn = is_numeric($orderColumnIndex)
            ? data_get($columns, (int) $orderColumnIndex.'.data')
            : null;

        $sortableColumns = [
            'name' => 'name',
            'slug' => 'slug',
            'code' => 'code',
            'description' => 'description',
            'sort_order' => 'sort_order',
            'created_at' => 'created_at',
        ];

        if (is_string($orderColumn) && array_key_exists($orderColumn, $sortableColumns)) {
            $query->orderBy($sortableColumns[$orderColumn], $orderDirection === 'desc' ? 'desc' : 'asc')
                ->orderBy('id');

            return;
        }

        match ($fallbackSort) {
            'name' => $query->orderBy('name')->orderBy('id'),
            'sort_order' => $query->orderBy('sort_order')->orderBy('name')->orderBy('id'),
            default => $query->latest(),
        };
    }

    private function transformProductTypes(Collection $productTypes, ?Authenticatable $user = null): array
    {
        return $productTypes
            ->map(fn (ProductType $productType) => $this->transformProductType($productType, $user))
            ->all();
    }

    private function transformProductType(ProductType $productType, ?Authenticatable $user = null): array
    {
        return [
            'id' => $productType->id,
            'name' => $productType->name,
            'slug' => $productType->slug,
            'code' => $productType->code,
            'description' => $productType->description,
            'sort_order' => $productType->sort_order,
            'products_count' => $productType->products_count ?? $productType->products()->count(),
            'is_active' => $productType->is_active,
            'status_label' => $productType->is_active ? 'Active' : 'Inactive',
            'status_badge_class' => $productType->is_active ? 'bg-label-success' : 'bg-label-secondary',
            'created_at' => $productType->created_at?->format('d M Y'),
            'can_update' => $user?->can('update', $productType) ?? false,
            'can_delete' => $user?->can('delete', $productType) ?? false,
            'delete_url' => $user?->can('delete', $productType)
                ? route('tenant.ecommerce.product-types.destroy', $productType)
                : null,
        ];
    }
}
