{{-- Shared brand-primary → Bootstrap primary token mapping. Expects $shopBrandPrimary. --}}
@php
    $brandRgb = null;
    if (! empty($shopBrandPrimary) && preg_match('/^#([0-9a-fA-F]{6})$/', $shopBrandPrimary, $m)) {
        $brandRgb = [
            hexdec(substr($m[1], 0, 2)),
            hexdec(substr($m[1], 2, 2)),
            hexdec(substr($m[1], 4, 2)),
        ];
    }
@endphp
:root {
    @if ($shopBrandPrimary)
        --shop-brand-primary: {{ $shopBrandPrimary }};
    @endif
    @if ($brandRgb)
        --bs-primary: {{ $shopBrandPrimary }};
        --bs-primary-rgb: {{ $brandRgb[0] }}, {{ $brandRgb[1] }}, {{ $brandRgb[2] }};
        --bs-link-color: {{ $shopBrandPrimary }};
        --bs-link-hover-color: {{ $shopBrandPrimary }};
    @endif
}
@if ($brandRgb)
.btn-primary {
    --bs-btn-bg: {{ $shopBrandPrimary }};
    --bs-btn-border-color: {{ $shopBrandPrimary }};
    --bs-btn-hover-bg: {{ $shopBrandPrimary }};
    --bs-btn-hover-border-color: {{ $shopBrandPrimary }};
    --bs-btn-active-bg: {{ $shopBrandPrimary }};
    --bs-btn-active-border-color: {{ $shopBrandPrimary }};
}
.text-primary { color: {{ $shopBrandPrimary }} !important; }
@endif
