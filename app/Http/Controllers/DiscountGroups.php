<?php
namespace App\Http\Controllers;

class DiscountGroups extends Controller
{
    public function index()
    {
        return view('tenant.ecommerce.discounts.group.index');
    }
}
