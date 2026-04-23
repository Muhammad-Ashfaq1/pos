<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;



class EcommerceController extends Controller
{
    public function categories()
{
   $categories = Category::get();

    return view('tenant.ecommerce.categories.index', compact('categories'));
}


    public function subCategories()

    {

       $categories = Category::active()->latest()->get();

    return view('tenant.ecommerce.sub-categories.index', compact('categories'));
    }

    public function products()
    {
        return view('tenant.ecommerce.products.index');
    }
}
