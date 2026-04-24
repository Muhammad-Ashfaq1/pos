@php
    $currentRouteName = request()->route()?->getName();
@endphp

<div class="col-md-4 col-lg-2 settings-tab-sidebar settings-sidebar">
    <ul class="nav flex-column text-start">
        @foreach($settingsSections as $item)
            <li class="nav-item mb-2">
                <a href="{{ route($item['route']) }}"
                   class="nav-link d-flex align-items-center justify-content-start @if($currentRouteName === $item['pattern']) active @endif">
                    <i class="icon-base ti {{ $item['icon'] }} me-2 settings-icon-responsive flex-shrink-0"></i>
                    <span class="settings-text-responsive">{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</div>
