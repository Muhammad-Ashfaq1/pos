<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Categories\SaveCategoryRequest;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Category::class);

        return view('tenant.ecommerce.categories.index', [
            'listingUrl' => route('tenant.ecommerce.categories.listing'),
        ]);
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

        $start = (int) ($validated['start'] ?? 0);
        $length = (int) ($validated['length'] ?? 10);
        $search = data_get($validated, 'search.value', '');
        $status = $validated['status'] ?? '';
        $sort = $validated['sort'] ?? 'latest';

        $baseQuery = Category::query();
        $filteredQuery = Category::query()
            ->search($search)
            ->when($status !== '', function (Builder $query) use ($status): void {
                $query->where('is_active', $status === '1');
            });

        match ($sort) {
            'name' => $filteredQuery->orderBy('name')->orderBy('id'),
            'sort_order' => $filteredQuery->orderBy('sort_order')->orderBy('name')->orderBy('id'),
            default => $filteredQuery->latest(),
        };

        $recordsTotal = (clone $baseQuery)->count();
        $recordsFiltered = (clone $filteredQuery)->count();
        $categories = $filteredQuery
            ->skip($start)
            ->take($length)
            ->get();

        return response()->json([
            'draw' => (int) ($validated['draw'] ?? 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $this->transformCategories($categories, $request),
        ]);
    }

    public function save(SaveCategoryRequest $request): JsonResponse
    {
        $category = $request->category();
        $payload = $request->payload();
        $user = $request->user();
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

        return response()->json([
            'message' => $message,
            'data' => [
                ...$this->transformCategory($category, $request),
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

    private function transformCategories(Collection $categories, Request $request): array
    {
        return $categories
            ->map(fn (Category $category) => $this->transformCategory($category, $request))
            ->all();
    }

    private function transformCategory(Category $category, Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $category->id,
            'name' => $category->name,
            'code' => $category->code,
            'description' => $category->description,
            'sort_order' => $category->sort_order,
            'is_active' => $category->is_active,
            'status_label' => $category->is_active ? 'Active' : 'Inactive',
            'status_badge_class' => $category->is_active ? 'bg-label-success' : 'bg-label-secondary',
            'created_at' => $category->created_at?->format('d M Y'),
            'can_update' => $user?->can('update', $category) ?? false,
            'can_delete' => $user?->can('delete', $category) ?? false,
            'delete_url' => $user?->can('delete', $category)
                ? route('tenant.ecommerce.categories.destroy', $category)
                : null,
        ];
    }
}
