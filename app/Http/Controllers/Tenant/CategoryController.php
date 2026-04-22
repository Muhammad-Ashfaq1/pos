<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Categories\SaveCategoryRequest;
use App\Models\Category;
use App\Repositories\Interface\CategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryRepositoryInterface $repo
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Category::class);

        return $this->repo->index();
    }

    public function listing(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Category::class);

        $validated = $request->validate([
            'draw' => ['nullable', 'integer'],
            'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search.value' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['1', '0'])],
            'sort' => ['nullable', Rule::in(['latest', 'name', 'sort_order'])],
        ]);

        return response()->json(
            $this->repo->getCategoriesListing($validated, $request->user())
        );
    }

    public function save(SaveCategoryRequest $request): JsonResponse
    {
        $result = $this->repo->store(
            $request->payload(),
            $request->category(),
            $request->user(),
        );

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);

        $result = $this->repo->destroy($category);

        return response()->json([
            'message' => $result['message'],
        ]);
    }
}
