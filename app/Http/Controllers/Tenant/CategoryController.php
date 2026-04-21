<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Categories\SaveCategoryRequest;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Category::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['1', '0'])],
            'sort' => ['nullable', Rule::in(['latest', 'name', 'sort_order'])],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ]);

        $filters = [
            'search' => $validated['search'] ?? '',
            'status' => $validated['status'] ?? '',
            'sort' => $validated['sort'] ?? 'latest',
            'per_page' => (int) ($validated['per_page'] ?? 15),
        ];

        $categories = Category::query()
            ->search($filters['search'])
            ->when($filters['status'] !== '', function (Builder $query) use ($filters): void {
                $query->where('is_active', $filters['status'] === '1');
            });

        match ($filters['sort']) {
            'name' => $categories->orderBy('name')->orderBy('id'),
            'sort_order' => $categories->orderBy('sort_order')->orderBy('name')->orderBy('id'),
            default => $categories->latest(),
        };

        return view('tenant.ecommerce.categories.index', [
            'categories' => $categories->paginate($filters['per_page'])->withQueryString(),
            'filters' => $filters,
            'sortOptions' => [
                'latest' => 'Latest',
                'name' => 'Name A-Z',
                'sort_order' => 'Sort Order Low-High',
            ],
        ]);
    }

    public function save(SaveCategoryRequest $request): JsonResponse
    {
        $category = $request->category();
        $payload = $request->payload();
        $userId = $request->user()?->getAuthIdentifier();
        $isUpdate = $category !== null;

        if ($isUpdate) {
            $category->fill($payload);
            $category->forceFill(['updated_by' => $userId])->save();
        } else {
            $category = new Category($payload);
            $category->forceFill([
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
            $category->save();
        }

        $message = $isUpdate ? 'Category updated successfully.' : 'Category created successfully.';
        $request->session()->flash('success', $message);

        return response()->json([
            'message' => $message,
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'code' => $category->code,
                'description' => $category->description,
                'sort_order' => $category->sort_order,
                'is_active' => $category->is_active,
            ],
        ]);
    }

    public function toggleStatus(Request $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);

        $category->forceFill([
            'is_active' => ! $category->is_active,
            'updated_by' => $request->user()?->getAuthIdentifier(),
        ])->save();

        return response()->json([
            'message' => 'Category status updated successfully.',
            'data' => [
                'id' => $category->id,
                'is_active' => $category->is_active,
                'status_label' => $category->is_active ? 'Active' : 'Inactive',
                'status_badge_class' => $category->is_active ? 'bg-label-success' : 'bg-label-secondary',
            ],
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }
}
