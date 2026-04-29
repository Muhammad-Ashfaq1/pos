<div class="preview-card preview-operations-card">
    <div class="preview-card-header">
        <div>
            <h2 class="preview-card-title">Operations</h2>
        </div>
    </div>

    <div class="preview-card-body">
        @foreach($operations as $operation)
            @if(isset($operation['url']))
                <a href="{{ $operation['url'] }}" class="preview-operation-item" style="text-decoration: none;">
            @else
                <div class="preview-operation-item">
            @endif
                <div class="preview-operation-main">
                    <span class="preview-operation-icon">
                        <i class="ti {{ $operation['icon'] }}"></i>
                    </span>
                    <span class="preview-operation-label">{{ $operation['label'] }}</span>
                </div>

                <span class="preview-operation-link">
                    <i class="ti tabler-arrow-up-right"></i>
                </span>
            @if(isset($operation['url']))
                </a>
            @else
                </div>
            @endif
        @endforeach
    </div>
</div>
