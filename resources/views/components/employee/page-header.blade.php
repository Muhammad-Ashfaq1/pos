@props([
    'title',
    'backUrl',
    'backTitle' => 'Back',
])

<div {{ $attributes->class(['employee-orders-heading', 'employee-orders-heading--with-actions' => isset($actions)]) }}>
    <div class="employee-orders-heading-main">
        <a href="{{ $backUrl }}" class="employee-orders-back-btn" data-bs-toggle="tooltip" title="{{ $backTitle }}">
            <i class="ti tabler-arrow-left"></i>
        </a>
        <h4 class="employee-orders-title">{{ $title }}</h4>
    </div>

    @isset($actions)
        <div class="employee-orders-heading-actions">
            {{ $actions }}
        </div>
    @endisset
</div>
