@php
    $user = auth()->user();
    $currentRouteName = request()->route()?->getName();
    $menuGroups = collect([
        [
            'label' => 'Workspace',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'employee.dashboard', 'pattern' => 'employee.dashboard', 'icon' => 'tabler-layout-dashboard', 'visible' => true],
                ['label' => 'POS / Workspace', 'route' => 'employee.pos', 'pattern' => 'employee.workspace|employee.pos', 'icon' => 'tabler-cash-register', 'visible' => true],
            ],
        ],
        [
            'label' => 'Catalog',
            'items' => [
                ['label' => 'Products', 'route' => 'tenant.ecommerce.products.index', 'pattern' => 'tenant.ecommerce.products.*', 'icon' => 'tabler-package', 'visible' => $user?->can('product.view') || $user?->can('products.view') || $user?->can('products.manage')],
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
        [
            'label' => 'Account',
            'items' => [
                ['label' => 'Profile', 'route' => 'employee.account', 'pattern' => 'employee.account|employee.profile', 'icon' => 'tabler-user-circle', 'visible' => true],
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
                <span class="text-primary">
                    <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z" fill="currentColor" />
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z" fill="currentColor" />
                    </svg>
                </span>
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
                    <a href="{{ route($item['route']) }}" class="menu-link">
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
