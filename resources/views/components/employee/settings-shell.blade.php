@props([
    'activeSettingsTab' => 'product-mix',
])

<div {{ $attributes->class(['employee-orders-page', 'employee-settings-page']) }}>
    <x-employee.page-header
        title="Settings"
        :back-url="route('employee.dashboard')"
        back-title="Back to dashboard"
    />

    <div class="employee-settings-layout">
        <aside class="employee-settings-sidebar pos-glass-card pos-tone-primary" aria-label="Settings">
            <nav class="employee-settings-nav">
                <div class="employee-settings-nav-group">
                    <div class="employee-settings-nav-label">Dashboard</div>
                    <a href="{{ route('employee.settings.product-mix') }}"
                       class="employee-settings-nav-link {{ $activeSettingsTab === 'product-mix' ? 'is-active' : '' }}">
                        <span class="employee-settings-nav-icon" aria-hidden="true">
                            <i class="icon-base ti tabler-chart-pie-2"></i>
                        </span>
                        <span class="employee-settings-nav-title">Product Mix</span>
                    </a>
                </div>
            </nav>
        </aside>

        <section class="employee-settings-main">
            {{ $slot }}
        </section>
    </div>
</div>
