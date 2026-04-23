<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Products\SaveProductRequest;
use App\Models\Product;
use App\Repositories\Interface\ProductRepositoryInterface;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $repo
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        return $this->repo->index();
    }

    public function listing(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        $validated = $request->validate([
            'draw' => ['nullable', 'integer'],
            'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search.value' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['1', '0'])],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(
                    fn ($query) => $query->where('tenant_id', app(TenantContext::class)->id())
                ),
            ],
            'sub_category_id' => [
                'nullable',
                'integer',
                Rule::exists('sub_categories', 'id')->where(
                    fn ($query) => $query->where('tenant_id', app(TenantContext::class)->id())
                ),
            ],
            'product_type' => ['nullable', Rule::in(array_keys(Product::typeOptions()))],
            'track_inventory' => ['nullable', Rule::in(['1', '0'])],
            'sort' => ['nullable', Rule::in(['latest', 'name', 'price_low_high', 'stock_low_high'])],
            'columns' => ['nullable', 'array'],
            'columns.*.data' => ['nullable', 'string'],
            'order' => ['nullable', 'array'],
            'order.*.column' => ['nullable', 'integer', 'min:0'],
            'order.*.dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        if (
            ! empty($validated['category_id'])
            && ! empty($validated['sub_category_id'])
        ) {
            $belongsToCategory = \App\Models\SubCategory::query()
                ->whereKey($validated['sub_category_id'])
                ->where('category_id', $validated['category_id'])
                ->exists();

            if (! $belongsToCategory) {
                throw ValidationException::withMessages([
                    'sub_category_id' => 'The selected sub category does not belong to the selected category.',
                ]);
            }
        }

        return response()->json(
            $this->repo->getProductsListing($validated, $request->user())
        );
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
        );

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
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
}
