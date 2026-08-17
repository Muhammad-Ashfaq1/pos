@props([
    'card',
])

@php
    $card = is_array($card) ? $card : [];
    $key = (string) ($card['key'] ?? '');
    $tone = (string) ($card['tone'] ?? 'primary');
    $icon = (string) ($card['icon'] ?? 'tabler-chart-bar');
    $label = (string) ($card['label'] ?? '');
    $value = (string) ($card['value'] ?? '0');
    $meta = (string) ($card['meta'] ?? '');
    $format = (string) ($card['format'] ?? 'number');
@endphp

<div {{ $attributes->class(['pos-glass-card', 'pos-tone-'.$tone, 'h-100']) }} data-product-mix-card="{{ $key }}">
    <div class="pos-stat-body">
        <div class="pos-stat-head">
            <span class="pos-stat-icon"><i class="icon-base ti {{ $icon }}" aria-hidden="true"></i></span>
            <h6 class="pos-stat-label">{{ $label }}</h6>
        </div>
        <p class="pos-stat-value" data-product-mix-value="{{ $key }}" data-product-mix-format="{{ $format }}">{{ $value }}</p>
        <p class="pos-stat-desc mb-0" data-product-mix-meta="{{ $key }}">{{ $meta }}</p>
    </div>
</div>
