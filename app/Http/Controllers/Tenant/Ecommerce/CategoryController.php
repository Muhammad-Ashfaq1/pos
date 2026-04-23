<?php

namespace App\Http\Controllers\Tenant\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{


public function save(Request $request)
{
    $request->validate([
        'id' => 'nullable|exists:categories,id',
        'name' => [
            'required','string','max:255',
            Rule::unique('categories', 'name')
                ->ignore($request->id)

        ],
        'code' => 'nullable|string|max:50',
        'sort_order' => 'nullable|integer',
        'is_active' => 'nullable|boolean',
    ]);

     Category::updateOrCreate(
        ['id' => $request->id],
        [
            'name' => $request->name,
            'code' => $request->code,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]
    );

    return $this->getLatestCategory(
        true,
        $request->id ? 'Category updated successfully' : 'Category created successfully'
    );
}
    public function edit($id)
    {
        $category = Category::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }
      private function getLatestCategory($success = true, $message = 'Category saved successfully!', $html = null)
    {


       $categories = Category::latest()->get();

        if ($html === null) {
            $html = view('tenant.ecommerce.categories.data-table', compact('categories'))->render();

        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'html' => $html
        ]);
    }


 public function destroy($id)
{
    $category = Category::findOrFail($id);
    $category->delete();

    return $this->getLatestCategory(
        true,
        'Category deleted successfully'
    );
}
}
