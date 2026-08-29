@props([
    'variant' => 'page-header',
])

@php
    $buttonClass = match ($variant) {
        'toolbar' => 'btn btn-label-secondary btn-icon pos-workspace-fs-btn',
        'navbar' => 'pos-workspace-fs-btn pos-workspace-fs-btn--navbar',
        default => 'employee-orders-icon-btn pos-workspace-fs-btn',
    };
@endphp

<button
    type="button"
    class="{{ $buttonClass }}"
    data-workspace-fullscreen
    aria-pressed="false"
    title="Full screen"
    aria-label="Full screen">
    <i class="icon-base ti tabler-arrows-maximize pos-workspace-fs-enter" aria-hidden="true"></i>
    <i class="icon-base ti tabler-arrows-minimize pos-workspace-fs-exit" aria-hidden="true"></i>
</button>
