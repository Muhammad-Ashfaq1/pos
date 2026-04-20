<?php

namespace App\Http\Controllers\Tenant\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{

public function list()
{
    $categories = Category::latest()->get();

    return response()->json([
        'data' => $categories
    ]);
}

public function save(Request $request)
{
    $request->validate([
        'id' => 'nullable|exists:categories,id',
       'name' => [  'required','string','max:255',
      Rule::unique('categories', 'name')->ignore($request->id)->where(fn ($q) => $q->where('tenant_id', tenant('id')))
],
        'code' => 'nullable|string|max:50',
        'sort_order' => 'nullable|integer',
        'is_active' => 'nullable|boolean',
    ]);

    $category = Category::updateOrCreate(
        [
            'id' => $request->id
        ],
        [
            'name' => $request->name,
            'code' => $request->code,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->boolean('is_active'),

        ]
    );

    return response()->json([
        'success' => true,
        'message' => $request->id ? 'Category updated successfully' : 'Category added successfully',
        'data' => $category
    ]);
}
    public function edit($id)
    {
        $category = Category::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }


    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
