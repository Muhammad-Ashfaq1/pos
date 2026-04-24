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
            'operatorCards' => $this->operatorCards($user),
            'summaryCards' => $this->summaryCards($stats),
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
            'workspaceTiles' => $this->workspaceTiles($user),
            'catalogCards' => $this->catalogCards($stats),
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
            $stats['customers_total'] !== null ? ['label' => 'Customer Lookup', 'value' => $stats['customers_total'], 'icon' => 'tabler-users', 'theme' => 'success'] : null,
            $stats['vehicles_total'] !== null ? ['label' => 'Vehicle Lookup', 'value' => $stats['vehicles_total'], 'icon' => 'tabler-car', 'theme' => 'warning'] : null,
            $stats['active_products'] !== null ? ['label' => 'Catalog Products', 'value' => $stats['active_products'], 'icon' => 'tabler-package', 'theme' => 'info'] : null,
        ])->filter()->values()->all();
    }

    private function operatorCards(User $user): array
    {
        return collect([
            [
                'label' => 'POS Workspace',
                'description' => 'Open the employee operating screen for lookup, service shell, and quick daily actions.',
                'route' => route('employee.workspace'),
                'icon' => 'tabler-cash-register',
                'theme' => 'primary',
                'badge' => 'Main Entry',
            ],
            [
                'label' => 'Start Service / Order',
                'description' => 'UI-ready shell for future service order flow without inventing order tables today.',
                'route' => route('employee.workspace'),
                'icon' => 'tabler-playlist-add',
                'theme' => 'warning',
                'badge' => 'Placeholder',
            ],
            $user->can('viewAny', Customer::class) ? [
                'label' => 'Customer Lookup',
                'description' => 'Search and open customer records inside the current tenant.',
                'route' => route('tenant.ecommerce.customers.index'),
                'icon' => 'tabler-users-group',
                'theme' => 'success',
                'badge' => 'Live Data',
            ] : null,
            $user->can('viewAny', Vehicle::class) ? [
                'label' => 'Vehicle Lookup',
                'description' => 'Review tenant-scoped vehicle records and service-linked context.',
                'route' => route('tenant.ecommerce.vehicles.index'),
                'icon' => 'tabler-car-garage',
                'theme' => 'info',
                'badge' => 'Live Data',
            ] : null,
            $user->can('viewAny', Product::class) ? [
                'label' => 'Products Catalog',
                'description' => 'Browse products and stock-sensitive catalog items safely.',
                'route' => route('tenant.ecommerce.products.index'),
                'icon' => 'tabler-package',
                'theme' => 'secondary',
                'badge' => 'Reference',
            ] : null,
            $user->can('viewAny', Service::class) ? [
                'label' => 'Services Catalog',
                'description' => 'Open service definitions, pricing, and technician-required work types.',
                'route' => route('tenant.ecommerce.services.index'),
                'icon' => 'tabler-tool',
                'theme' => 'dark',
                'badge' => 'Reference',
            ] : null,
        ])->filter()->values()->all();
    }

    private function workspaceTiles(User $user): array
    {
        return collect([
            [
                'label' => 'Start Service / Order',
                'description' => 'Prepared shell for the future employee transaction flow.',
                'route' => null,
                'icon' => 'tabler-shopping-cart-plus',
                'theme' => 'primary',
                'status' => 'Coming Next',
            ],
            $user->can('viewAny', Customer::class) ? [
                'label' => 'Search Customers',
                'description' => 'Open customer lookup and service-ready profiles.',
                'route' => route('tenant.ecommerce.customers.index'),
                'icon' => 'tabler-search',
                'theme' => 'success',
                'status' => 'Available',
            ] : null,
            $user->can('viewAny', Vehicle::class) ? [
                'label' => 'Search Vehicles',
                'description' => 'Review vehicles, plates, and linked customer info.',
                'route' => route('tenant.ecommerce.vehicles.index'),
                'icon' => 'tabler-steering-wheel',
                'theme' => 'warning',
                'status' => 'Available',
            ] : null,
            $user->can('viewAny', Product::class) ? [
                'label' => 'View Products',
                'description' => 'Inspect products, stock, and catalog details.',
                'route' => route('tenant.ecommerce.products.index'),
                'icon' => 'tabler-package',
                'theme' => 'info',
                'status' => 'Available',
            ] : null,
            $user->can('viewAny', Service::class) ? [
                'label' => 'View Services',
                'description' => 'Browse services and technician-required offerings.',
                'route' => route('tenant.ecommerce.services.index'),
                'icon' => 'tabler-tool',
                'theme' => 'secondary',
                'status' => 'Available',
            ] : null,
            [
                'label' => 'Today Work Queue',
                'description' => 'Reserved for assignments, service jobs, and queue tracking once those tables exist.',
                'route' => null,
                'icon' => 'tabler-clipboard-list',
                'theme' => 'dark',
                'status' => 'Placeholder',
            ],
            [
                'label' => 'Notifications',
                'description' => 'Prepared panel slot for reminders and employee alerts.',
                'route' => null,
                'icon' => 'tabler-bell',
                'theme' => 'warning',
                'status' => 'Placeholder',
            ],
            [
                'label' => 'Account',
                'description' => 'Review workspace access, identity, and tenant info.',
                'route' => route('employee.account'),
                'icon' => 'tabler-user-circle',
                'theme' => 'primary',
                'status' => 'Available',
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
                'meta' => 'Products, services, customers, and vehicles shown only when permitted',
                'icon' => 'tabler-layout-grid',
                'theme' => 'success',
            ],
            [
                'title' => 'Team Members',
                'value' => $stats['team_members'],
                'meta' => 'Current tenant workspace staff count',
                'icon' => 'tabler-users-group',
                'theme' => 'warning',
            ],
        ])->filter()->values()->all();
    }

    private function placeholderCards(): array
    {
        return [
            [
                'title' => 'Assigned Services',
                'description' => 'No service-job or assignment table exists yet, so this panel is intentionally prepared as a clean placeholder.',
                'icon' => 'tabler-calendar-time',
            ],
            [
                'title' => 'Work Queue',
                'description' => 'Pending and completed queue cards will plug into this screen once service execution records are added.',
                'icon' => 'tabler-clipboard-data',
            ],
            [
                'title' => 'Notifications & Reminders',
                'description' => 'The tenant already stores notification settings, but employee-targeted notification records do not exist yet.',
                'icon' => 'tabler-bell-ringing',
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
