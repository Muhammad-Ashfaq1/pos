@php
    $user = auth()->user();
    $isSuperAdmin = $user?->isSuperAdmin();
    $isEmployee = $user?->isEmployee();
    $vehicleFeatureEnabled = app(\App\Support\Tenancy\TenantContext::class)->current()?->isVehicleRequired() ?? true;
    $homeRoute = $isSuperAdmin ? 'admin.dashboard' : ($isEmployee ? 'employee.dashboard' : 'tenant.dashboard');
    $currentRouteName = request()->route()?->getName();
    $settingsMenuItem = $user?->can('settings.manage')
        ? [
            'label' => 'Settings',
            'route' => 'tenant.settings.shop-profile.general',
            'pattern' => 'tenant.settings.shop-profile.*',
            'icon' => 'tabler-settings-cog',
        ]
        : null;

    $adminMenuItems = [
        [
            'label' => 'Dashboard',
            'route' => 'admin.dashboard',
            'pattern' => 'admin.dashboard',
            'icon' => 'tabler-smart-home',
        ],
        [
            'label' => 'Shops',
            'route' => 'admin.shops.index',
            'pattern' => 'admin.shops.*',
            'icon' => 'tabler-building-store',
        ],
        [
            'label' => 'Demo Requests',
            'route' => 'admin.demo-requests.index',
            'pattern' => 'admin.demo-requests.*',
            'icon' => 'tabler-calendar-event',
            'badge' => \App\Models\DemoRequest::query()
                ->where('status', \App\Enums\DemoRequestStatus::New->value)
                ->count(),
        ],
    ];

    $tenantMenuGroups = collect([
        [
            'label' => 'Catalog',
            'icon' => 'tabler-box-seam',
            'items' => collect([
                $user?->can('category.view')
                    ? [
                        'label' => 'Categories',
                        'route' => 'tenant.ecommerce.categories.index',
                        'pattern' => 'tenant.ecommerce.categories.*',
                        'icon' => 'tabler-category',
                    ]
                    : null,
                $user?->can('subcategory.view')
                    ? [
                        'label' => 'Sub Categories',
                        'route' => 'tenant.ecommerce.subcategories.index',
                        'pattern' => 'tenant.ecommerce.subcategories.*',
                        'icon' => 'tabler-category-plus',
                    ]
                    : null,
                $user?->can('product-type.view')
                    ? [
                        'label' => 'Product Types',
                        'route' => 'tenant.ecommerce.product-types.index',
                        'pattern' => 'tenant.ecommerce.product-types.*',
                        'icon' => 'tabler-tags',
                    ]
                    : null,
                $user?->can('product.view') || $user?->can('products.view') || $user?->can('products.manage')
                    ? [
                        'label' => 'Products',
                        'route' => 'tenant.ecommerce.products.index',
                        'pattern' => 'tenant.ecommerce.products.*',
                        'icon' => 'tabler-package',
                    ]
                    : null,
            ])
                ->filter()
                ->values()
                ->all(),
        ],
        [
            'label' => 'Services',
            'icon' => 'tabler-tool',
            'items' => collect([
                $user?->can('service.view')
                    ? [
                        'label' => 'Services',
                        'route' => 'tenant.ecommerce.services.index',
                        'pattern' => 'tenant.ecommerce.services.*',
                        'icon' => 'tabler-tool',
                    ]
                    : null,
            ])
                ->filter()
                ->values()
                ->all(),
        ],
        [
            'label' => 'Sales & Promotions',
            'icon' => 'tabler-ticket',
            'items' => collect([
                $user?->can('discount.manage')
                    ? [
                        'label' => 'Discounts',
                        'route' => 'tenant.ecommerce.discounts.index',
                        'pattern' => 'tenant.ecommerce.discounts.*',
                        'icon' => 'tabler-ticket',
                    ]
                    : null,
            ])
                ->filter()
                ->values()
                ->all(),
        ],
        [
            'label' => 'Customers & Vehicles',
            'icon' => 'tabler-users',
            'items' => collect([
                $user?->can('customer.view') || $user?->can('customers.view')
                    ? [
                        'label' => 'Customers',
                        'route' => 'tenant.ecommerce.customers.index',
                        'pattern' => 'tenant.ecommerce.customers.*',
                        'icon' => 'tabler-users',
                    ]
                    : null,
                $vehicleFeatureEnabled && ($user?->can('vehicle.view') || $user?->can('vehicles.view'))
                    ? [
                        'label' => 'Vehicles',
                        'route' => 'tenant.ecommerce.vehicles.index',
                        'pattern' => 'tenant.ecommerce.vehicles.*',
                        'icon' => 'tabler-car',
                    ]
                    : null,
            ])
                ->filter()
                ->values()
                ->all(),
        ],
        [
            'label' => 'Reports',
            'icon' => 'tabler-chart-bar',
            'items' => collect([
                $user?->can('reports.view')
                    ? [
                        'label' => 'Reports',
                        'route' => 'tenant.reports.index',
                        'pattern' => 'tenant.reports.*',
                        'icon' => 'tabler-chart-histogram',
                        'routeParams' => ['report' => 'sales'],
                    ]
                    : null,
            ])
                ->filter()
                ->values()
                ->all(),
        ],
        [
            'label' => 'Discounts',
            'icon' => 'tabler-ticket',
            'items' => collect([
                $user?->can('cards.view') || $user?->can('cards.manage')
                    ? [
                        'label' => 'Discount',
                        'route' => 'tenant.ecommerce.cards.type',
                        'routeParams' => ['type' => 'discount'],
                        'pattern' => 'tenant.ecommerce.cards.*',
                        'cardType' => 'discount',
                        'icon' => 'tabler-ticket',
                    ]
                    : null,
                $user?->can('cards.view') || $user?->can('cards.manage')
                    ? [
                        'label' => 'Gift',
                        'route' => 'tenant.ecommerce.cards.type',
                        'routeParams' => ['type' => 'gift'],
                        'pattern' => 'tenant.ecommerce.cards.*',
                        'cardType' => 'gift',
                        'icon' => 'tabler-gift',
                    ]
                    : null,
                $user?->can('cards.view') || $user?->can('cards.manage')
                    ? [
                        'label' => 'Reward',
                        'route' => 'tenant.ecommerce.cards.type',
                        'routeParams' => ['type' => 'reward'],
                        'pattern' => 'tenant.ecommerce.cards.*',
                        'cardType' => 'reward',
                        'icon' => 'tabler-trophy',
                    ]
                    : null,
                $user?->isTenantAdmin() || $user?->can('discount.group.manage')
                    ? [
                        'label' => 'Discount groups',
                        'route' => 'tenant.discounts.group.index',
                        'pattern' => 'tenant.discounts.group.*',
                        'icon' => 'tabler-ticket',
                    ]
                    : null,
            ])
                ->filter()
                ->values()
                ->all(),
        ],
    ])
        ->filter(fn(array $group): bool => !empty($group['items']))
        ->values();

    $isMenuItemActive = function (array $item) use ($currentRouteName): bool {
        if (isset($item['cardType'])) {
            return request()->routeIs('tenant.ecommerce.cards.*')
                && request()->route('type') === $item['cardType'];
        }

        return str($currentRouteName ?? '')->is($item['pattern']);
    };

    $isGroupActive = function (array $group) use ($isMenuItemActive): bool {
        return collect($group['items'])->contains(
            fn (array $item): bool => $isMenuItemActive($item),
        );
    };
@endphp

@once
    <style>
        #layout-menu .menu-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        #layout-menu .menu-link .menu-icon {
            flex: 0 0 1.375rem;
        }

        #layout-menu .menu-sub>.menu-item>.menu-link::before {
            display: none;
        }

        #layout-menu .menu-sub .menu-link {
            padding-inline-start: 1rem;
        }

        #layout-menu .menu-sub .menu-icon {
            opacity: 0.9;
        }

        #layout-menu .menu-inner.menu-layout-column {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        #layout-menu .menu-item-settings-bottom {
            margin-top: auto;
        }
    </style>
@endonce

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route($homeRoute) }}" class="app-brand-link">
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

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1 menu-layout-column">
        @if ($isSuperAdmin)
            @foreach ($adminMenuItems as $item)
                <li class="menu-item {{ request()->routeIs($item['pattern']) ? 'active' : '' }}">
                    <a href="{{ route($item['route']) }}" class="menu-link">
                        <i class="menu-icon icon-base ti {{ $item['icon'] }}"></i>
                        <div>{{ $item['label'] }}</div>
                        @if (! empty($item['badge']))
                            <span class="badge rounded-pill bg-danger ms-auto">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        @else
            @php
                $dashboardRoute = $isEmployee ? 'employee.dashboard' : 'tenant.dashboard';
            @endphp
            <li class="menu-item {{ request()->routeIs($dashboardRoute) ? 'active' : '' }}">
                <a href="{{ route($dashboardRoute) }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-layout-dashboard"></i>
                    <div>Dashboard</div>
                </a>
            </li>

            @foreach ($tenantMenuGroups as $group)
                @php
                    $groupOpen = $isGroupActive($group);
                @endphp
                <li class="menu-item {{ $groupOpen ? 'active open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon icon-base ti {{ $group['icon'] }}"></i>
                        <div>{{ $group['label'] }}</div>
                    </a>

                    <ul class="menu-sub">
                        @foreach ($group['items'] as $item)
                            <li class="menu-item {{ $isMenuItemActive($item) ? 'active' : '' }}">
                                <a href="{{ route($item['route'], $item['routeParams'] ?? []) }}" class="menu-link">
                                    <i class="menu-icon icon-base ti {{ $item['icon'] }}"></i>
                                    <div>{{ $item['label'] }}</div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endforeach

            @if ($settingsMenuItem)
                <li class="menu-item menu-item-settings-bottom {{ request()->routeIs($settingsMenuItem['pattern']) ? 'active' : '' }}">
                    <a href="{{ route($settingsMenuItem['route']) }}" class="menu-link">
                        <i class="menu-icon icon-base ti {{ $settingsMenuItem['icon'] }}"></i>
                        <div>{{ $settingsMenuItem['label'] }}</div>
                    </a>
                </li>
            @endif
        @endif

        @if (session()->has('impersonator_id'))
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
