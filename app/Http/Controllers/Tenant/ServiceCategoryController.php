<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ServiceCategories\SaveServiceCategoryRequest;
use App\Models\ServiceCategory;
use App\Repositories\Interface\ServiceCategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceCategoryController extends Controller
{
    public function __construct(
        private readonly ServiceCategoryRepositoryInterface $repo
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ServiceCategory::class);

        return $this->repo->index();
    }

    public function listing(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ServiceCategory::class);

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
            $this->repo->getServiceCategoriesListing($validated, $request->user())
        );
    }

    public function save(SaveServiceCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $serviceCategory = isset($validated['id'])
            ? ServiceCategory::query()->findOrFail($validated['id'])
            : null;

        if ($serviceCategory) {
            $this->authorize('update', $serviceCategory);
        } else {
            $this->authorize('create', ServiceCategory::class);
        }

        $result = $this->repo->store(
            Arr::except($validated, ['id']),
            $serviceCategory,
            $request->user(),
        );

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }

    public function destroy(ServiceCategory $serviceCategory): JsonResponse
    {
        $this->authorize('delete', $serviceCategory);

        $result = $this->repo->destroy($serviceCategory);

        return response()->json([
            'message' => $result['message'],
        ]);
    }
}
