<div class="preview-card">
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
                <span class="preview-updated-time">19 seconds ago</span>
            </div>

            <button type="button" class="preview-refresh-btn">
                <i class="ti tabler-refresh"></i>
            </button>

            <span class="preview-status-dot"></span>
        </div>
    </div>

    <div class="preview-card-body">
        <div class="preview-stats-grid">
            @foreach($summaryCards as $card)
                <div class="preview-chip {{ $card['chip'] }}">
                    <div>
                        <div class="preview-chip-number-row">
                            <span class="preview-chip-value">{{ $card['value'] }}</span>
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
