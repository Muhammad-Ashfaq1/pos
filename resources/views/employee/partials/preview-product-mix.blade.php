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
@endphp

<div class="preview-card" id="employee-product-mix">
    <div class="preview-card-header">
        <div>
            <h2 class="preview-card-title">Product Mix</h2>
        </div>

        <div class="preview-card-tools">
            <select class="preview-select" data-product-mix-period aria-label="Product mix period filter">
                @foreach ($periodOptions as $value => $label)
                    <option value="{{ $value }}" @selected($selectedPeriod === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="preview-updated">
                <span class="preview-updated-label">Updated</span>
                <span class="preview-updated-time" data-product-mix-updated-time>1 second ago</span>
            </div>

            <button type="button" class="preview-refresh-btn" data-product-mix-refresh aria-label="Refresh product mix">
                <i class="ti tabler-refresh"></i>
            </button>

            <span class="preview-status-dot" data-product-mix-status></span>
        </div>
    </div>

    <div class="preview-card-body">
        <div class="preview-stats-grid pos-ed-kpis" data-product-mix-stats>
            @php $edTones = ['primary', 'info', 'success']; @endphp
            @foreach($summaryCards as $i => $card)
                <div class="pos-glass-card pos-tone-{{ $edTones[$i % count($edTones)] }} h-100">
                    <div class="pos-stat-body">
                        <div class="pos-stat-head">
                            <span class="pos-stat-icon"><i class="icon-base ti {{ $card['icon'] }}" aria-hidden="true"></i></span>
                            <h6 class="pos-stat-label">{{ $card['label'] }}</h6>
                        </div>
                        <p class="pos-stat-value" data-product-mix-value="{{ $card['key'] }}">{{ $card['value'] }}</p>
                        <p class="pos-stat-desc mb-0" data-product-mix-meta="{{ $card['key'] }}">{{ $card['meta'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
