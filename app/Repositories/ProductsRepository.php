<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use App\Repositories\Interface\ProductRepositoryInterface;
use App\Repositories\Support\Concerns\HandlesCatalogSlugs;
use App\Services\ImageService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductsRepository implements ProductRepositoryInterface
{
    use HandlesCatalogSlugs;

    public function __construct(
        private readonly ImageService $imageService
    ) {
    }

    public function index(): View
    {
        return view('tenant.ecommerce.products.index', [
            'listingUrl' => route('tenant.ecommerce.products.listing'),
            'categoriesDropdownUrl' => route('tenant.ecommerce.dropdowns.categories'),
            'subCategoriesDropdownUrl' => route('tenant.ecommerce.dropdowns.subcategories'),
            'productTypes' => Product::typeOptions(),
        ]);
    }

    public function store(array $data, ?Product $product = null, ?Authenticatable $user = null, array $images = []): array
    {
        $isUpdate = $product !== null;
        $userId = $user?->getAuthIdentifier();

        $product = DB::transaction(function () use ($data, $product, $user, $userId, $isUpdate, $images): Product {
            $data['slug'] = $this->makeUniqueSlug(
                Product::class,
                $data['slug'] ?? $data['name'] ?? '',
                $product?->id,
                'product'
            );

            $data['opening_stock'] = $this->normalizeDecimal($data['opening_stock'] ?? 0);
            $data['current_stock'] = array_key_exists('current_stock', $data)
                ? $this->normalizeDecimal($data['current_stock'])
                : ($isUpdate ? $product->current_stock : $data['opening_stock']);
            $data['minimum_stock_level'] = $this->normalizeDecimal($data['minimum_stock_level'] ?? 0);
            $data['reorder_level'] = $this->normalizeDecimal($data['reorder_level'] ?? 0);
            $data['tax_percentage'] = $data['tax_percentage'] !== null && $data['tax_percentage'] !== ''
                ? $this->normalizeMoney($data['tax_percentage'])
                : null;
            $data['cost_price'] = $this->normalizeMoney($data['cost_price'] ?? 0);
            $data['sale_price'] = $this->normalizeMoney($data['sale_price'] ?? 0);
            $data['category_id'] = $data['category_id'] ?: null;
            $data['sub_category_id'] = $data['sub_category_id'] ?: null;

            if ($isUpdate) {
                $product->fill($data);
                $product->forceFill(['updated_by' => $userId])->save();
            } else {
                $product = new Product($data);
                $product->forceFill([
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
                $product->save();
            }

            $this->imageService->syncForModel(
                model: $product,
                uploadedImages: array_values(array_filter($images, fn ($file) => $file instanceof UploadedFile)),
                removedImageIds: array_map('intval', $data['removed_image_ids'] ?? []),
                primaryImageRef: $data['primary_image_ref'] ?? null,
                user: $user,
            );

            return $product->fresh([
                'category:id,name',
                'subCategory:id,name',
                'images',
                'primaryImage',
            ]);
        });

        return [
            'success' => true,
            'message' => $isUpdate ? 'Product updated successfully.' : 'Product created successfully.',
            'data' => $this->transformProduct($product, $user),
        ];
    }

    public function destroy(Product $product): array
    {
        $product->delete();

        return [
            'success' => true,
            'message' => 'Product deleted successfully.',
        ];
    }

    public function getProductsListing(array $filters, ?Authenticatable $user = null): array
    {
        $start = (int) ($filters['start'] ?? 0);
        $length = (int) ($filters['length'] ?? 10);
        $search = data_get($filters, 'search.value', '');
        $status = $filters['status'] ?? '';
        $categoryId = $filters['category_id'] ?? null;
        $subCategoryId = $filters['sub_category_id'] ?? null;
        $productType = $filters['product_type'] ?? '';
        $trackInventory = $filters['track_inventory'] ?? '';
        $sort = $filters['sort'] ?? 'latest';

        $baseQuery = Product::query();
        $filteredQuery = Product::query()
            ->with([
                'category:id,name',
                'subCategory:id,name',
                'images',
                'primaryImage',
            ])
            ->search($search)
            ->when($status !== '', function (Builder $query) use ($status): void {
                $query->where('is_active', $status === '1');
            })
            ->when($categoryId, function (Builder $query) use ($categoryId): void {
                $query->where('category_id', $categoryId);
            })
            ->when($subCategoryId, function (Builder $query) use ($subCategoryId): void {
                $query->where('sub_category_id', $subCategoryId);
            })
            ->when($productType !== '', function (Builder $query) use ($productType): void {
                $query->where('product_type', $productType);
            })
            ->when($trackInventory !== '', function (Builder $query) use ($trackInventory): void {
                $query->where('track_inventory', $trackInventory === '1');
            });

        $this->applyOrdering($filteredQuery, $filters, $sort);

        $recordsTotal = (clone $baseQuery)->count();
        $recordsFiltered = (clone $filteredQuery)->count();
        $products = $filteredQuery
            ->skip($start)
            ->take($length)
            ->get();

        return [
            'draw' => (int) ($filters['draw'] ?? 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $this->transformProducts($products, $user),
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
                    ->whereColumn('categories.id', 'products.category_id')
                    ->limit(1),
                $direction
            ),
            'sub_category_name' => fn (Builder $builder, string $direction) => $builder->orderBy(
                SubCategory::query()
                    ->select('name')
                    ->whereColumn('sub_categories.id', 'products.sub_category_id')
                    ->limit(1),
                $direction
            ),
            'product_type' => fn (Builder $builder, string $direction) => $builder->orderBy('product_type', $direction),
            'name' => fn (Builder $builder, string $direction) => $builder->orderBy('name', $direction),
            'sku' => fn (Builder $builder, string $direction) => $builder->orderBy('sku', $direction),
            'barcode' => fn (Builder $builder, string $direction) => $builder->orderBy('barcode', $direction),
            'brand' => fn (Builder $builder, string $direction) => $builder->orderBy('brand', $direction),
            'sale_price' => fn (Builder $builder, string $direction) => $builder->orderBy('sale_price', $direction),
            'current_stock' => fn (Builder $builder, string $direction) => $builder->orderBy('current_stock', $direction),
            'created_at' => fn (Builder $builder, string $direction) => $builder->orderBy('created_at', $direction),
        ];

        if (is_string($orderColumn) && array_key_exists($orderColumn, $sortableColumns)) {
            $sortableColumns[$orderColumn]($query, $orderDirection);
            $query->orderBy('id');

            return;
        }

        match ($fallbackSort) {
            'name' => $query->orderBy('name')->orderBy('id'),
            'price_low_high' => $query->orderBy('sale_price')->orderBy('name')->orderBy('id'),
            'stock_low_high' => $query->orderBy('current_stock')->orderBy('name')->orderBy('id'),
            default => $query->latest(),
        };
    }

    private function transformProducts(Collection $products, ?Authenticatable $user = null): array
    {
        return $products
            ->map(fn (Product $product) => $this->transformProduct($product, $user))
            ->all();
    }

    private function transformProduct(Product $product, ?Authenticatable $user = null): array
    {
        $typeOptions = Product::typeOptions();
        $isLowStock = $product->track_inventory
            && (float) $product->current_stock <= (float) $product->reorder_level;

        return [
            'id' => $product->id,
            'category_id' => $product->category_id,
            'sub_category_id' => $product->sub_category_id,
            'category_name' => $product->category?->name,
            'sub_category_name' => $product->subCategory?->name,
            'product_type' => $product->product_type,
            'product_type_label' => $typeOptions[$product->product_type] ?? ucfirst((string) $product->product_type),
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'brand' => $product->brand,
            'unit' => $product->unit,
            'description' => $product->description,
            'cost_price' => (string) $product->cost_price,
            'sale_price' => (string) $product->sale_price,
            'tax_percentage' => $product->tax_percentage !== null ? (string) $product->tax_percentage : null,
            'opening_stock' => (string) $product->opening_stock,
            'current_stock' => (string) $product->current_stock,
            'minimum_stock_level' => (string) $product->minimum_stock_level,
            'reorder_level' => (string) $product->reorder_level,
            'track_inventory' => $product->track_inventory,
            'track_inventory_label' => $product->track_inventory ? 'Tracked' : 'Not Tracked',
            'is_active' => $product->is_active,
            'status_label' => $product->is_active ? 'Active' : 'Inactive',
            'status_badge_class' => $product->is_active ? 'bg-label-success' : 'bg-label-secondary',
            'primary_image_url' => $product->primaryImage?->url,
            'images' => $this->imageService->transformMany($product->images),
            'images_count' => $product->images->count(),
            'stock_badge_class' => ! $product->track_inventory
                ? 'bg-label-secondary'
                : ($isLowStock ? 'bg-label-warning' : 'bg-label-success'),
            'stock_status_label' => ! $product->track_inventory
                ? 'No Tracking'
                : ($isLowStock ? 'Low Stock' : 'In Stock'),
            'created_at' => $product->created_at?->format('d M Y'),
            'can_update' => $user?->can('update', $product) ?? false,
            'can_delete' => $user?->can('delete', $product) ?? false,
            'delete_url' => $user?->can('delete', $product)
                ? route('tenant.ecommerce.products.destroy', $product)
                : null,
        ];
    }

    private function normalizeMoney(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    private function normalizeDecimal(mixed $value): string
    {
        return number_format((float) $value, 3, '.', '');
    }
}
