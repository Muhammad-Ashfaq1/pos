@php
    $user = auth()->user();
    $isSuperAdmin = $user?->isSuperAdmin();
    $contextLabel = $isSuperAdmin
        ? 'Central Admin'
        : ($user?->tenant?->display_name ?? config('app.name', 'Oil Change POS'));
    $contextName = $isSuperAdmin ? config('app.name', 'Oil Change POS') : null;
@endphp

<nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme pos-navbar pos-tone-primary"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)" aria-label="Toggle menu">
            <i class="icon-base ti tabler-menu-2 icon-md" aria-hidden="true"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center justify-content-between w-100" id="navbar-collapse">
        <div class="pos-navbar-brand">
            <span class="pos-navbar-org-name" title="{{ $contextLabel }}">{{ $contextLabel }}</span>
            @if ($contextName)
                <small class="pos-navbar-subtitle text-muted">{{ $contextName }}</small>
            @endif
        </div>

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            @if (session()->has('impersonator_id'))
                <li class="nav-item me-2">
                    <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-pill border border-warning bg-label-warning">
                        <i class="icon-base ti tabler-user-exclamation icon-sm text-warning" aria-hidden="true"></i>
                        <span class="small fw-medium text-warning d-none d-sm-inline">
                            Impersonating as <strong>{{ $user?->name }}</strong>
                        </span>
                        <a href="{{ route('admin.impersonate.stop') }}" class="btn btn-warning btn-sm py-0 px-2">
                            <i class="icon-base ti tabler-x icon-xs me-1" aria-hidden="true"></i>Stop
                        </a>
                    </div>
                </li>
            @endif
            @include('layouts.partials.theme-switcher')
            <li class="nav-item dropdown pos-navbar-account">
                <a class="nav-link dropdown-toggle hide-arrow p-0 pos-navbar-avatar-trigger" href="javascript:void(0);" data-bs-toggle="dropdown" aria-label="Account menu" aria-expanded="false">
                    @php $navAvatarUrl = \App\Support\AccountSettings::avatarUrl($user); @endphp
                    <div class="avatar avatar-online pos-navbar-avatar">
                        @if ($navAvatarUrl)
                            <img src="{{ $navAvatarUrl }}" alt="{{ $user?->name }}" class="rounded-circle">
                        @else
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                {{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}
                            </span>
                        @endif
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end pos-navbar-dropdown">
                    <li>
                        <div class="pos-navbar-dropdown-head">
                            <div class="avatar avatar-online pos-navbar-avatar pos-navbar-avatar--lg">
                                @if ($navAvatarUrl)
                                    <img src="{{ $navAvatarUrl }}" alt="{{ $user?->name }}" class="rounded-circle">
                                @else
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        {{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}
                                    </span>
                                @endif
                            </div>
                            <div class="pos-navbar-dropdown-meta min-w-0">
                                <div class="pos-navbar-dropdown-name text-truncate">{{ $user?->name }}</div>
                                <small class="pos-navbar-dropdown-email text-truncate">{{ $user?->email }}</small>
                            </div>
                        </div>
                    </li>
                    @unless ($isSuperAdmin)
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <div class="pos-navbar-dropdown-org px-3 py-2">
                                <span class="pos-navbar-org-name" title="{{ $contextLabel }}">{{ $contextLabel }}</span>
                            </div>
                        </li>
                    @endunless
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
