<?php

namespace App\Http\Controllers\Tenant;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user()?->isEmployee()) {
            return redirect()->route('employee.dashboard');
        }

        $tenant = tenant();

        $stats = [
            'status' => $tenant?->status?->value ?? 'unknown',
            'onboarding_status' => $tenant?->onboarding_state ?? 'not_started',
            'team_members' => $tenant?->users()->count() ?? 0,
        ];

        return view('tenant.dashboard', compact('tenant', 'stats'));
    }
}
