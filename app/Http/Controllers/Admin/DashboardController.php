<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\View\View;

class DashboardController
{
    public function __invoke(): View
    {
        $stats = [
            'tenants_total' => Tenant::query()->count(),
            'tenants_pending' => Tenant::query()->where('status', 'pending')->count(),
            'tenants_approved' => Tenant::query()->where('status', 'approved')->count(),
            'tenant_admins' => User::query()->where('role', User::TENANT_ADMIN)->count(),
        ];

        $recentTenants = Tenant::query()->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentTenants'));
    }
}
