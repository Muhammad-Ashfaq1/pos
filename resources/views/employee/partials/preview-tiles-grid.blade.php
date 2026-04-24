<div class="preview-tiles-grid">
    @foreach($tiles as $tile)
        <div class="preview-card preview-tile">
            <div class="preview-tile-content">
                <span class="preview-tile-icon-wrap">
                    <i class="ti {{ $tile['icon'] }}"></i>
                </span>
                <h3 class="preview-tile-title">{{ $tile['label'] }}</h3>
            </div>
        </div>
    @endforeach
</div>
