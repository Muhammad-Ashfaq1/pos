@php
    use App\Support\EmployeeNavigation;

    $user = auth()->user();
    $currentRouteName = request()->route()?->getName() ?? '';
    $menuGroups = collect(EmployeeNavigation::sidebarGroups($user));

    $isActive = function (string $pattern) use ($currentRouteName): bool {
        return collect(explode('|', $pattern))->contains(
            fn (string $segment): bool => str($currentRouteName)->is($segment)
        );
    };
@endphp

<aside class="employee-preview-sidebar" aria-label="Employee navigation">
    <div class="employee-preview-sidebar-brand">
        @include('layouts.partials.shop-brand', [
            'shopTenant' => $user?->tenant,
            'size' => 36,
            'textClass' => 'employee-preview-sidebar-brand-text',
        ])
    </div>

    <nav class="employee-preview-sidebar-nav">
        @foreach($menuGroups as $group)
            <div class="employee-preview-sidebar-group">
                <div class="employee-preview-sidebar-group-label">{{ $group['label'] }}</div>
                @foreach($group['items'] as $item)
                    <a href="{{ route($item['route'], $item['routeParams'] ?? []) }}"
                       class="employee-preview-sidebar-link {{ $isActive($item['pattern']) ? 'is-active' : '' }}">
                        <i class="ti {{ $item['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>
</aside>
