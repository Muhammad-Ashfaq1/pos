<?php

namespace App\Http\Controllers\Tenant;

use App\Services\TenantDashboardService;
use App\Support\DashboardDateRange;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController
{
    public function __construct(
        private readonly TenantDashboardService $dashboard
    ) {}

    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user()?->isEmployee()) {
            return redirect()->route('employee.dashboard');
        }

        $range = DashboardDateRange::fromRequest(
            $request->query('period'),
            $request->query('start'),
            $request->query('end'),
        );

        $tenant = tenant();
        $data = $this->dashboard->metrics($tenant, $range);

        return view('tenant.dashboard', array_merge($data, ['tenant' => $tenant]));
    }
}
