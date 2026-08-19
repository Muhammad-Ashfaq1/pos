@php
    $periodOptions = $periodOptions ?? [
        'today' => ['Today (Default)', 'tabler-calendar'],
        'yesterday' => ['Yesterday', 'tabler-calendar-minus'],
        'week' => ['This Week', 'tabler-calendar-week'],
        'last_week' => ['Last Week', 'tabler-calendar-week'],
        'month' => ['This Month', 'tabler-calendar-month'],
        'last_month' => ['Last Month', 'tabler-calendar-month'],
        'year' => ['This Year', 'tabler-calendar-stats'],
    ];
    $selectedPeriod = $productMixPeriod ?? 'today';
    $rangeLabel = $productMixPeriodLabel ?? ($dashboardRange['label'] ?? 'Today');
    $rangeStart = $dashboardRange['start'] ?? '';
    $rangeEnd = $dashboardRange['end'] ?? '';
    $topProducts = $topProducts ?? $top_products ?? [];
    $salesByCategory = $salesByCategory ?? $sales_by_category ?? [];
    $summaryCards = $summaryCards ?? [];
@endphp

<div class="preview-card pos-glass-card pos-tone-primary" id="employee-product-mix">
    <div class="preview-card-header">
        <div>
            <h2 class="preview-card-title">Sales &amp; Product Mix</h2>
            <p class="preview-card-subtitle mb-0">
                <span data-dashboard-range-label>{{ $rangeLabel }}</span>
            </p>
        </div>

        <div class="preview-card-tools" data-dashboard-range>
            <div class="dropdown employee-dashboard-range-dropdown">
                <button class="btn btn-sm btn-label-primary dropdown-toggle employee-dashboard-range-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" data-dashboard-range-toggle>
                    <i class="ti tabler-filter me-1"></i><span data-dashboard-range-toggle-label>{{ $selectedPeriod === 'custom' ? 'Custom range' : $rangeLabel }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end p-2 employee-dashboard-range-menu" style="min-width: 16rem;">
                    @foreach ($periodOptions as $value => [$label, $icon])
                        <li>
                            <a class="dropdown-item rounded @if($selectedPeriod === $value) active @endif"
                               href="javascript:void(0)"
                               data-dashboard-period="{{ $value }}">
                                <i class="ti {{ $icon }} me-2"></i>{{ $label }}
                            </a>
                        </li>
                    @endforeach
                    <li><hr class="dropdown-divider"></li>
                    <li class="px-2">
                        <form data-dashboard-custom>
                            <input type="hidden" name="period" value="custom">
                            <label class="form-label small mb-1 fw-semibold"><i class="ti tabler-calendar-event me-1"></i>Custom range</label>
                            <div class="mb-2">
                                <input type="text" name="start" class="form-control form-control-sm app-datepicker" placeholder="YYYY-MM-DD" value="{{ $selectedPeriod === 'custom' ? $rangeStart : '' }}" autocomplete="off" required>
                            </div>
                            <div class="mb-2">
                                <input type="text" name="end" class="form-control form-control-sm app-datepicker" placeholder="YYYY-MM-DD" value="{{ $selectedPeriod === 'custom' ? $rangeEnd : '' }}" autocomplete="off" required>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary w-100">Apply custom range</button>
                        </form>
                    </li>
                </ul>
            </div>

            <div class="preview-updated">
                <span class="preview-updated-label">Updated</span>
                <span class="preview-updated-time" data-product-mix-updated-time>1 second ago</span>
            </div>

            <button type="button" class="preview-refresh-btn" data-product-mix-refresh aria-label="Refresh sales and product mix">
                <i class="ti tabler-refresh"></i>
            </button>

            <span class="preview-status-dot" data-product-mix-status></span>
        </div>
    </div>

    <div class="preview-card-body">
        <div class="preview-stats-grid pos-ed-kpis pos-ed-kpis--{{ max(1, min(4, count($summaryCards))) }}" data-product-mix-stats>
            @foreach($summaryCards as $card)
                <x-employee.product-mix-stat-card :card="$card" />
            @endforeach
        </div>

        <div class="product-mix-breakdown">
            <div class="product-mix-breakdown-header">
                <div>
                    <h3 class="product-mix-breakdown-title">Product Mix</h3>
                    <p class="product-mix-breakdown-subtitle">Top sellers &amp; categories</p>
                </div>
            </div>

            <div class="product-mix-breakdown-grid">
                <div class="product-mix-panel product-mix-panel--products pos-glass-card pos-tone-warning">
                    <div class="product-mix-panel-head">
                        <span class="product-mix-panel-icon product-mix-panel-icon--warning"><i class="ti tabler-chart-bar" aria-hidden="true"></i></span>
                        <h4 class="product-mix-panel-title">Top Selling Products</h4>
                    </div>

                    <div class="product-mix-empty-state" data-product-mix-top-products-empty @if(count($topProducts)) hidden @endif>
                        <span class="product-mix-empty-badge"><i class="ti tabler-package" aria-hidden="true"></i></span>
                        <p class="product-mix-empty-title">No product sales</p>
                        <p class="product-mix-empty-text">Rankings appear after your first completed order.</p>
                    </div>

                    <div class="product-mix-product-body" data-product-mix-top-body @if(!count($topProducts)) hidden @endif>
                        <div id="employeeTopProductsChart" data-product-mix-top-chart></div>
                    </div>
                </div>

                <div class="product-mix-panel product-mix-panel--categories pos-glass-card pos-tone-primary">
                    <div class="product-mix-panel-head">
                        <span class="product-mix-panel-icon product-mix-panel-icon--primary"><i class="ti tabler-chart-bar" aria-hidden="true"></i></span>
                        <h4 class="product-mix-panel-title">Sales by Category</h4>
                    </div>

                    <div class="product-mix-empty-state" data-product-mix-category-empty @if(count($salesByCategory)) hidden @endif>
                        <span class="product-mix-empty-badge product-mix-empty-badge--info"><i class="ti tabler-category" aria-hidden="true"></i></span>
                        <p class="product-mix-empty-title">No category data</p>
                        <p class="product-mix-empty-text">Category breakdown fills in as sales come through.</p>
                    </div>

                    <div class="product-mix-category-body" data-product-mix-category-body @if(!count($salesByCategory)) hidden @endif>
                        <div id="employeeCategorySalesChart" data-product-mix-category-chart></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
