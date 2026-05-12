<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\DiscountGroup;
use App\Models\Product;
use App\Models\SubCategory;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DropdownController extends Controller
{
    public function categories(Request $request): JsonResponse
    {
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

    public function discounts(Request $request): JsonResponse
    {
        $search = trim((string) $request->string('q')->toString());
        $perPage = min((int) $request->integer('per_page', 20), 50);
        $page = max((int) $request->integer('page', 1), 1);
        $appliesTo = trim((string) $request->string('applies_to')->toString());
        $activeOnly = $request->boolean('active_only', false);

        $query = Discount::query()
            ->select(['id', 'name', 'code', 'discount_type', 'applies_to', 'value'])
            ->when($activeOnly, fn ($builder) => $builder->where('is_active', true))
            ->when($appliesTo !== '', fn ($builder) => $builder->where('applies_to', $appliesTo))
            ->search($search)
            ->orderBy('name')
            ->orderBy('id');

        $total = (clone $query)->count();
        $discounts = $query
            ->forPage($page, $perPage)
            ->get();

        return response()->json([
            'results' => $discounts->map(fn (Discount $discount) => [
                'id' => $discount->id,
                'text' => $this->discountLabel($discount),
                'name' => $discount->name,
                'code' => $discount->code,
                'discount_type' => $discount->discount_type,
                'applies_to' => $discount->applies_to,
                'value' => (float) $discount->value,
            ])->all(),
            'pagination' => [
                'more' => ($page * $perPage) < $total,
            ],
        ]);
    }

    public function discountGroups(Request $request): JsonResponse
    {
        $search = trim((string) $request->string('q')->toString());
        $perPage = min((int) $request->integer('per_page', 20), 50);
        $page = max((int) $request->integer('page', 1), 1);
        $activeOnly = $request->boolean('active_only', false);

        $query = DiscountGroup::query()
            ->select(['id', 'name', 'slug', 'type', 'value', 'min_limit', 'is_active'])
            ->when($activeOnly, fn ($builder) => $builder->where('is_active', true))
            ->when($search !== '', function ($builder) use ($search): void {
                $builder->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->orderBy('id');

        $total = (clone $query)->count();
        $groups = $query
            ->forPage($page, $perPage)
            ->get();

        return response()->json([
            'results' => $groups->map(fn (DiscountGroup $group) => [
                'id' => $group->id,
                'text' => $this->discountGroupLabel($group),
                'name' => $group->name,
                'slug' => $group->slug,
                'type' => $group->type,
                'value' => (float) $group->value,
                'min_limit' => $group->min_limit !== null ? (float) $group->min_limit : null,
                'is_active' => (bool) $group->is_active,
            ])->all(),
            'pagination' => [
                'more' => ($page * $perPage) < $total,
            ],
        ]);
    }

    public function customers(Request $request): JsonResponse
    {
        $search = trim((string) $request->string('q')->toString());
        $perPage = min((int) $request->integer('per_page', 20), 50);
        $page = max((int) $request->integer('page', 1), 1);
        $customerType = trim((string) $request->string('customer_type')->toString());

        $query = Customer::query()
            ->with('discountGroup:id,name,type,value,min_limit,is_active')
            ->select(['id', 'customer_type', 'discount_group_id', 'name', 'phone', 'email'])
            ->when($customerType !== '', fn ($builder) => $builder->where('customer_type', $customerType))
            ->search($search)
            ->orderBy('name')
            ->orderBy('id');

        $total = (clone $query)->count();
        $customers = $query
            ->forPage($page, $perPage)
            ->get();

        return response()->json([
            'results' => $customers->map(fn (Customer $customer) => [
                'id' => $customer->id,
                'text' => $customer->name,
                'name' => $customer->name,
                'customer_type' => $customer->customer_type,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'discount_group' => $customer->discountGroup ? [
                    'id' => $customer->discountGroup->id,
                    'name' => $customer->discountGroup->name,
                    'type' => $customer->discountGroup->type,
                    'value' => (float) $customer->discountGroup->value,
                    'min_limit' => $customer->discountGroup->min_limit !== null ? (float) $customer->discountGroup->min_limit : null,
                    'is_active' => (bool) $customer->discountGroup->is_active,
                ] : null,
            ])->all(),
            'pagination' => [
                'more' => ($page * $perPage) < $total,
            ],
        ]);
    }

    private function discountLabel(Discount $discount): string
    {
        $value = $discount->discount_type === Discount::TYPE_PERCENTAGE
            ? rtrim(rtrim(number_format((float) $discount->value, 2, '.', ''), '0'), '.').'%'
            : '$'.number_format((float) $discount->value, 2);

        return trim($discount->name.' - '.$value);
    }

    private function discountGroupLabel(DiscountGroup $group): string
    {
        $value = $group->type === DiscountGroup::TYPE_PERCENTAGE
            ? rtrim(rtrim(number_format((float) $group->value, 2, '.', ''), '0'), '.').'%'
            : '$'.number_format((float) $group->value, 2);

        return trim($group->name.' - '.$value);
    }

    public function vehicles(Request $request): JsonResponse
    {
        $search = trim((string) $request->string('q')->toString());
        $perPage = min((int) $request->integer('per_page', 20), 50);
        $page = max((int) $request->integer('page', 1), 1);
        $customerId = $request->input('customer_id');

        $query = Vehicle::query()
            ->with('customer:id,name')
            ->select(['id', 'customer_id', 'plate_number', 'registration_number', 'make', 'model', 'year', 'is_default'])
            ->when($customerId, function ($q) use ($customerId) {
                return $q->where('customer_id', $customerId);
            }, function ($q) {
                return $q->whereRaw('0 = 1');
            })
            ->search($search)
            ->orderByDesc('is_default')
            ->orderBy('plate_number')
            ->orderBy('id');

        $total = (clone $query)->count();
        $vehicles = $query
            ->forPage($page, $perPage)
            ->get();

        return response()->json([
            'results' => $vehicles->map(fn (Vehicle $vehicle) => [
                'id' => $vehicle->id,
                'text' => trim(collect([$vehicle->make, $vehicle->model, $vehicle->year])->filter()->implode(' ')),
                'plate_number' => $vehicle->plate_number,
                'registration_number' => $vehicle->registration_number,
                'customer_id' => $vehicle->customer_id,
                'customer_name' => $vehicle->customer?->name,
                'is_default' => $vehicle->is_default,
            ])->all(),
            'pagination' => [
                'more' => ($page * $perPage) < $total,
            ],
        ]);
    }
}
