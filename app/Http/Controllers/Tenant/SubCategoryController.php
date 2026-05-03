<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\SubCategories\SaveSubCategoryRequest;
use App\Models\SubCategory;
use App\Repositories\Interface\SubCategoryRepositoryInterface;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubCategoryController extends Controller
{
    public function __construct(
        private readonly SubCategoryRepositoryInterface $repo
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SubCategory::class);

        return $this->repo->index();
    }

    public function listing(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SubCategory::class);

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
            'sort' => ['nullable', Rule::in(['latest', 'name', 'sort_order', 'category'])],
            'columns' => ['nullable', 'array'],
            'columns.*.data' => ['nullable', 'string'],
            'order' => ['nullable', 'array'],
            'order.*.column' => ['nullable', 'integer', 'min:0'],
            'order.*.dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        return response()->json(
            $this->repo->getSubCategoriesListing($validated, $request->user())
        );
    }

    public function save(SaveSubCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $subCategory = isset($validated['id'])
            ? SubCategory::query()->findOrFail($validated['id'])
            : null;

        if ($subCategory) {
            $this->authorize('update', $subCategory);
        } else {
            $this->authorize('create', SubCategory::class);
        }

        $result = $this->repo->store(
            Arr::except($validated, ['id']),
            $subCategory,
            $request->user(),
        );

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }

    public function destroy(SubCategory $subCategory): JsonResponse
    {
        $this->authorize('delete', $subCategory);

        $result = $this->repo->destroy($subCategory);

        return response()->json([
            'message' => $result['message'],
        ]);
    }
}
