<?php

namespace App\Http\Controllers\Tenant\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubCategory;
use Illuminate\Validation\Rule;


class SubCategoryController extends Controller
{

public function list()
{
    $subCategories = SubCategory::with('category')->latest()->get();

    return response()->json([
        'data' => $subCategories
    ]);
}



    public function save(Request $request)
    {
        $request->validate([
            'id' => 'nullable|exists:sub_categories,id',
            'category_id' => 'required|exists:categories,id',
            'name' => [ 'required','string','max:255',
         Rule::unique('sub_categories', 'name')
        ->ignore($request->id)
        ->where(fn ($q) => $q->where('tenant_id', tenant('id')))
],

            'code' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
             'is_active' => 'nullable|boolean',
        ]);

        $subCategory = SubCategory::updateOrCreate(
            [
                'id' => $request->id
            ],
            [
                'category_id' => $request->category_id,
                'name' => $request->name,
                'code' => $request->code,
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => $request->boolean('is_active'),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => $request->id ? 'SubCategory updated successfully' : 'SubCategory created successfully',
            'data' => $subCategory
        ]);
    }


    public function edit($id)
    {
          $subCategory = SubCategory::with('category')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $subCategory
        ]);
    }


    public function destroy($id)
    {
        SubCategory::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'SubCategory deleted successfully'
        ]);
    }
}
