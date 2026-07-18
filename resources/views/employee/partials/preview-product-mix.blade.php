<div class="preview-card" id="employee-product-mix">
    <div class="preview-card-header">
        <div>
            <h2 class="preview-card-title">Product Mix</h2>
        </div>

        <div class="preview-card-tools">
            <select class="preview-select" aria-label="Employee dashboard filter">
                <option selected>Today (Default)</option>
                <option>This Week</option>
                <option>This Month</option>
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
        <div class="preview-stats-grid" data-product-mix-stats>
            @foreach($summaryCards as $card)
                <div class="preview-chip {{ $card['chip'] }}">
                    <div>
                        <div class="preview-chip-number-row">
                            <span class="preview-chip-value" data-product-mix-value="{{ $card['key'] }}">{{ $card['value'] }}</span>
                            <span class="preview-chip-label">{{ $card['label'] }}</span>
                        </div>
                        <div class="preview-chip-meta">{{ $card['meta'] }}</div>
                    </div>
                    <i class="ti {{ $card['icon'] }} preview-chip-icon"></i>
                </div>
            @endforeach
        </div>
    </div>
</div>
