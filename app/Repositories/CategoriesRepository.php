<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Interface\CategoryRepositoryInterface;
use App\Repositories\Support\Concerns\HandlesCatalogSlugs;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class CategoriesRepository implements CategoryRepositoryInterface
{
    use HandlesCatalogSlugs;

    public function index(): View
    {
        return view('tenant.ecommerce.categories.index', [
            'listingUrl' => route('tenant.ecommerce.categories.listing'),
        ]);
    }

    public function store(array $data, ?Category $category = null, ?Authenticatable $user = null): array
    {
        $isUpdate = $category !== null;
        $userId = $user?->getAuthIdentifier();
        $data['slug'] = $this->makeUniqueSlug(
            Category::class,
            $data['slug'] ?? $data['name'] ?? '',
            $category?->id,
            'category'
        );

        if ($isUpdate) {
            $category->fill($data);
            $category->forceFill(['updated_by' => $userId])->save();
        } else {
            $category = new Category($data);
            $category->forceFill([
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
            $category->save();
        }

        return [
            'success' => true,
            'message' => $isUpdate ? 'Category updated successfully.' : 'Category created successfully.',
            'data' => $this->transformCategory($category, $user),
        ];
    }

    public function destroy(Category $category): array
    {
        $category->delete();

        return [
            'success' => true,
            'message' => 'Category deleted successfully.',
        ];
    }

    public function getCategoriesListing(array $filters, ?Authenticatable $user = null): array
    {
        $start = (int) ($filters['start'] ?? 0);
        $length = (int) ($filters['length'] ?? 10);
        $search = data_get($filters, 'search.value', '');
        $status = $filters['status'] ?? '';
        $sort = $filters['sort'] ?? 'latest';

        $baseQuery = Category::query();
        $filteredQuery = Category::query()
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
            'data' => $this->transformCategories($categories, $user),
        ];
    }

    private function applyOrdering(Builder $query, array $filters, string $fallbackSort): void
    {
        $orderColumnIndex = data_get($filters, 'order.0.column');
        $orderDirection = data_get($filters, 'order.0.dir', 'asc');
        $columns = $filters['columns'] ?? [];
        $orderColumn = is_numeric($orderColumnIndex)
            ? data_get($columns, (int) $orderColumnIndex . '.data')
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

    private function transformCategories(Collection $categories, ?Authenticatable $user = null): array
    {
        return $categories
            ->map(fn (Category $category) => $this->transformCategory($category, $user))
            ->all();
    }

    private function transformCategory(Category $category, ?Authenticatable $user = null): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'code' => $category->code,
            'description' => $category->description,
            'sort_order' => $category->sort_order,
            'is_active' => $category->is_active,
            'status_label' => $category->is_active ? 'Active' : 'Inactive',
            'status_badge_class' => $category->is_active ? 'bg-label-success' : 'bg-label-secondary',
            'created_at' => $category->created_at?->format('d M Y'),
            'can_update' => $user?->can('update', $category) ?? false,
            'can_delete' => $user?->can('delete', $category) ?? false,
            'delete_url' => $user?->can('delete', $category)
                ? route('tenant.ecommerce.categories.destroy', $category)
                : null,
        ];
    }
}
