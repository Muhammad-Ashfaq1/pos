<nav class="preview-bottom-nav">
    @foreach($bottomNav as $item)
        <a href="javascript:void(0)" class="preview-bottom-link">
            <span class="preview-bottom-icon">
                <i class="ti {{ $item['icon'] }}"></i>
            </span>
            <span class="preview-bottom-label">{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
