@php
    $user = auth()->user();
    $currentRouteName = request()->route()?->getName();
    $menuGroups = collect([
        [
            'label' => 'Workspace',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'employee.dashboard', 'pattern' => 'employee.dashboard', 'icon' => 'tabler-layout-dashboard', 'visible' => true],
                ['label' => 'POS / Workspace', 'route' => 'employee.pos', 'pattern' => 'employee.workspace|employee.pos', 'icon' => 'tabler-cash-register', 'visible' => true],
                ['label' => 'Discounts', 'route' => 'employee.cards.type', 'routeParams' => ['type' => 'discount'], 'pattern' => 'employee.cards.*', 'icon' => 'tabler-ticket', 'visible' => $user?->can('cards.view') || $user?->can('cards.manage')],
            ],
        ],
        [
            'label' => 'Catalog',
            'items' => [
                ['label' => 'Products', 'route' => 'tenant.ecommerce.products.index', 'pattern' => 'tenant.ecommerce.products.*', 'icon' => 'tabler-package', 'visible' => $user?->can('product.view') || $user?->can('products.view') || $user?->can('products.manage')],
                ['label' => 'Manage Products', 'route' => 'employee.products.index', 'pattern' => 'employee.products.*', 'icon' => 'tabler-package-import', 'visible' => $user?->can('product.create') || $user?->can('product.update')],
                ['label' => 'Services', 'route' => 'tenant.ecommerce.services.index', 'pattern' => 'tenant.ecommerce.services.*', 'icon' => 'tabler-tool', 'visible' => $user?->can('service.view') || $user?->can('services.view')],
            ],
        ],
        [
            'label' => 'Lookup',
            'items' => [
                ['label' => 'Customers', 'route' => 'tenant.ecommerce.customers.index', 'pattern' => 'tenant.ecommerce.customers.*', 'icon' => 'tabler-users', 'visible' => $user?->can('customer.view') || $user?->can('customers.view')],
                ['label' => 'Vehicles', 'route' => 'tenant.ecommerce.vehicles.index', 'pattern' => 'tenant.ecommerce.vehicles.*', 'icon' => 'tabler-car', 'visible' => $user?->can('vehicle.view') || $user?->can('vehicles.view')],
            ],
        ],
    ])->map(function (array $group): array {
        $group['items'] = collect($group['items'])
            ->filter(fn (array $item): bool => (bool) $item['visible'])
            ->values()
            ->all();

        return $group;
    })->filter(fn (array $group): bool => ! empty($group['items']))->values();

    $isActive = function (string $pattern) use ($currentRouteName): bool {
        return collect(explode('|', $pattern))->contains(
            fn (string $segment): bool => str($currentRouteName ?? '')->is($segment)
        );
    };
@endphp

@once
    <style>
        #layout-menu.employee-menu .menu-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        #layout-menu.employee-menu .menu-link .menu-icon {
            flex: 0 0 1.375rem;
        }

        #layout-menu.employee-menu .employee-panel-badge {
            letter-spacing: 0.08em;
            font-size: 0.7rem;
        }

        #layout-menu.employee-menu .employee-mini-stat {
            border-radius: 0.75rem;
            background: rgba(115, 103, 240, 0.08);
        }
    </style>
@endonce

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme employee-menu">
    <div class="app-brand demo">
        <a href="{{ route('employee.dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                @include('layouts.partials.brand-logo')
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-3">{{ config('app.name', 'Oil Change POS') }}</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
            <i class="icon-base ti tabler-x d-block d-xl-none"></i>
        </a>
    </div>

    <div class="px-4 pb-2">
        <div class="card border-0 bg-label-primary shadow-none mb-0">
            <div class="card-body py-3">
                <span class="badge bg-primary employee-panel-badge">EMPLOYEE PANEL</span>
                <div class="fw-semibold mt-2">{{ $user?->tenant?->display_name ?? 'Workspace' }}</div>
                <small class="text-muted">Daily operations and catalog access</small>
                <div class="employee-mini-stat d-flex justify-content-between align-items-center px-3 py-2 mt-3">
                    <small class="text-muted">Signed in as</small>
                    <small class="fw-semibold text-body">{{ str($user?->primaryRoleName() ?? 'employee')->replace('_', ' ')->title() }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @foreach($menuGroups as $group)
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">{{ $group['label'] }}</span>
            </li>

            @foreach($group['items'] as $item)
                <li class="menu-item {{ $isActive($item['pattern']) ? 'active' : '' }}">
                    <a href="{{ route($item['route'], $item['routeParams'] ?? []) }}" class="menu-link">
                        <i class="menu-icon icon-base ti {{ $item['icon'] }}"></i>
                        <div>{{ $item['label'] }}</div>
                    </a>
                </li>
            @endforeach
        @endforeach

        @if(session()->has('impersonator_id'))
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Session</span>
            </li>
            <li class="menu-item">
                <a href="{{ route('admin.impersonate.stop') }}" class="menu-link text-warning">
                    <i class="menu-icon icon-base ti tabler-user-x"></i>
                    <div>Stop Impersonation</div>
                </a>
            </li>
        @endif
    </ul>
</aside>
<div class="menu-mobile-toggler d-xl-none rounded-1">
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large text-bg-secondary p-2 rounded-1">
        <i class="ti tabler-menu icon-base"></i>
        <i class="ti tabler-chevron-right icon-base"></i>
    </a>
</div>
