@props([
    'id',
    'label' => null,
])

<section
    {{ $attributes->merge(['class' => 'pos-settings-tab', 'id' => 'sst-' . $id, 'role' => 'tabpanel']) }}
    data-settings-tab="{{ $id }}"
    @if($label) aria-label="{{ $label }}" @endif>
    {{ $slot }}
</section>
