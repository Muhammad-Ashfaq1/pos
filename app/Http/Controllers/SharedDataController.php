<?php

namespace App\Http\Controllers;

use App\Models\Category;
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
            ->with('discount:id,name,discount_type,applies_to,value,max_discount_amount,is_active,starts_at,ends_at')
            ->where('is_active', true)
            ->when($subCategoryId, fn ($q) => $q->where('sub_category_id', $subCategoryId))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($search !== '', fn ($q) => $q->search($search))
            ->orderBy('name');

        $products = $query->get(['id', 'name', 'sku', 'barcode', 'brand', 'unit', 'sale_price', 'discount_id', 'current_stock', 'track_inventory', 'product_type', 'sub_category_id', 'category_id']);

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
            ->with('discount:id,name,discount_type,applies_to,value,max_discount_amount,is_active,starts_at,ends_at')
            ->where('is_active', true)
            ->search($search)
            ->orderBy('name')
            ->limit(40)
            ->get(['id', 'name', 'sku', 'barcode', 'brand', 'unit', 'sale_price', 'discount_id', 'current_stock', 'track_inventory', 'product_type', 'sub_category_id', 'category_id']);

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
        $unitPrice = (float) $p->sale_price;
        $unitDiscount = $this->itemDiscountAmount($p);

        return [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'barcode' => $p->barcode,
            'brand' => $p->brand,
            'unit' => $p->unit,
            'sale_price' => $unitPrice,
            'discounted_price' => round(max($unitPrice - $unitDiscount, 0), 2),
            'unit_discount_amount' => $unitDiscount,
            'discount_name' => $p->discount?->name,
            'discount_type' => $this->hasActiveItemDiscount($p) ? $p->discount?->discount_type : null,
            'discount_value' => $this->hasActiveItemDiscount($p) ? (float) $p->discount->value : null,
            'current_stock' => (int) $p->current_stock,
            'track_inventory' => (bool) $p->track_inventory,
            'product_type' => $p->product_type,
            'sub_category_id' => $p->sub_category_id,
            'category_id' => $p->category_id,
            'type' => 'product',
        ];
    }

    private function itemDiscountAmount(Product $product): float
    {
        if (! $this->hasActiveItemDiscount($product)) {
            return 0.0;
        }

        $price = (float) $product->sale_price;
        $discount = $product->discount;

        $amount = $discount->discount_type === 'percentage'
            ? $price * ((float) $discount->value / 100)
            : (float) $discount->value;

        if ($discount->max_discount_amount !== null) {
            $amount = min($amount, (float) $discount->max_discount_amount);
        }

        return round(min($amount, $price), 2);
    }

    private function hasActiveItemDiscount(Product $product): bool
    {
        $discount = $product->discount;

        if (! $discount || $discount->applies_to !== 'item' || ! $discount->is_active) {
            return false;
        }

        $now = now();

        if ($discount->starts_at && $discount->starts_at->gt($now)) {
            return false;
        }

        if ($discount->ends_at && $discount->ends_at->lt($now)) {
            return false;
        }

        return true;
    }
}
