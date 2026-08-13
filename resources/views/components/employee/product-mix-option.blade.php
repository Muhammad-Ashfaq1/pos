@props([
    'card',
    'checked' => false,
])

@php
    $card = is_array($card) ? $card : [];
    $key = (string) ($card['key'] ?? '');
    $label = (string) ($card['label'] ?? '');
    $description = (string) ($card['description'] ?? '');
    $icon = (string) ($card['icon'] ?? 'tabler-chart-bar');
    $tone = (string) ($card['tone'] ?? 'primary');
    $group = (string) ($card['group_label'] ?? '');
    $meta = (string) ($card['preview_meta'] ?? '');
@endphp

<label class="employee-pm-option pos-glass-card pos-tone-{{ $tone }} {{ $checked ? 'is-selected' : '' }}"
       data-pm-option
       data-pm-group="{{ $group }}"
       data-pm-key="{{ $key }}"
       data-pm-label="{{ $label }}"
       data-pm-icon="{{ $icon }}"
       data-pm-tone="{{ $tone }}"
       data-pm-meta="{{ $meta }}">
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

    <span class="employee-pm-option-preview pos-stat-head">
        <span class="pos-stat-icon"><i class="icon-base ti {{ $icon }}" aria-hidden="true"></i></span>
        <span class="pos-stat-label mb-0">{{ $meta !== '' ? $meta : $label }}</span>
    </span>
</label>
