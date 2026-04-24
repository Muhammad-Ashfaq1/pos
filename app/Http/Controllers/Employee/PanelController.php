<?php

namespace App\Http\Controllers\Employee;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PanelController
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $tenant = tenant();
        $stats = $this->stats($user, $tenant);

        return view('employee.dashboard', [
            'user' => $user,
            'tenant' => $tenant,
            'stats' => $stats,
            'summaryCards' => $this->summaryCards($stats),
            'actionCards' => $this->actionCards($user, $stats),
            'summaryRows' => $this->summaryRows($stats),
            'placeholders' => $this->placeholderCards(),
        ]);
    }

    public function workspace(Request $request): View
    {
        $user = $request->user();
        $tenant = tenant();
        $stats = $this->stats($user, $tenant);

        return view('employee.workspace', [
            'user' => $user,
            'tenant' => $tenant,
            'stats' => $stats,
            'workspaceTiles' => $this->workspaceTiles($user, $stats),
            'catalogCards' => $this->catalogCards($stats),
            'summaryRows' => $this->summaryRows($stats),
            'placeholders' => $this->placeholderCards(),
        ]);
    }

    public function account(Request $request): View
    {
        $user = $request->user();
        $tenant = tenant();
        $stats = $this->stats($user, $tenant);

        return view('employee.account', [
            'user' => $user,
            'tenant' => $tenant,
            'stats' => $stats,
            'permissionBadges' => $this->permissionBadges($user),
        ]);
    }

    private function stats(User $user, ?Tenant $tenant): array
    {
        $canViewProducts = $user->can('viewAny', Product::class);
        $canViewServices = $user->can('viewAny', Service::class);
        $canViewCustomers = $user->can('viewAny', Customer::class);
        $canViewVehicles = $user->can('viewAny', Vehicle::class);

        return [
            'today_label' => now()->format('D, d M'),
            'today_full_label' => now()->format('l, d F Y'),
            'workspace_name' => $tenant?->display_name ?? 'Employee Workspace',
            'products_total' => $canViewProducts ? Product::query()->count() : null,
            'active_products' => $canViewProducts ? Product::query()->where('is_active', true)->count() : null,
            'low_stock_products' => $canViewProducts
                ? Product::query()
                    ->where('track_inventory', true)
                    ->whereColumn('current_stock', '<=', 'reorder_level')
                    ->count()
                : null,
            'services_total' => $canViewServices ? Service::query()->count() : null,
            'active_services' => $canViewServices ? Service::query()->where('is_active', true)->count() : null,
            'technician_services' => $canViewServices ? Service::query()->where('requires_technician', true)->count() : null,
            'customers_total' => $canViewCustomers ? Customer::query()->count() : null,
            'vehicles_total' => $canViewVehicles ? Vehicle::query()->count() : null,
            'team_members' => $tenant?->users()->count() ?? User::query()->where('tenant_id', $user->tenant_id)->count(),
            'accessible_modules' => collect([$canViewProducts, $canViewServices, $canViewCustomers, $canViewVehicles])
                ->filter()
                ->count(),
        ];
    }

    private function summaryCards(array $stats): array
    {
        return collect([
            $stats['active_services'] !== null ? ['label' => 'Active Services', 'value' => $stats['active_services'], 'icon' => 'tabler-tool', 'theme' => 'primary'] : null,
            $stats['customers_total'] !== null ? ['label' => 'Customer Records', 'value' => $stats['customers_total'], 'icon' => 'tabler-users-group', 'theme' => 'success'] : null,
            $stats['vehicles_total'] !== null ? ['label' => 'Vehicle Records', 'value' => $stats['vehicles_total'], 'icon' => 'tabler-car-garage', 'theme' => 'warning'] : null,
            $stats['active_products'] !== null ? ['label' => 'Active Products', 'value' => $stats['active_products'], 'icon' => 'tabler-package', 'theme' => 'info'] : null,
        ])->filter()->values()->all();
    }

    private function actionCards(User $user, array $stats): array
    {
        return collect([
            [
                'label' => 'POS / Quick Sale',
                'description' => 'Open the worker-facing workspace shell for quick sale, lookup, and future service execution.',
                'route' => route('employee.pos'),
                'icon' => 'tabler-cash-register',
                'theme' => 'primary',
                'badge' => 'Main Entry',
                'stat' => 'Ready now',
            ],
            [
                'label' => 'Assigned Services / Queue',
                'description' => 'Prepared queue section until a tenant-scoped service-job or order table is added.',
                'route' => route('employee.pos'),
                'icon' => 'tabler-clipboard-list',
                'theme' => 'warning',
                'badge' => 'Placeholder',
                'stat' => 'No table yet',
            ],
            $user->can('viewAny', Customer::class) ? [
                'label' => 'Customers Lookup',
                'description' => 'Search customer records already stored for this workspace.',
                'route' => route('tenant.ecommerce.customers.index'),
                'icon' => 'tabler-users',
                'theme' => 'success',
                'badge' => 'Live Data',
                'stat' => ($stats['customers_total'] ?? 0).' records',
            ] : null,
            $user->can('viewAny', Vehicle::class) ? [
                'label' => 'Vehicles Lookup',
                'description' => 'Review vehicle records, plate numbers, and customer links.',
                'route' => route('tenant.ecommerce.vehicles.index'),
                'icon' => 'tabler-car',
                'theme' => 'info',
                'badge' => 'Live Data',
                'stat' => ($stats['vehicles_total'] ?? 0).' vehicles',
            ] : null,
            $user->can('viewAny', Product::class) ? [
                'label' => 'Products Catalog',
                'description' => 'Open the read-safe product catalog with stock-aware counts.',
                'route' => route('tenant.ecommerce.products.index'),
                'icon' => 'tabler-package',
                'theme' => 'secondary',
                'badge' => 'Reference',
                'stat' => ($stats['products_total'] ?? 0).' products',
            ] : null,
            $user->can('viewAny', Service::class) ? [
                'label' => 'Services Catalog',
                'description' => 'Browse service definitions and technician-required offerings.',
                'route' => route('tenant.ecommerce.services.index'),
                'icon' => 'tabler-tool',
                'theme' => 'dark',
                'badge' => 'Reference',
                'stat' => ($stats['active_services'] ?? 0).' active',
            ] : null,
        ])->filter()->values()->all();
    }

    private function workspaceTiles(User $user, array $stats): array
    {
        return collect([
            [
                'label' => 'Start Service / Order',
                'description' => 'Daily-start shell reserved for later POS or service-order implementation.',
                'route' => null,
                'icon' => 'tabler-shopping-cart-plus',
                'theme' => 'primary',
                'status' => 'Placeholder',
                'meta' => 'No order tables yet',
            ],
            $user->can('viewAny', Customer::class) ? [
                'label' => 'Search Customer',
                'description' => 'Open customer lookup and service-ready customer records.',
                'route' => route('tenant.ecommerce.customers.index'),
                'icon' => 'tabler-search',
                'theme' => 'success',
                'status' => 'Available',
                'meta' => ($stats['customers_total'] ?? 0).' customers',
            ] : null,
            $user->can('viewAny', Vehicle::class) ? [
                'label' => 'Search Vehicle',
                'description' => 'Review plates, registrations, and customer-linked vehicles.',
                'route' => route('tenant.ecommerce.vehicles.index'),
                'icon' => 'tabler-steering-wheel',
                'theme' => 'warning',
                'status' => 'Available',
                'meta' => ($stats['vehicles_total'] ?? 0).' vehicles',
            ] : null,
            $user->can('viewAny', Product::class) ? [
                'label' => 'View Products',
                'description' => 'Inspect products, units, and low-stock-sensitive items.',
                'route' => route('tenant.ecommerce.products.index'),
                'icon' => 'tabler-package',
                'theme' => 'info',
                'status' => 'Available',
                'meta' => ($stats['products_total'] ?? 0).' products',
            ] : null,
            $user->can('viewAny', Service::class) ? [
                'label' => 'View Services',
                'description' => 'Open service pricing, duration, and technician-required work.',
                'route' => route('tenant.ecommerce.services.index'),
                'icon' => 'tabler-tool',
                'theme' => 'secondary',
                'status' => 'Available',
                'meta' => ($stats['services_total'] ?? 0).' services',
            ] : null,
            [
                'label' => 'Today\'s Work Summary',
                'description' => 'Jump to today\'s live tenant-scoped counts and workspace readiness details.',
                'route' => '#today-summary',
                'icon' => 'tabler-calendar-stats',
                'theme' => 'dark',
                'status' => 'Live Data',
                'meta' => $stats['today_label'],
            ],
        ])->filter()->values()->all();
    }

    private function catalogCards(array $stats): array
    {
        return collect([
            $stats['products_total'] !== null ? [
                'title' => 'Products Ready',
                'value' => $stats['products_total'],
                'meta' => ($stats['low_stock_products'] ?? 0).' low-stock watch items',
                'icon' => 'tabler-package',
                'theme' => 'info',
            ] : null,
            $stats['services_total'] !== null ? [
                'title' => 'Services Ready',
                'value' => $stats['services_total'],
                'meta' => ($stats['technician_services'] ?? 0).' technician-required services',
                'icon' => 'tabler-tool',
                'theme' => 'primary',
            ] : null,
            [
                'title' => 'Accessible Modules',
                'value' => $stats['accessible_modules'],
                'meta' => 'Products, services, customers, and vehicles only appear when permitted',
                'icon' => 'tabler-layout-grid',
                'theme' => 'success',
            ],
            [
                'title' => 'Workspace Team',
                'value' => $stats['team_members'],
                'meta' => 'Current tenant-scoped staff count',
                'icon' => 'tabler-users-group',
                'theme' => 'warning',
            ],
        ])->filter()->values()->all();
    }

    private function summaryRows(array $stats): array
    {
        return [
            ['label' => 'Date', 'value' => $stats['today_full_label']],
            ['label' => 'Active Services', 'value' => $stats['active_services'] ?? 'N/A'],
            ['label' => 'Customer Records', 'value' => $stats['customers_total'] ?? 'N/A'],
            ['label' => 'Vehicle Records', 'value' => $stats['vehicles_total'] ?? 'N/A'],
            ['label' => 'Low Stock Watch', 'value' => $stats['low_stock_products'] ?? 'N/A'],
            ['label' => 'Team Members', 'value' => $stats['team_members']],
        ];
    }

    private function placeholderCards(): array
    {
        return [
            [
                'title' => 'Assigned Services',
                'description' => 'No service-job or assignment table exists yet, so this remains a clean placeholder for future tenant-scoped queue data.',
                'icon' => 'tabler-calendar-time',
            ],
            [
                'title' => 'Notifications & Reminders',
                'description' => 'The workspace can later plug employee-targeted reminders and alerts into this area once notification records exist.',
                'icon' => 'tabler-bell-ringing',
            ],
            [
                'title' => 'Order / Work Queue',
                'description' => 'Service execution, queue priority, and completion cards will attach here when the POS or order module is implemented.',
                'icon' => 'tabler-clipboard-data',
            ],
        ];
    }

    private function permissionBadges(User $user): array
    {
        $permissionMap = [
            'dashboard.view' => 'Dashboard',
            'product.view' => 'Products',
            'products.view' => 'Product Listing',
            'service.view' => 'Services',
            'services.view' => 'Service Listing',
            'customer.view' => 'Customers',
            'customers.view' => 'Customer Listing',
            'vehicle.view' => 'Vehicles',
            'vehicles.view' => 'Vehicle Listing',
        ];

        return collect($permissionMap)
            ->filter(fn (string $label, string $permission): bool => $user->can($permission))
            ->values()
            ->all();
    }
}
