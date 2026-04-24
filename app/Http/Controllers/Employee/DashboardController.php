<?php

namespace App\Http\Controllers\Employee;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $tenant = tenant();

        $stats = [
            'services_total' => $user->can('viewAny', Service::class) ? Service::query()->count() : null,
            'technician_services' => $user->can('viewAny', Service::class)
                ? Service::query()->where('requires_technician', true)->count()
                : null,
            'customers_total' => $user->can('viewAny', Customer::class) ? Customer::query()->count() : null,
            'vehicles_total' => $user->can('viewAny', Vehicle::class) ? Vehicle::query()->count() : null,
            'products_total' => $user->can('viewAny', Product::class) ? Product::query()->count() : null,
            'low_stock_products' => $user->can('viewAny', Product::class)
                ? Product::query()
                    ->where('track_inventory', true)
                    ->whereColumn('current_stock', '<=', 'reorder_level')
                    ->count()
                : null,
            'team_members' => $tenant?->users()->count() ?? User::query()->where('tenant_id', $user->tenant_id)->count(),
        ];

        $quickActions = collect([
            $user->can('viewAny', Service::class) ? [
                'label' => 'Services',
                'description' => 'Browse active services and service setup details.',
                'route' => route('tenant.ecommerce.services.index'),
                'icon' => 'tabler-tool',
                'color' => 'primary',
            ] : null,
            $user->can('viewAny', Customer::class) ? [
                'label' => 'Customers',
                'description' => 'Open customer profiles available to your role.',
                'route' => route('tenant.ecommerce.customers.index'),
                'icon' => 'tabler-users',
                'color' => 'success',
            ] : null,
            $user->can('viewAny', Vehicle::class) ? [
                'label' => 'Vehicles',
                'description' => 'Review tenant-scoped vehicle records and history.',
                'route' => route('tenant.ecommerce.vehicles.index'),
                'icon' => 'tabler-car',
                'color' => 'warning',
            ] : null,
            $user->can('viewAny', Product::class) ? [
                'label' => 'Products',
                'description' => 'Inspect products and inventory-ready catalog items.',
                'route' => route('tenant.ecommerce.products.index'),
                'icon' => 'tabler-package',
                'color' => 'info',
            ] : null,
        ])->filter()->values();

        $placeholderCards = [
            [
                'title' => 'Today Assigned Work',
                'icon' => 'tabler-calendar-time',
                'badge' => 'Awaiting Job Module',
                'description' => 'No tenant-scoped assignment table exists yet, so this panel is ready for future work orders.',
            ],
            [
                'title' => 'Pending Service Jobs',
                'icon' => 'tabler-clipboard-list',
                'badge' => 'Safe Placeholder',
                'description' => 'Service job tracking has not been implemented in this POS codebase yet.',
            ],
            [
                'title' => 'Completed Jobs',
                'icon' => 'tabler-circle-check',
                'badge' => 'Safe Placeholder',
                'description' => 'Completed-work metrics will appear here once service execution records are available.',
            ],
        ];

        return view('employee.dashboard', compact('user', 'tenant', 'stats', 'quickActions', 'placeholderCards'));
    }
}
