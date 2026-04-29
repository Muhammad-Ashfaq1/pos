<?php

namespace App\Http\Controllers\Employee;

use Illuminate\View\View;

class PanelController
{
    public function dashboard(): View
    {
        return view('employee.dashboard');
    }
}
