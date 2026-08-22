@php
    $currentRouteName = request()->route()?->getName();
@endphp

<div class="col-md-4 col-lg-3 settings-tab-sidebar card settings-sidebar pos-glass-card pos-tone-secondary">
    <div class="settings-sidebar-label">Settings</div>
    <ul class="nav flex-column settings-sidebar-nav">
        @foreach($settingsSections as $item)
            <li class="nav-item">
                <a href="{{ route($item['route']) }}"
                   class="nav-link settings-sidebar-link {{ $currentRouteName === $item['pattern'] ? 'is-active' : '' }}">
                    <span class="settings-sidebar-icon" aria-hidden="true">
                        <i class="icon-base ti {{ $item['icon'] }}"></i>
                    </span>
                    <span class="settings-text-responsive">{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</div>
