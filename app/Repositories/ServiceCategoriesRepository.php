<?php

namespace App\Repositories;

use App\Models\ServiceCategory;
use App\Repositories\Interface\ServiceCategoryRepositoryInterface;
use App\Repositories\Support\Concerns\HandlesCatalogSlugs;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class ServiceCategoriesRepository implements ServiceCategoryRepositoryInterface
{
    use HandlesCatalogSlugs;

    public function index(): View
    {
        return view('tenant.ecommerce.service-categories.index', [
            'listingUrl' => route('tenant.ecommerce.service-categories.listing'),
        ]);
    }

    public function store(array $data, ?ServiceCategory $serviceCategory = null, ?Authenticatable $user = null): array
    {
        $isUpdate = $serviceCategory !== null;
        $userId = $user?->getAuthIdentifier();

        unset($data['code']);

        $data['slug'] = $this->makeUniqueSlug(
            ServiceCategory::class,
            $data['slug'] ?? $data['name'] ?? '',
            $serviceCategory?->id,
            'service-category'
        );

        if ($isUpdate) {
            $serviceCategory->fill($data);
            $serviceCategory->forceFill(['updated_by' => $userId])->save();
        } else {
            $serviceCategory = new ServiceCategory($data);
            $serviceCategory->forceFill([
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
            $serviceCategory->save();
        }

        return [
            'success' => true,
            'message' => $isUpdate ? 'Service category updated successfully.' : 'Service category created successfully.',
            'data' => $this->transformServiceCategory($serviceCategory, $user),
        ];
    }

    public function destroy(ServiceCategory $serviceCategory): array
    {
        $serviceCategory->delete();

        return [
            'success' => true,
            'message' => 'Service category deleted successfully.',
        ];
    }

    public function getServiceCategoriesListing(array $filters, ?Authenticatable $user = null): array
    {
        $start = (int) ($filters['start'] ?? 0);
        $length = (int) ($filters['length'] ?? 10);
        $search = data_get($filters, 'search.value', '');
        $status = $filters['status'] ?? '';
        $sort = $filters['sort'] ?? 'latest';

        $baseQuery = ServiceCategory::query();
        $filteredQuery = ServiceCategory::query()
            ->search($search)
            ->when($status !== '', function (Builder $query) use ($status): void {
                $query->where('is_active', $status === '1');
            });

        $this->applyOrdering($filteredQuery, $filters, $sort);

        $recordsTotal = (clone $baseQuery)->count();
        $recordsFiltered = (clone $filteredQuery)->count();
        $categories = $filteredQuery
            ->skip($start)
            ->take($length)
            ->get();

        return [
            'draw' => (int) ($filters['draw'] ?? 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $this->transformServiceCategories($categories, $user),
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

    private function transformServiceCategories(Collection $categories, ?Authenticatable $user = null): array
    {
        return $categories
            ->map(fn (ServiceCategory $category) => $this->transformServiceCategory($category, $user))
            ->all();
    }

    private function transformServiceCategory(ServiceCategory $category, ?Authenticatable $user = null): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'sort_order' => $category->sort_order,
            'is_active' => $category->is_active,
            'status_label' => $category->is_active ? 'Active' : 'Inactive',
            'status_badge_class' => $category->is_active ? 'bg-label-success' : 'bg-label-secondary',
            'created_at' => $category->created_at?->format('d M Y'),
            'can_update' => $user?->can('update', $category) ?? false,
            'can_delete' => $user?->can('delete', $category) ?? false,
            'delete_url' => $user?->can('delete', $category)
                ? route('tenant.ecommerce.service-categories.destroy', $category)
                : null,
        ];
    }
}
