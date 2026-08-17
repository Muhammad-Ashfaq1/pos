@props([
    'card',
    'checked' => false,
    'preview' => null,
])

@php
    $card = is_array($card) ? $card : [];
    $preview = is_array($preview) ? $preview : [];
    $key = (string) ($card['key'] ?? '');
    $label = (string) ($card['label'] ?? '');
    $description = (string) ($card['description'] ?? '');
    $icon = (string) ($card['icon'] ?? 'tabler-chart-bar');
    $tone = (string) ($card['tone'] ?? 'primary');
    $group = (string) ($card['group_label'] ?? '');
    $gradient = (string) ($card['gradient'] ?? 'purple');
    $fallbackMeta = (string) ($card['preview_meta'] ?? '');
    $meta = (string) (($preview['meta'] ?? '') !== '' ? $preview['meta'] : $fallbackMeta);
    $value = (string) ($preview['value'] ?? '0');
    $useDollar = str_contains($icon, 'currency-dollar');
@endphp

<label class="employee-pm-option employee-pm-swatch-{{ $gradient }} {{ $checked ? 'is-selected' : '' }}"
       data-pm-option
       data-pm-group="{{ $group }}"
       data-pm-key="{{ $key }}"
       data-pm-label="{{ $label }}"
       data-pm-icon="{{ $icon }}"
       data-pm-tone="{{ $tone }}"
       data-pm-meta="{{ $meta }}"
       data-pm-value="{{ $value }}">
    <span class="employee-pm-option-head">
        <input type="checkbox"
               name="cards[]"
               value="{{ $key }}"
               class="employee-pm-option-check form-check-input"
               @checked($checked)>
        <span class="employee-pm-option-copy">
            <span class="employee-pm-option-title">{{ $label }}</span>
            <span class="employee-pm-option-desc">{{ $description }}</span>
        </span>
    </span>

    <span class="employee-pm-option-swatch" aria-hidden="true">
        <span class="employee-pm-option-swatch-label">{{ $label }}</span>
        <span class="employee-pm-option-swatch-icon">
            @if ($useDollar)
                <span class="employee-pm-option-swatch-symbol">$</span>
            @else
                <i class="icon-base ti {{ $icon }}"></i>
            @endif
        </span>
    </span>
</label>
