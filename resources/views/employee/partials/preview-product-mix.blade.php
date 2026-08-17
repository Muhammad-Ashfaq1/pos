@php
    $periodOptions = [
        'today' => 'Today (Default)',
        'yesterday' => 'Yesterday',
        'week' => 'This Week',
        'last_week' => 'Last Week',
        'month' => 'This Month',
        'last_month' => 'Last Month',
        'year' => 'This Year',
    ];
    $selectedPeriod = $productMixPeriod ?? 'today';
    $topProducts = $topProducts ?? $top_products ?? [];
    $salesByCategory = $salesByCategory ?? $sales_by_category ?? [];
    $summaryCards = $summaryCards ?? [];
@endphp

<div class="preview-card pos-glass-card pos-tone-primary" id="employee-product-mix">
    <div class="preview-card-header">
        <div>
            <h2 class="preview-card-title">Sales &amp; Product Mix</h2>
        </div>

        <div class="preview-card-tools">
            <select class="preview-select" data-product-mix-period aria-label="Sales and product mix period filter">
                @foreach ($periodOptions as $value => $label)
                    <option value="{{ $value }}" @selected($selectedPeriod === $value)>{{ $label }}</option>
                @endforeach
            </select>

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

                    {{-- Admin-style Apex hbar, sized for this card --}}
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

                    {{-- Admin-style Apex column chart, sized for this card --}}
                    <div class="product-mix-category-body" data-product-mix-category-body @if(!count($salesByCategory)) hidden @endif>
                        <div id="employeeCategorySalesChart" data-product-mix-category-chart></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="employeeProductMixData">{!! json_encode([
    'top_products' => $topProducts,
    'sales_by_category' => $salesByCategory,
]) !!}</script>
