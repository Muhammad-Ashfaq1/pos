@php
    use App\Support\EmployeeNavigation;

    $currentNavMode = EmployeeNavigation::navMode(auth()->user());
    $navOptions = [
        [
            'value' => EmployeeNavigation::MODE_BOTTOM,
            'label' => 'Bottom shortcuts',
            'hint' => 'Floating bar — POS, Customers, Inventory, Settings',
            'icon' => 'tabler-layout-navbar',
        ],
        [
            'value' => EmployeeNavigation::MODE_SIDEBAR,
            'label' => 'Sidebar menu',
            'hint' => 'Left menu with only allowed tasks',
            'icon' => 'tabler-layout-sidebar',
        ],
    ];
@endphp

<form method="POST" action="{{ route('employee.preferences.navigation') }}" class="card mt-4" id="workspace">
    @csrf
    @method('PUT')

    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
            <div>
                <h5 class="mb-1">Workspace navigation</h5>
                <p class="text-muted mb-0 small">Choose how shortcuts appear across the employee portal. Dashboard tiles stay on home.</p>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach ($navOptions as $option)
                <label class="pos-nav-mode-swatch {{ $currentNavMode === $option['value'] ? 'is-selected' : '' }}">
                    <input type="radio"
                           class="btn-check"
                           name="employee_nav_mode"
                           value="{{ $option['value'] }}"
                           @checked($currentNavMode === $option['value'])>
                    <span class="pos-nav-mode-swatch-icon" aria-hidden="true">
                        <i class="ti {{ $option['icon'] }}"></i>
                    </span>
                    <span class="pos-nav-mode-swatch-copy">
                        <span class="pos-nav-mode-swatch-label">{{ $option['label'] }}</span>
                        <span class="pos-nav-mode-swatch-hint">{{ $option['hint'] }}</span>
                    </span>
                </label>
            @endforeach
        </div>

        <div class="account-settings-actions pt-1">
            <button type="submit" class="btn btn-primary account-settings-save-btn">Save Changes</button>
        </div>
    </div>
</form>

@once
<style>
.pos-nav-mode-swatch {
    display: inline-flex;
    align-items: flex-start;
    gap: 0.65rem;
    min-width: min(100%, 16.5rem);
    padding: 0.7rem 0.9rem;
    border: 1px solid rgba(var(--bs-primary-rgb), 0.18);
    border-radius: 0.85rem;
    cursor: pointer;
    background: rgba(var(--bs-primary-rgb), 0.04);
    user-select: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
}

.pos-nav-mode-swatch.is-selected,
.pos-nav-mode-swatch:has(input:checked) {
    border-color: rgb(var(--bs-primary-rgb));
    box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb), 0.16);
    background: rgba(var(--bs-primary-rgb), 0.1);
}

.pos-nav-mode-swatch-icon {
    width: 2rem;
    height: 2rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.55rem;
    background: rgba(var(--bs-primary-rgb), 0.12);
    color: rgb(var(--bs-primary-rgb));
    font-size: 1.05rem;
    flex-shrink: 0;
}

.pos-nav-mode-swatch-copy {
    display: grid;
    gap: 0.15rem;
}

.pos-nav-mode-swatch-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--bs-heading-color, #444050);
    line-height: 1.3;
}

.pos-nav-mode-swatch-hint {
    font-size: 0.75rem;
    color: var(--bs-secondary-color, #6f6b7d);
    line-height: 1.4;
}
</style>
@endonce
