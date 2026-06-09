<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Serves the customer-portal Blade shells. All data is loaded client-side from
 * the /api/v1/customer/* endpoints (Sanctum token), so these are static pages.
 */
class PortalController extends Controller
{
    public function login(): View
    {
        return view('customer.login');
    }

    public function reset(): View
    {
        return view('customer.reset');
    }

    public function app(): View
    {
        return view('customer.app');
    }
}
