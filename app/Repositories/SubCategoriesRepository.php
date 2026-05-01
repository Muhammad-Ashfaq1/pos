<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\SubCategory;
use App\Repositories\Interface\SubCategoryRepositoryInterface;
use App\Repositories\Support\Concerns\HandlesCatalogSlugs;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class SubCategoriesRepository implements SubCategoryRepositoryInterface
{
    use HandlesCatalogSlugs;

    public function index(): View
    {
        return view('tenant.ecommerce.sub-categories.index', [
            'listingUrl' => route('tenant.ecommerce.subcategories.listing'),
            'categoriesDropdownUrl' => route('tenant.ecommerce.dropdowns.categories'),
        ]);
    }

    public function store(array $data, ?SubCategory $subCategory = null, ?Authenticatable $user = null): array
    {
        $isUpdate = $subCategory !== null;
        $userId = $user?->getAuthIdentifier();
        $data['slug'] = $this->makeUniqueSlug(
            SubCategory::class,
            $data['slug'] ?? $data['name'] ?? '',
            $subCategory?->id,
            'sub-category'
        );

        if ($isUpdate) {
            $subCategory->fill($data);
            $subCategory->forceFill(['updated_by' => $userId])->save();
        } else {
            $subCategory = new SubCategory($data);
            $subCategory->forceFill([
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
            $subCategory->save();
        }

        $subCategory->loadMissing('category:id,name');

        return [
            'success' => true,
            'message' => $isUpdate ? 'Sub category updated successfully.' : 'Sub category created successfully.',
            'data' => $this->transformSubCategory($subCategory, $user),
        ];
    }

    public function destroy(SubCategory $subCategory): array
    {
        $subCategory->delete();

        return [
            'success' => true,
            'message' => 'Sub category deleted successfully.',
        ];
    }

    public function getSubCategoriesListing(array $filters, ?Authenticatable $user = null): array
    {
        $start = (int) ($filters['start'] ?? 0);
        $length = (int) ($filters['length'] ?? 10);
        $search = data_get($filters, 'search.value', '');
        $status = $filters['status'] ?? '';
        $sort = $filters['sort'] ?? 'latest';
        $categoryId = $filters['category_id'] ?? null;

        $baseQuery = SubCategory::query();
        $filteredQuery = SubCategory::query()
            ->with('category:id,name')
            ->search($search)
            ->when($status !== '', function (Builder $query) use ($status): void {
                $query->where('is_active', $status === '1');
            })
            ->when($categoryId, function (Builder $query) use ($categoryId): void {
                $query->where('category_id', $categoryId);
            });


        $this->applyOrdering($filteredQuery, $filters, $sort);

        $recordsTotal = (clone $baseQuery)->count();
        $recordsFiltered = (clone $filteredQuery)->count();
        $subCategories = $filteredQuery
            ->skip($start)
            ->take($length)
            ->get();

        return [
            'draw' => (int) ($filters['draw'] ?? 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $this->transformSubCategories($subCategories, $user),
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
            'category_name' => fn (Builder $builder, string $direction) => $builder->orderBy(
                Category::query()
                    ->select('name')
                    ->whereColumn('categories.id', 'sub_categories.category_id')
                    ->limit(1),
                $direction
            ),
            'name' => fn (Builder $builder, string $direction) => $builder->orderBy('name', $direction),
            'slug' => fn (Builder $builder, string $direction) => $builder->orderBy('slug', $direction),
            'code' => fn (Builder $builder, string $direction) => $builder->orderBy('code', $direction),
            'description' => fn (Builder $builder, string $direction) => $builder->orderBy('description', $direction),
            'sort_order' => fn (Builder $builder, string $direction) => $builder->orderBy('sort_order', $direction),
            'created_at' => fn (Builder $builder, string $direction) => $builder->orderBy('created_at', $direction),
        ];

        if (is_string($orderColumn) && array_key_exists($orderColumn, $sortableColumns)) {
            $sortableColumns[$orderColumn]($query, $orderDirection);
            $query->orderBy('id');

            return;
        }

        match ($fallbackSort) {
            'category' => $sortableColumns['category_name']($query, 'asc'),
            'name' => $query->orderBy('name')->orderBy('id'),
            'sort_order' => $query->orderBy('sort_order')->orderBy('name')->orderBy('id'),
            default => $query->latest(),
        };
    }

    private function transformSubCategories(Collection $subCategories, ?Authenticatable $user = null): array
    {
        return $subCategories
            ->map(fn (SubCategory $subCategory) => $this->transformSubCategory($subCategory, $user))
            ->all();
    }

    private function transformSubCategory(SubCategory $subCategory, ?Authenticatable $user = null): array
    {
        return [
            'id' => $subCategory->id,
            'category_id' => $subCategory->category_id,
            'category_name' => $subCategory->category?->name,
            'name' => $subCategory->name,
            'slug' => $subCategory->slug,
            'code' => $subCategory->code,
            'description' => $subCategory->description,
            'sort_order' => $subCategory->sort_order,
            'is_active' => $subCategory->is_active,
            'status_label' => $subCategory->is_active ? 'Active' : 'Inactive',
            'status_badge_class' => $subCategory->is_active ? 'bg-label-success' : 'bg-label-secondary',
            'created_at' => $subCategory->created_at?->format('d M Y'),
            'can_update' => $user?->can('update', $subCategory) ?? false,
            'can_delete' => $user?->can('delete', $subCategory) ?? false,
            'delete_url' => $user?->can('delete', $subCategory)
                ? route('tenant.ecommerce.subcategories.destroy', $subCategory)
                : null,
        ];
    }
}
