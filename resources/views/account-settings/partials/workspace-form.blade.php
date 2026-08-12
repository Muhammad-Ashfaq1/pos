@php
    use App\Support\EmployeeNavigation;

    $currentNavMode = EmployeeNavigation::navMode(auth()->user());
    $navOptions = [
        [
            'value' => EmployeeNavigation::MODE_BOTTOM,
            'label' => 'Bottom bar',
            'icon' => 'tabler-layout-navbar',
        ],
        [
            'value' => EmployeeNavigation::MODE_SIDEBAR,
            'label' => 'Sidebar',
            'icon' => 'tabler-layout-sidebar',
        ],
    ];
@endphp

<form method="POST" action="{{ route('employee.preferences.navigation') }}" class="card mt-4" id="workspace" data-nav-mode-picker>
    @csrf
    @method('PUT')

    <div class="card-body">
        <div class="mb-3">
            <h5 class="mb-1">Navigation</h5>
            <p class="text-muted mb-0 small">Choose bottom bar or sidebar.</p>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach ($navOptions as $option)
                <label class="pos-theme-swatch pos-nav-mode-chip {{ $currentNavMode === $option['value'] ? 'is-selected' : '' }}"
                       data-nav-mode-card="{{ $option['value'] }}">
                    <input type="radio"
                           class="btn-check"
                           name="employee_nav_mode"
                           value="{{ $option['value'] }}"
                           @checked($currentNavMode === $option['value'])>
                    <i class="ti {{ $option['icon'] }}" aria-hidden="true"></i>
                    <span>{{ $option['label'] }}</span>
                </label>
            @endforeach
        </div>

        <div class="account-settings-actions">
            <button type="submit" class="btn btn-primary account-settings-save-btn">Save Changes</button>
        </div>
    </div>
</form>

@once
<style>
.pos-nav-mode-chip {
    gap: 0.5rem;
}

.pos-nav-mode-chip i {
    font-size: 1rem;
    color: rgb(var(--bs-primary-rgb));
}

.pos-nav-mode-chip.is-selected,
.pos-nav-mode-chip:has(input:checked) {
    border-color: rgb(var(--bs-primary-rgb));
    box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb), 0.16);
    background: rgba(var(--bs-primary-rgb), 0.1);
}
</style>
<script>
(function () {
    const root = document.querySelector('[data-nav-mode-picker]');
    if (!root) return;

    root.querySelectorAll('[data-nav-mode-card]').forEach(function (card) {
        card.addEventListener('change', function () {
            root.querySelectorAll('[data-nav-mode-card]').forEach(function (el) {
                el.classList.toggle('is-selected', el.querySelector('input')?.checked === true);
            });
        });
    });
})();
</script>
@endonce
