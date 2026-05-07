<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
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

    public function customers(Request $request): JsonResponse
    {
        $search = trim((string) $request->string('q')->toString());
        $perPage = min((int) $request->integer('per_page', 20), 50);
        $page = max((int) $request->integer('page', 1), 1);
        $customerType = trim((string) $request->string('customer_type')->toString());

        $query = Customer::query()
            ->select(['id', 'customer_type', 'name', 'phone', 'email'])
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
                'text' => collect([$customer->name, $customer->phone])->filter()->implode(' - '),
                'name' => $customer->name,
                'customer_type' => $customer->customer_type,
                'phone' => $customer->phone,
                'email' => $customer->email,
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
        $customerId = $request->integer('customer_id') ?: null;

        $query = Vehicle::query()
            ->with('customer:id,name')
            ->select(['id', 'customer_id', 'plate_number', 'registration_number', 'make', 'model', 'year', 'is_default'])
            ->when($customerId, fn ($builder) => $builder->where('customer_id', $customerId))
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
                'text' => collect([
                    $vehicle->plate_number,
                    trim(collect([$vehicle->make, $vehicle->model, $vehicle->year])->filter()->implode(' ')),
                ])->filter()->implode(' - '),
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
