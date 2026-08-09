@props([
    'section',
    'url',
    // Retained so a tab that still passes them keeps rendering; the page-level
    // Save button they described is gone (settings save themselves).
    'submitLabel' => null,
    'tone' => null,
])

{{--
    Wraps a settings tab in a form whose controls SAVE THEMSELVES via a
    per-section endpoint. Each input's `name` is the schema key for that
    section. Pass `url` explicitly — POS does not hard-code a route name.

    SETTINGS TABS ONLY. A modal whose fields must be reviewed together before
    submission keeps its own Save/Confirm button and does not use this component.

    Usage:
        <x-settings.section-form section="message" :url="route('…')">
            <input type="checkbox" name="bold_unreads" value="1">
        </x-settings.section-form>
--}}
<form
    {{ $attributes->merge(['class' => 'pos-section-form']) }}
    data-section-form="{{ $section }}"
    data-section-url="{{ $url }}"
    method="POST"
    novalidate>
    @csrf

    {{ $slot }}

    <div class="pos-section-form-foot d-flex justify-content-end align-items-center gap-2 mt-3">
        <span class="pos-section-form-status small text-muted"
              data-section-form-status
              data-state="idle"
              role="status"
              aria-live="polite"></span>
        <button type="button"
                class="btn btn-sm btn-outline-secondary d-none"
                data-section-form-retry>
            Retry
        </button>
    </div>
</form>
