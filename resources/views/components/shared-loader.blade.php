@props([
    'message' => 'Please wait...',
    'size' => 'app-loader-spinner-sm',
])

<div {{ $attributes->merge(['class' => 'app-loader-inline-block']) }}>
    <span class="app-loader-spinner {{ $size }}" aria-hidden="true">
        @for ($i = 0; $i < 12; $i++)
            <span class="spoke" style="--angle: {{ $i * 30 }}deg; --delay: {{ number_format($i / 12, 3, '.', '') }}s"></span>
        @endfor
    </span>
    <span class="app-loader-message">{{ $message }}</span>
</div>
