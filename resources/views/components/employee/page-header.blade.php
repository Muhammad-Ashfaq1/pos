@props([
    'title',
    'backUrl',
    'backTitle' => 'Back',
])

@php
    $showWorkspaceFullscreen = \App\Support\OrderSurface::isAdminWorkspace();
    $hasHeadingActions = isset($actions) || $showWorkspaceFullscreen;
@endphp

<div {{ $attributes->class([
    'employee-orders-heading',
    'employee-orders-heading--with-actions' => $hasHeadingActions,
    'employee-orders-heading--workspace-fs' => $showWorkspaceFullscreen,
]) }}>
    <div class="employee-orders-heading-main">
        <a href="{{ $backUrl }}" class="employee-orders-back-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $backTitle }}">
            <i class="ti tabler-arrow-left"></i>
        </a>
        <h4 class="employee-orders-title">{{ $title }}</h4>
    </div>

    @if ($hasHeadingActions)
        <div class="employee-orders-heading-actions">
            @isset($actions)
                {{ $actions }}
            @endisset
            @if ($showWorkspaceFullscreen)
                @include('partials.workspace-fullscreen-button')
            @endif
        </div>
    @endif
</div>
