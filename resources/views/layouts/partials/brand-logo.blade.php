{{-- OCC brand mark: gradient rounded square with an oil drop --}}
<svg width="{{ $size ?? 30 }}" height="{{ $size ?? 30 }}" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="{{ config('app.name') }} logo">
    <defs>
        <linearGradient id="occBrandGrad" x1="0" y1="0" x2="32" y2="32" gradientUnits="userSpaceOnUse">
            <stop stop-color="#2563EB" />
            <stop offset="1" stop-color="#06B6D4" />
        </linearGradient>
    </defs>
    <rect width="32" height="32" rx="9" fill="url(#occBrandGrad)" />
    <path d="M16 6.5c4 5 6.5 8.3 6.5 11.3a6.5 6.5 0 1 1-13 0c0-3 2.5-6.3 6.5-11.3Z" fill="#fff" />
</svg>
