<div class="preview-tiles-grid">
    @foreach($tiles as $tile)
        @php $tone = $tile['tone'] ?? 'primary'; @endphp
        @if(isset($tile['url']))
            <a href="{{ $tile['url'] }}" class="preview-card preview-tile pos-glass-card pos-tone-{{ $tone }}">
        @else
            <div class="preview-card preview-tile preview-tile--static pos-glass-card pos-tone-{{ $tone }}">
        @endif
            <div class="preview-tile-content">
                <span class="preview-tile-icon-wrap">
                    <i class="ti {{ $tile['icon'] }}" aria-hidden="true"></i>
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
