<div class="preview-tiles-grid">
    @foreach($tiles as $tile)
        @if(isset($tile['url']))
            <a href="{{ $tile['url'] }}" class="preview-card preview-tile" style="text-decoration: none;">
        @else
            <div class="preview-card preview-tile">
        @endif
            <div class="preview-tile-content">
                <span class="preview-tile-icon-wrap">
                    <i class="ti {{ $tile['icon'] }}"></i>
                </span>
                <h3 class="preview-tile-title">{{ $tile['label'] }}</h3>
            </div>
        @if(isset($tile['url']))
            </a>
        @else
            </div>
        @endif
    @endforeach
</div>
