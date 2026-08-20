@php
    $user = auth()->user();
    $isSuperAdmin = $user?->isSuperAdmin();
    $contextLabel = $isSuperAdmin
        ? 'Central Admin'
        : ($user?->tenant?->display_name ?? config('app.name', 'Oil Change POS'));
    $contextName = $isSuperAdmin ? config('app.name', 'Oil Change POS') : null;
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
            @if ($contextName)
                <small class="text-muted">{{ $contextName }}</small>
            @endif
        </div>

        <ul class="navbar-nav flex-row align-items-center gap-3 ms-auto">
            @if (session()->has('impersonator_id'))
                <li class="nav-item me-2">
                    <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-pill border border-warning bg-label-warning">
                        <i class="icon-base ti tabler-user-exclamation icon-sm text-warning"></i>
                        <span class="small fw-medium text-warning d-none d-sm-inline">
                            Impersonating as <strong>{{ $user?->name }}</strong>
                        </span>
                        <a href="{{ route('admin.impersonate.stop') }}" class="btn btn-warning btn-sm py-0 px-2">
                            <i class="icon-base ti tabler-x icon-xs me-1"></i>Stop
                        </a>
                    </div>
                </li>
            @endif
            @include('layouts.partials.theme-switcher')
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        @php $navAvatarUrl = \App\Support\AccountSettings::avatarUrl($user); @endphp
                        @if ($navAvatarUrl)
                            <img src="{{ $navAvatarUrl }}" alt="{{ $user?->name }}" class="rounded-circle" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                {{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}
                            </span>
                        @endif
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
                    @include('layouts.partials.account-menu-items', [
                        'logoutLabel' => 'Sign out',
                        'iconClass' => 'icon-base ti',
                    ])
                </ul>
            </li>
        </ul>
    </div>
</nav>
