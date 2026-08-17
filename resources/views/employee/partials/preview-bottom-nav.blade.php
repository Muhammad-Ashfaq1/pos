<nav class="preview-bottom-nav">
    @foreach($bottomNav as $item)
        @php
            $itemUrl = $item['url'] ?? '';
            $isActive = $itemUrl !== '' && str_starts_with(rtrim(url()->current(), '/'), rtrim($itemUrl, '/'));
        @endphp
        <a href="{{ $itemUrl !== '' ? $itemUrl : 'javascript:void(0)' }}"
           class="preview-bottom-link {{ $isActive ? 'is-active' : '' }}">
            <span class="preview-bottom-icon">
                <i class="ti {{ $item['icon'] }}"></i>
            </span>
            <span class="preview-bottom-label">{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
