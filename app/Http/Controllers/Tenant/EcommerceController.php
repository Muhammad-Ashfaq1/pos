<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;

class EcommerceController extends Controller
{
    public function categories()
    {
        return view('tenant.ecommerce.categories.index');
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
