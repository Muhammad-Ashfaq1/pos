<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;



class EcommerceController extends Controller
{
    public function categories()
{
    $categories = Category::latest()->get();

    return view('tenant.ecommerce.categories.index', [
        'categories' => $categories
    ]);
}
    public function subCategories()
    {
        return view('tenant.ecommerce.sub-categories.index');
    }

    public function products()
    {
        return view('tenant.ecommerce.products.index');
    }
}
