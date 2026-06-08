<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ProductTypes\SaveProductTypeRequest;
use App\Models\ProductType;
use App\Repositories\Interface\ProductTypeRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductTypeController extends Controller
{
    public function __construct(
        private readonly ProductTypeRepositoryInterface $repo
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ProductType::class);

        return $this->repo->index();
    }

    public function listing(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ProductType::class);

        $validated = $request->validate([
            'draw' => ['nullable', 'integer'],
            'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search.value' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['1', '0'])],
            'sort' => ['nullable', Rule::in(['latest', 'name', 'sort_order'])],
            'columns' => ['nullable', 'array'],
            'columns.*.data' => ['nullable', 'string'],
            'order' => ['nullable', 'array'],
            'order.*.column' => ['nullable', 'integer', 'min:0'],
            'order.*.dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        return response()->json(
            $this->repo->getProductTypesListing($validated, $request->user())
        );
    }

    public function save(SaveProductTypeRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $productType = isset($validated['id'])
            ? ProductType::query()->findOrFail($validated['id'])
            : null;

        if ($productType) {
            $this->authorize('update', $productType);
        } else {
            $this->authorize('create', ProductType::class);
        }

        $result = $this->repo->store(
            Arr::except($validated, ['id']),
            $productType,
            $request->user(),
        );

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }

    public function destroy(ProductType $productType): JsonResponse
    {
        $this->authorize('delete', $productType);

        $result = $this->repo->destroy($productType);

        return response()->json([
            'message' => $result['message'],
        ], $result['success'] ? 200 : 422);
    }
}
