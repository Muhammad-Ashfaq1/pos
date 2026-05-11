<?php
namespace App\Http\Controllers\Employee;

use Illuminate\View\View;

class PanelController
{
    public function dashboard(): View
    {
        return view('employee.dashboard');
    }

    public function newOrder(): View
    {
        $discountGroups = \App\Models\DiscountGroup::where('is_active', true)->get();
        return view('employee.order.new-order', compact('discountGroups'));
    }
}
