@php
    $user = auth()->user();
    $isSuperAdmin = $user?->isSuperAdmin();
    $contextLabel = $isSuperAdmin ? 'Central Admin' : 'Tenant Workspace';
    $contextName = $isSuperAdmin
        ? config('app.name', 'Oil Change POS')
        : $user?->tenant?->display_name ?? 'Shop Workspace';
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
            <h6 class="mb-0">{{ $contextLabel }}</h6>
            {{-- <h6 class="mb-0">{{ $contextName }}</h6> --}}
        </div>

        <ul class="navbar-nav flex-row align-items-center gap-3 ms-auto">
            @if (session()->has('impersonator_id'))
                <li class="nav-item me-3">
                    <a href="{{ route('admin.impersonate.stop') }}" class="btn btn-warning btn-sm">
                        Stop Impersonation
                    </a>
                </li>
            @endif
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);"
                    data-bs-toggle="dropdown" aria-label="Theme: light" aria-expanded="false">
                    <i class="tabler-sun icon-base ti icon-md theme-icon-active"></i>
                </a>
                <span class="d-none" id="nav-theme-text">Theme</span>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
                    <li>
                        <button type="button" class="dropdown-item align-items-center waves-effect active"
                            data-bs-theme-value="light" aria-pressed="true">
                            <span><i class="icon-base ti tabler-sun icon-22px me-3" data-icon="sun"></i>Light</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item align-items-center waves-effect"
                            data-bs-theme-value="dark" aria-pressed="false">
                            <span><i class="icon-base ti tabler-moon-stars icon-22px me-3"
                                    data-icon="moon-stars"></i>Dark</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item align-items-center waves-effect"
                            data-bs-theme-value="system" aria-pressed="false">
                            <span><i class="icon-base ti tabler-device-desktop-analytics icon-22px me-3"
                                    data-icon="device-desktop-analytics"></i>System</span>
                        </button>
                    </li>
                </ul>
            </li>
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
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <div class="dropdown-item-text">
                            <small
                                class="text-muted text-uppercase">{{ str_replace('_', ' ', $user?->primaryRoleName() ?? 'user') }}</small>
                        </div>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
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
