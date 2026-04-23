@php
    $user = auth()->user();
    $isSuperAdmin = $user?->isSuperAdmin();
    $homeRoute = $isSuperAdmin ? 'admin.dashboard' : 'tenant.dashboard';
    $adminMenuItems = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'icon' => 'tabler-smart-home'],
        ['label' => 'Shops', 'route' => 'admin.shops.index', 'pattern' => 'admin.shops.*', 'icon' => 'tabler-building-store'],
    ];
    $tenantSections = [
        [
            'label' => 'Shop Management',
            'items' => collect([
                $user?->can('settings.manage') ? ['label' => 'Shop Profile', 'route' => 'tenant.settings.shop-profile.edit', 'pattern' => 'tenant.settings.shop-profile.*', 'icon' => 'tabler-building-store'] : null,
            ])->filter()->values()->all(),
        ],
        [
            'label' => 'Catalog & Services',
            'items' => collect([
                $user?->can('category.view') ? ['label' => 'Categories', 'route' => 'tenant.ecommerce.categories.index', 'pattern' => 'tenant.ecommerce.categories.*', 'icon' => 'tabler-category'] : null,
                $user?->can('subcategory.view') ? ['label' => 'Sub Categories', 'route' => 'tenant.ecommerce.subcategories.index', 'pattern' => 'tenant.ecommerce.subcategories.*', 'icon' => 'tabler-category-plus'] : null,
                ($user?->can('product.view') || $user?->can('products.view') || $user?->can('products.manage')) ? ['label' => 'Products', 'route' => 'tenant.ecommerce.products.index', 'pattern' => 'tenant.ecommerce.products.*', 'icon' => 'tabler-package'] : null,
                $user?->can('service.view') ? ['label' => 'Services', 'route' => 'tenant.ecommerce.services.index', 'pattern' => 'tenant.ecommerce.services.*', 'icon' => 'tabler-tool'] : null,
                $user?->can('discount.manage') ? ['label' => 'Discounts', 'route' => 'tenant.ecommerce.discounts.index', 'pattern' => 'tenant.ecommerce.discounts.*', 'icon' => 'tabler-ticket'] : null,
            ])->filter()->values()->all(),
        ],
        [
            'label' => 'Customers',
            'items' => collect([
                ($user?->can('customer.view') || $user?->can('customers.view')) ? ['label' => 'Customers', 'route' => 'tenant.ecommerce.customers.index', 'pattern' => 'tenant.ecommerce.customers.*', 'icon' => 'tabler-users'] : null,
                ($user?->can('vehicle.view') || $user?->can('vehicles.view')) ? ['label' => 'Vehicles', 'route' => 'tenant.ecommerce.vehicles.index', 'pattern' => 'tenant.ecommerce.vehicles.*', 'icon' => 'tabler-car'] : null,
            ])->filter()->values()->all(),
        ],
    ];
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

        #layout-menu .menu-sub > .menu-item > .menu-link::before {
            display: none;
        }

        #layout-menu .menu-sub .menu-link {
            padding-inline-start: 1rem;
        }
    </style>
@endonce

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route($homeRoute) }}" class="app-brand-link">
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

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @if($isSuperAdmin)
            @foreach($adminMenuItems as $item)
                <li class="menu-item {{ request()->routeIs($item['pattern']) ? 'active' : '' }}">
                    <a href="{{ route($item['route']) }}" class="menu-link">
                        <i class="menu-icon icon-base ti {{ $item['icon'] }}"></i>
                        <div>{{ $item['label'] }}</div>
                    </a>
                </li>
            @endforeach
        @else
            <li class="menu-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
                <a href="{{ route('tenant.dashboard') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-layout-dashboard"></i>
                    <div>Dashboard</div>
                </a>
            </li>

            @foreach($tenantSections as $section)
                @continue(empty($section['items']))

                <li class="menu-header small">
                    <span class="menu-header-text">{{ $section['label'] }}</span>
                </li>

                @foreach($section['items'] as $item)
                    <li class="menu-item {{ request()->routeIs($item['pattern']) ? 'active' : '' }}">
                        <a href="{{ route($item['route']) }}" class="menu-link">
                            <i class="menu-icon icon-base ti {{ $item['icon'] }}"></i>
                            <div>{{ $item['label'] }}</div>
                        </a>
                    </li>
                @endforeach
            @endforeach
        @endif

        @if(session()->has('impersonator_id'))
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
