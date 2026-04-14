@php
    $user = auth()->user();
    $isSuperAdmin = $user?->isSuperAdmin();
    $contextLabel = $isSuperAdmin ? 'Central Admin' : 'Tenant Workspace';
    $contextName = $isSuperAdmin
        ? config('app.name', 'Oil Change POS')
        : ($user?->tenant?->shop_name ?? 'Shop Workspace');
@endphp

<nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
            <i class="icon-base ti tabler-menu-2 icon-md"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center justify-content-between w-100" id="navbar-collapse">
        <div>
            <span class="badge bg-label-primary mb-1">{{ $contextLabel }}</span>
            <h6 class="mb-0">{{ $contextName }}</h6>
        </div>

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            @if(session()->has('impersonator_id'))
                <li class="nav-item me-3">
                    <a href="{{ route('admin.impersonate.stop') }}" class="btn btn-warning btn-sm">
                        Stop Impersonation
                    </a>
                </li>
            @endif

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <span class="avatar-initial rounded-circle bg-label-primary">
                            {{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}
                        </span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <div class="dropdown-item-text">
                            <div class="fw-medium">{{ $user?->name }}</div>
                            <small class="text-muted">{{ $user?->email }}</small>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <div class="dropdown-item-text">
                            <small class="text-muted text-uppercase">{{ $user?->role }}</small>
                        </div>
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
