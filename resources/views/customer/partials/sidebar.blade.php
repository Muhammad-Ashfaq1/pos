{{-- Customer portal authenticated chrome — same Vuexy layout-menu collapse as admin --}}
@php
    $portalCustomer = auth('customer')->user();
    $portalNav = [
        ['label' => 'Overview', 'route' => 'customer.dashboard', 'pattern' => 'customer.dashboard', 'icon' => 'tabler-layout-dashboard'],
        ['label' => 'Service History', 'route' => 'customer.orders', 'pattern' => 'customer.orders*', 'icon' => 'tabler-history'],
        ['label' => 'Store Credit', 'route' => 'customer.credits', 'pattern' => 'customer.credits', 'icon' => 'tabler-wallet'],
        ['label' => 'Vehicles', 'route' => 'customer.vehicles', 'pattern' => 'customer.vehicles', 'icon' => 'tabler-car'],
    ];
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('customer.dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                @include('layouts.partials.brand-logo')
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-3">Auto<span class="text-warning">Serve</span></span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
            <i class="icon-base ti tabler-x d-block d-xl-none"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <li class="menu-header small">
            <span class="menu-header-text">My account</span>
        </li>
        @foreach ($portalNav as $item)
            <li class="menu-item {{ request()->routeIs($item['pattern']) ? 'active' : '' }}">
                <a href="{{ route($item['route']) }}" class="menu-link">
                    <i class="menu-icon icon-base ti {{ $item['icon'] }}"></i>
                    <div>{{ $item['label'] }}</div>
                </a>
            </li>
        @endforeach
    </ul>
</aside>

<div class="menu-mobile-toggler d-xl-none rounded-1">
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large text-bg-secondary p-2 rounded-1">
        <i class="ti tabler-menu icon-base"></i>
        <i class="ti tabler-chevron-right icon-base"></i>
    </a>
</div>
