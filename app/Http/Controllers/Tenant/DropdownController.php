<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\DiscountGroup;
use App\Models\Product;
use App\Models\Service;
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

    public function services(Request $request): JsonResponse
    {
        $search = trim((string) $request->string('q')->toString());
        $perPage = min((int) $request->integer('per_page', 20), 50);
        $page = max((int) $request->integer('page', 1), 1);
        $activeOnly = $request->boolean('active_only', true);

        $query = Service::query()
            ->with('category:id,name')
            ->select(['id', 'category_id', 'name', 'code', 'standard_price', 'tax_percentage', 'is_active'])
            ->when($activeOnly, fn ($builder) => $builder->where('is_active', true))
            ->search($search)
            ->orderBy('name')
            ->orderBy('id');

        $total = (clone $query)->count();
        $services = $query
            ->forPage($page, $perPage)
            ->get();

        return response()->json([
            'results' => $services->map(fn (Service $service) => [
                'id' => $service->id,
                'text' => $this->serviceOptionText($service),
                'name' => $service->name,
                'code' => $service->code,
                'category_name' => $service->category?->name,
                'standard_price' => (float) $service->standard_price,
                'tax_percentage' => $service->tax_percentage !== null ? (float) $service->tax_percentage : 0.0,
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
        $activeOnly = $request->boolean('active_only', true);
        $appliesTo = trim((string) $request->string('applies_to')->toString());

        $query = Discount::query()
            ->select(['id', 'name', 'code', 'discount_type', 'value', 'applies_to', 'is_active'])
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
                'text' => $this->discountOptionText($discount),
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
                'discount_group' => $this->discountGroupPayload($customer->discountGroup),
            ])->all(),
            'pagination' => [
                'more' => ($page * $perPage) < $total,
            ],
        ]);
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

    public function discountGroups(Request $request): JsonResponse
    {
        $search = trim((string) $request->string('q')->toString());
        $activeOnly = $request->boolean('active_only', true);

        $query = DiscountGroup::query()
            ->select(['id', 'name'])
            ->when($activeOnly, fn ($builder) => $builder->where('is_active', true))
            ->where('name', 'like', "%{$search}%")
            ->orderBy('name');

        $groups = $query->get();

        return response()->json([
            'results' => $groups->map(fn ($group) => [
                'id' => $group->id,
                'text' => $group->name,
            ])->all(),
        ]);
    }

    private function discountOptionText(Discount $discount): string
    {
        $value = $discount->discount_type === Discount::TYPE_PERCENTAGE
            ? rtrim(rtrim(number_format((float) $discount->value, 2, '.', ''), '0'), '.').'%'
            : '$'.number_format((float) $discount->value, 2);
        $code = filled($discount->code) ? " ({$discount->code})" : '';

        return "{$discount->name}{$code} - {$value}";
    }

    private function serviceOptionText(Service $service): string
    {
        $code = filled($service->code) ? " ({$service->code})" : '';
        $price = '$'.number_format((float) $service->standard_price, 2);

        return "{$service->name}{$code} - {$price}";
    }

    private function discountGroupPayload(?DiscountGroup $group): ?array
    {
        if (! $group || ! $group->is_active) {
            return null;
        }

        return [
            'id' => $group->id,
            'name' => $group->name,
            'type' => $group->type,
            'value' => (float) $group->value,
            'min_limit' => (float) $group->min_limit,
            'is_active' => (bool) $group->is_active,
            'label' => $this->discountGroupLabel($group),
        ];
    }

    private function discountGroupLabel(DiscountGroup $group): string
    {
        $value = $group->type === 'percentage'
            ? rtrim(rtrim(number_format((float) $group->value, 2, '.', ''), '0'), '.').'%'
            : '$'.number_format((float) $group->value, 2);

        return trim("{$group->name} - {$value}");
    }
}
