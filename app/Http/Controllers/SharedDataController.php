<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SharedDataController extends Controller
{
    public function categories(Request $request): JsonResponse
    {
        $search = trim((string) $request->string('q')->toString());

        $categories = Category::query()
            ->where('is_active', true)
            ->when($search !== '', fn ($q) => $q->search($search))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'slug']);

        return response()->json([
            'data' => $categories->map(fn (Category $c) => $this->mapCategory($c))->all(),
        ]);
    }

    public function subCategories(Request $request): JsonResponse
    {
        $search = trim((string) $request->string('q')->toString());
        $categoryId = $request->integer('category_id') ?: null;

        $query = SubCategory::query()
            ->where('is_active', true)
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($search !== '', fn ($q) => $q->search($search))
            ->orderBy('sort_order')
            ->orderBy('name');

        $subCategories = $query->get(['id', 'category_id', 'name', 'code', 'slug']);

        $categoryMeta = null;
        if ($categoryId) {
            $cat = Category::query()->find($categoryId);
            if ($cat) {
                $categoryMeta = ['id' => $cat->id, 'name' => $cat->name];
            }
        }

        return response()->json([
            'category' => $categoryMeta,
            'data' => $subCategories->map(fn (SubCategory $s) => $this->mapSubCategory($s))->all(),
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $search = trim((string) $request->string('q')->toString());
        $subCategoryId = $request->integer('sub_category_id') ?: null;
        $categoryId = $request->integer('category_id') ?: null;

        $query = Product::query()
            ->with(['discount:id,name,code,discount_type,applies_to,value,max_discount_amount,is_active,starts_at,ends_at', 'primaryImage'])
            ->where('is_active', true)
            ->when($subCategoryId, fn ($q) => $q->where('sub_category_id', $subCategoryId))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($search !== '', fn ($q) => $q->search($search))
            ->orderBy('name');

        $products = $query->get(['id', 'name', 'sku', 'barcode', 'brand', 'unit', 'sale_price', 'tax_percentage', 'current_stock', 'track_inventory', 'product_type', 'sub_category_id', 'category_id', 'discount_id']);

        $subCategoryMeta = null;
        if ($subCategoryId) {
            $sub = SubCategory::query()->find($subCategoryId);
            if ($sub) {
                $subCategoryMeta = ['id' => $sub->id, 'name' => $sub->name, 'category_id' => $sub->category_id];
            }
        }

        return response()->json([
            'sub_category' => $subCategoryMeta,
            'data' => $products->map(fn (Product $p) => $this->mapProduct($p))->all(),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $search = trim((string) $request->string('q')->toString());

        if ($search === '') {
            return response()->json([
                'categories' => [],
                'sub_categories' => [],
                'products' => [],
            ]);
        }

        $categories = Category::query()
            ->where('is_active', true)
            ->search($search)
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'code', 'slug']);

        $subCategories = SubCategory::query()
            ->where('is_active', true)
            ->search($search)
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'category_id', 'name', 'code', 'slug']);

        $products = Product::query()
            ->with(['discount:id,name,code,discount_type,applies_to,value,max_discount_amount,is_active,starts_at,ends_at', 'primaryImage'])
            ->where('is_active', true)
            ->search($search)
            ->orderBy('name')
            ->limit(40)
            ->get(['id', 'name', 'sku', 'barcode', 'brand', 'unit', 'sale_price', 'tax_percentage', 'current_stock', 'track_inventory', 'product_type', 'sub_category_id', 'category_id', 'discount_id']);

        return response()->json([
            'categories' => $categories->map(fn (Category $c) => $this->mapCategory($c))->all(),
            'sub_categories' => $subCategories->map(fn (SubCategory $s) => $this->mapSubCategory($s))->all(),
            'products' => $products->map(fn (Product $p) => $this->mapProduct($p))->all(),
        ]);
    }

    private function mapCategory(Category $c): array
    {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'code' => $c->code,
            'slug' => $c->slug,
            'type' => 'category',
        ];
    }

    private function mapSubCategory(SubCategory $s): array
    {
        return [
            'id' => $s->id,
            'name' => $s->name,
            'code' => $s->code,
            'slug' => $s->slug,
            'category_id' => $s->category_id,
            'type' => 'sub_category',
        ];
    }

    private function mapProduct(Product $p): array
    {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'barcode' => $p->barcode,
            'brand' => $p->brand,
            'unit' => $p->unit,
            'sale_price' => (float) $p->sale_price,
            'tax_percentage' => $p->tax_percentage !== null ? (float) $p->tax_percentage : 0.0,
            'current_stock' => (int) $p->current_stock,
            'track_inventory' => (bool) $p->track_inventory,
            'product_type' => $p->product_type,
            'sub_category_id' => $p->sub_category_id,
            'category_id' => $p->category_id,
            'image_url' => $p->primaryImage?->url,
            'discount' => $this->mapDiscount($p->discount),
            'type' => 'product',
        ];
    }

    private function mapDiscount(?Discount $discount): ?array
    {
        if (! $discount || ! $discount->is_active || $discount->applies_to !== Discount::APPLIES_TO_ITEM) {
            return null;
        }

        if ($discount->starts_at && $discount->starts_at->isFuture()) {
            return null;
        }

        if ($discount->ends_at && $discount->ends_at->isPast()) {
            return null;
        }

        return [
            'id' => $discount->id,
            'name' => $discount->name,
            'code' => $discount->code,
            'discount_type' => $discount->discount_type,
            'applies_to' => $discount->applies_to,
            'type' => $discount->discount_type,
            'value' => (float) $discount->value,
            'max_discount_amount' => $discount->max_discount_amount !== null ? (float) $discount->max_discount_amount : null,
            'max_amount' => $discount->max_discount_amount !== null ? (float) $discount->max_discount_amount : null,
            'is_active' => (bool) $discount->is_active,
            'starts_at' => $discount->starts_at?->toISOString(),
            'ends_at' => $discount->ends_at?->toISOString(),
            'label' => $this->discountLabel($discount),
        ];
    }

    private function discountLabel(Discount $discount): string
    {
        $value = $discount->discount_type === Discount::TYPE_PERCENTAGE
            ? rtrim(rtrim(number_format((float) $discount->value, 2, '.', ''), '0'), '.').'%'
            : '$'.number_format((float) $discount->value, 2);
        $code = filled($discount->code) ? " ({$discount->code})" : '';

        return "{$discount->name}{$code} - {$value}";
    }
}
