<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Products\ListProductsRequest;
use App\Http\Requests\Tenant\Products\SaveProductRequest;
use App\Models\Product;
use App\Models\ProductType;
use App\Repositories\Interface\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $repo
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $isEmployee = str_starts_with($request->route()?->getName() ?? '', 'employee.');

        if ($isEmployee) {
            return view('employee.products.index', [
                'listingUrl'              => route('employee.products.listing'),
                'editUrlTemplate'         => route('employee.products.edit', ['product' => '__PRODUCT__']),
                'categoriesDropdownUrl'   => route('tenant.ecommerce.dropdowns.categories'),
                'subCategoriesDropdownUrl'=> route('tenant.ecommerce.dropdowns.subcategories'),
                'discountDropdownUrl'     => route('tenant.ecommerce.dropdowns.discounts'),
                'saveUrl'                 => route('employee.products.save'),
                'productTypes'            => $this->productTypes(),
            ]);
        }

        return $this->repo->index();
    }

    public function listing(ListProductsRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        return response()->json(
            $this->repo->getProductsListing($request->validated(), $request->user())
        );
    }

    public function edit(Product $product, Request $request): JsonResponse
    {
        $this->authorize('update', $product);

        return response()->json([
            'data' => $this->repo->getProductFormData($product, $request->user()),
        ]);
    }

    public function save(SaveProductRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $product = isset($validated['id'])
            ? Product::query()->findOrFail($validated['id'])
            : null;

        if ($product) {
            $this->authorize('update', $product);
        } else {
            $this->authorize('create', Product::class);
        }

        $result = $this->repo->store(
            Arr::except($validated, ['id']),
            $product,
            $request->user(),
            $request->file('images', []),
        );

        return response()->json([
            'message' => $result['message'],
            'data'    => $result['data'],
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $result = $this->repo->destroy($product);

        return response()->json([
            'message' => $result['message'],
        ]);
    }

    private function productTypes(): array
    {
        return ProductType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
