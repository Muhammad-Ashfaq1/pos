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
                <div class="pos-glass-card pos-tone-{{ $card['tone'] ?? 'primary' }} h-100" data-product-mix-card="{{ $card['key'] }}">
                    <div class="pos-stat-body">
                        <div class="pos-stat-head">
                            <span class="pos-stat-icon"><i class="icon-base ti {{ $card['icon'] }}" aria-hidden="true"></i></span>
                            <h6 class="pos-stat-label">{{ $card['label'] }}</h6>
                        </div>
                        <p class="pos-stat-value" data-product-mix-value="{{ $card['key'] }}" data-product-mix-format="{{ $card['format'] ?? 'number' }}">{{ $card['value'] }}</p>
                        <p class="pos-stat-desc mb-0" data-product-mix-meta="{{ $card['key'] }}">{{ $card['meta'] }}</p>
                    </div>
                </div>
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
                <div class="product-mix-panel product-mix-panel--products pos-glass-card pos-tone-success">
                    <div class="product-mix-panel-head">
                        <span class="product-mix-panel-icon"><i class="ti tabler-chart-bar" aria-hidden="true"></i></span>
                        <h4 class="product-mix-panel-title">Top Products</h4>
                    </div>

                    <div class="product-mix-empty-state" data-product-mix-top-products-empty @if(count($topProducts)) hidden @endif>
                        <span class="product-mix-empty-badge"><i class="ti tabler-package" aria-hidden="true"></i></span>
                        <p class="product-mix-empty-title">No product sales</p>
                        <p class="product-mix-empty-text">Rankings appear after your first completed order.</p>
                    </div>

                    @php
                        $topProductMax = collect($topProducts)->max('revenue') ?: 1;
                        $barPalette = ['#7367F0', '#28C76F', '#FF9F43', '#00CFE8', '#EA5455', '#A8AAAE'];
                    @endphp
                    <div class="product-mix-product-body" data-product-mix-top-body @if(!count($topProducts)) hidden @endif>
                        <div class="product-mix-hbar" data-product-mix-top-chart>
                            @foreach($topProducts as $index => $product)
                                @php
                                    $mixRevenue = (float) $product['revenue'];
                                    $mixPct = max(8, round(($mixRevenue / $topProductMax) * 100));
                                    $mixCompact = abs($mixRevenue) >= 1000
                                        ? ($currencySymbol ?? '$') . number_format($mixRevenue / 1000, 1) . 'k'
                                        : ($currencySymbol ?? '$') . number_format($mixRevenue, 0);
                                @endphp
                                <div class="product-mix-hbar-row"
                                     data-name="{{ $product['name'] }}"
                                     data-sales="{{ ($currencySymbol ?? '$') . number_format($mixRevenue, 2) }}"
                                     data-qty="{{ number_format((float) $product['qty']) }}"
                                     data-color="{{ $barPalette[$index % count($barPalette)] }}">
                                    <span class="product-mix-hbar-track">
                                        <span class="product-mix-hbar-fill" style="width: {{ $mixPct }}%; background: {{ $barPalette[$index % count($barPalette)] }}">
                                            <span class="product-mix-hbar-value">{{ $mixCompact }}</span>
                                        </span>
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <ul class="product-mix-rank-list product-mix-rank-list--compact" data-product-mix-top-list>
                            @foreach($topProducts as $index => $product)
                                @php $mixPct = round(((float) $product['revenue'] / $topProductMax) * 100); @endphp
                                <li class="product-mix-rank-item product-mix-rank-item--compact">
                                    <div class="product-mix-rank-body">
                                        <div class="product-mix-rank-row">
                                            <span class="product-mix-rank-name">{{ $product['name'] }}</span>
                                            <span class="product-mix-rank-meta">{{ number_format($product['qty']) }} · {{ ($currencySymbol ?? '$') . number_format($product['revenue'], 2) }}</span>
                                        </div>
                                        <span class="product-mix-rank-bar" style="--mix-pct: {{ $mixPct }}"></span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="product-mix-panel product-mix-panel--categories pos-glass-card pos-tone-info">
                    <div class="product-mix-panel-head">
                        <span class="product-mix-panel-icon product-mix-panel-icon--info"><i class="ti tabler-chart-donut-3" aria-hidden="true"></i></span>
                        <h4 class="product-mix-panel-title">By Category</h4>
                    </div>

                    <div class="product-mix-empty-state" data-product-mix-category-empty @if(count($salesByCategory)) hidden @endif>
                        <span class="product-mix-empty-badge product-mix-empty-badge--info"><i class="ti tabler-category" aria-hidden="true"></i></span>
                        <p class="product-mix-empty-title">No category data</p>
                        <p class="product-mix-empty-text">Category breakdown fills in as sales come through.</p>
                    </div>

                    <div class="product-mix-category-body" data-product-mix-category-body @if(!count($salesByCategory)) hidden @endif>
                        <div class="product-mix-chart-wrap" data-product-mix-category-wrap>
                            <div id="employeeCategoryMixChart" data-product-mix-category-chart></div>
                        </div>

                        @php
                            $categoryTotal = collect($salesByCategory)->sum('revenue') ?: 1;
                        @endphp
                        <ul class="product-mix-rank-list product-mix-rank-list--compact" data-product-mix-category-list>
                            @foreach($salesByCategory as $category)
                                @php $sharePct = round(((float) $category['revenue'] / $categoryTotal) * 100); @endphp
                                <li class="product-mix-rank-item product-mix-rank-item--compact">
                                    <div class="product-mix-rank-body">
                                        <div class="product-mix-rank-row">
                                            <span class="product-mix-rank-name">{{ $category['name'] }}</span>
                                            <span class="product-mix-rank-meta">{{ ($currencySymbol ?? '$') . number_format($category['revenue'], 2) }} · {{ $sharePct }}%</span>
                                        </div>
                                        <span class="product-mix-rank-bar product-mix-rank-bar--info" style="--mix-pct: {{ $sharePct }}"></span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
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
