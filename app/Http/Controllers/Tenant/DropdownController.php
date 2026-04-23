<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DropdownController extends Controller
{
    public function categories(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()?->canAny([
                'category.view',
                'category.create',
                'category.update',
                'subcategory.view',
                'subcategory.create',
                'subcategory.update',
            ]),
            403
        );

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
}
