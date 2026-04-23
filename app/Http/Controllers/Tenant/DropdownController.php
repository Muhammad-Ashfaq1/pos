<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DropdownController extends Controller
{
    public function categories(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()?->canAny([
                'category.view',
                'category.create',
                'category.update',
                'subcategory.view',
                'subcategory.create',
                'subcategory.update',
                'product.view',
                'product.create',
                'product.update',
                'products.view',
                'products.manage',
                'service.view',
                'service.create',
                'service.update',
            ]),
            403
        );

        $search = trim((string) $request->string('q')->toString());
        $perPage = min((int) $request->integer('per_page', 20), 50);
        $page = max((int) $request->integer('page', 1), 1);
        $activeOnly = $request->boolean('active_only', false);

        $query = Category::query()
            ->select(['id', 'name', 'code', 'slug'])
            ->when($activeOnly, fn ($builder) => $builder->where('is_active', true))
            ->search($search)
            ->orderBy('name')
            ->orderBy('id');

        $total = (clone $query)->count();
        $categories = $query
            ->forPage($page, $perPage)
            ->get();

        return response()->json([
            'results' => $categories->map(fn (Category $category) => [
                'id' => $category->id,
                'text' => $category->name,
                'name' => $category->name,
                'code' => $category->code,
                'slug' => $category->slug,
            ])->all(),
            'pagination' => [
                'more' => ($page * $perPage) < $total,
            ],
        ]);
    }

    public function subCategories(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()?->canAny([
                'subcategory.view',
                'subcategory.create',
                'subcategory.update',
                'product.view',
                'product.create',
                'product.update',
                'products.view',
                'products.manage',
                'service.view',
                'service.create',
                'service.update',
            ]),
            403
        );

        $search = trim((string) $request->string('q')->toString());
        $perPage = min((int) $request->integer('per_page', 20), 50);
        $page = max((int) $request->integer('page', 1), 1);
        $categoryId = $request->integer('category_id') ?: null;
        $activeOnly = $request->boolean('active_only', false);

        $query = SubCategory::query()
            ->select(['id', 'category_id', 'name', 'code', 'slug'])
            ->when($activeOnly, fn ($builder) => $builder->where('is_active', true))
            ->when($categoryId, fn ($builder) => $builder->where('category_id', $categoryId))
            ->search($search)
            ->orderBy('name')
            ->orderBy('id');

        $total = (clone $query)->count();
        $subCategories = $query
            ->forPage($page, $perPage)
            ->get();

        return response()->json([
            'results' => $subCategories->map(fn (SubCategory $subCategory) => [
                'id' => $subCategory->id,
                'text' => $subCategory->name,
                'name' => $subCategory->name,
                'category_id' => $subCategory->category_id,
                'code' => $subCategory->code,
                'slug' => $subCategory->slug,
            ])->all(),
            'pagination' => [
                'more' => ($page * $perPage) < $total,
            ],
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()?->canAny([
                'product.view',
                'product.create',
                'product.update',
                'products.view',
                'products.manage',
                'service.view',
                'service.create',
                'service.update',
            ]),
            403
        );

        $search = trim((string) $request->string('q')->toString());
        $perPage = min((int) $request->integer('per_page', 20), 50);
        $page = max((int) $request->integer('page', 1), 1);
        $activeOnly = $request->boolean('active_only', false);
        $categoryId = $request->integer('category_id') ?: null;

        $query = Product::query()
            ->select(['id', 'category_id', 'name', 'sku', 'unit', 'product_type'])
            ->when($activeOnly, fn ($builder) => $builder->where('is_active', true))
            ->when($categoryId, fn ($builder) => $builder->where('category_id', $categoryId))
            ->search($search)
            ->orderBy('name')
            ->orderBy('id');

        $total = (clone $query)->count();
        $products = $query
            ->forPage($page, $perPage)
            ->get();

        return response()->json([
            'results' => $products->map(fn (Product $product) => [
                'id' => $product->id,
                'text' => $product->name,
                'name' => $product->name,
                'category_id' => $product->category_id,
                'sku' => $product->sku,
                'unit' => $product->unit,
                'product_type' => $product->product_type,
            ])->all(),
            'pagination' => [
                'more' => ($page * $perPage) < $total,
            ],
        ]);
    }
}
