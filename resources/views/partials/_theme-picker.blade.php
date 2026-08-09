{{-- Compact personal theme picker for account settings. --}}
@php
    $current = $current ?? \App\Support\AppTheme::resolve(auth()->user());
    $selectedVariant = $current['variant'];
    $selectedMode = $current['mode'];
    $lightThemes = [
        ['id' => 'sky', 'label' => 'Sky', 'accent' => '#25b9d6'],
        ['id' => 'lake', 'label' => 'Lake', 'accent' => '#696cff'],
        ['id' => 'eggplant', 'label' => 'Eggplant', 'accent' => '#8b5cf6'],
    ];
    $darkThemes = [
        ['id' => 'dark', 'label' => 'Dark', 'accent' => '#60a5fa'],
        ['id' => 'high-contrast', 'label' => 'High contrast', 'accent' => '#3b82f6'],
    ];
@endphp

<div class="card mt-4" data-pos-theme-picker>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
            <div>
                <h5 class="mb-1">Appearance</h5>
                <p class="text-muted mb-0 small">Choose a colour theme and light / dark mode.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted" data-pos-theme-status role="status" aria-live="polite"></span>
                <select class="form-select form-select-sm w-auto" data-pos-theme-mode-select aria-label="Theme mode">
                    <option value="light" @selected($selectedMode === 'light')>Light</option>
                    <option value="dark" @selected($selectedMode === 'dark')>Dark</option>
                    <option value="system" @selected($selectedMode === 'system')>System</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <div class="small fw-semibold text-uppercase text-muted mb-2">Light</div>
            <div class="d-flex flex-wrap gap-2">
                @foreach ($lightThemes as $t)
                    <label class="pos-theme-swatch {{ $selectedVariant === $t['id'] ? 'is-selected' : '' }}"
                           data-pos-theme-card="{{ $t['id'] }}"
                           style="--swatch: {{ $t['accent'] }}">
                        <input type="radio" class="btn-check" name="pos_theme_variant"
                               value="{{ $t['id'] }}" data-pos-theme-variant
                               @checked($selectedVariant === $t['id'])>
                        <span class="pos-theme-swatch-dot" aria-hidden="true"></span>
                        <span>{{ $t['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <div class="small fw-semibold text-uppercase text-muted mb-2">Dark</div>
            <div class="d-flex flex-wrap gap-2">
                @foreach ($darkThemes as $t)
                    <label class="pos-theme-swatch {{ $selectedVariant === $t['id'] ? 'is-selected' : '' }}"
                           data-pos-theme-card="{{ $t['id'] }}"
                           style="--swatch: {{ $t['accent'] }}">
                        <input type="radio" class="btn-check" name="pos_theme_variant"
                               value="{{ $t['id'] }}" data-pos-theme-variant
                               @checked($selectedVariant === $t['id'])>
                        <span class="pos-theme-swatch-dot" aria-hidden="true"></span>
                        <span>{{ $t['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>
</div>

@once
<style>
.pos-theme-swatch {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.4rem 0.75rem;
    border: 1px solid rgba(var(--bs-primary-rgb), 0.18);
    border-radius: 999px;
    cursor: pointer;
    background: rgba(var(--bs-primary-rgb), 0.04);
    font-size: 0.875rem;
    user-select: none;
}
.pos-theme-swatch.is-selected {
    border-color: rgb(var(--bs-primary-rgb));
    box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb), 0.16);
    background: rgba(var(--bs-primary-rgb), 0.1);
}
.pos-theme-swatch-dot {
    inline-size: 0.75rem;
    block-size: 0.75rem;
    border-radius: 999px;
    background: var(--swatch);
}
</style>
@endonce
