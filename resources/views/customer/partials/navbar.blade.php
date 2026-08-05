@php
    $portalCustomer = auth('customer')->user();
    $shopName = $portalCustomer->tenant?->shop_name ?? $portalCustomer->tenant?->name ?? 'Customer Portal';
@endphp

<nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
            <i class="icon-base ti tabler-menu-2 icon-md"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center justify-content-between w-100" id="navbar-collapse">
        <div>
            <h6 class="mb-0">Customer Portal</h6>
            <small class="text-muted">{{ $shopName }}</small>
        </div>

        <ul class="navbar-nav flex-row align-items-center gap-3 ms-auto">
            @include('layouts.partials.theme-switcher')
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown"
                   aria-expanded="false" aria-label="Account menu">
                    <div class="avatar avatar-online">
                        @php $navAvatarUrl = \App\Support\AccountSettings::avatarUrl($portalCustomer); @endphp
                        @if ($navAvatarUrl)
                            <img src="{{ $navAvatarUrl }}" alt="{{ $portalCustomer->name }}" class="rounded-circle">
                        @else
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                {{ strtoupper(substr($portalCustomer->name ?? 'C', 0, 1)) }}
                            </span>
                        @endif
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <div class="dropdown-item-text">
                            <div class="fw-medium">{{ $portalCustomer->name }}</div>
                            <small class="text-muted">{{ $portalCustomer->email }}</small>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a href="{{ route('account.profile') }}"
                           class="dropdown-item {{ request()->routeIs('account.*') ? 'active' : '' }}">
                            <i class="icon-base ti tabler-user me-2"></i>
                            Profile
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="icon-base ti tabler-logout me-2"></i>
                                Sign out
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
