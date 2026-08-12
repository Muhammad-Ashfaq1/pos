<div class="preview-tiles-grid">
    @php $tileTones = ['primary', 'info', 'success', 'warning']; @endphp
    @foreach($tiles as $i => $tile)
        @php $tone = $tileTones[$i % count($tileTones)]; @endphp
        @if(isset($tile['url']))
            <a href="{{ $tile['url'] }}" class="preview-card preview-tile pos-glass-card pos-tone-{{ $tone }}" style="text-decoration: none;">
        @else
            <div class="preview-card preview-tile pos-glass-card pos-tone-{{ $tone }}">
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
