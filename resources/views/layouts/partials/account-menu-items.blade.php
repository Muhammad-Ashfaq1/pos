{{-- Shared account menu: Profile first, then Logout --}}
@php
    $logoutLabel = $logoutLabel ?? 'Logout';
    $iconClass = $iconClass ?? 'ti';
@endphp
<li>
    <a href="{{ route('account.profile') }}"
       class="dropdown-item {{ request()->routeIs('account.*') ? 'active' : '' }}">
        <i class="{{ $iconClass }} tabler-user me-2"></i>
        Profile
    </a>
</li>
<li>
    <hr class="dropdown-divider">
</li>
<li>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="dropdown-item">
            <i class="{{ $iconClass }} tabler-logout me-2"></i>
            {{ $logoutLabel }}
        </button>
    </form>
</li>
