{{--
    Shop brand mark for tenant/employee/customer chrome.
    Falls back to AutoServe SVG + app name when the shop has no logo/name.
--}}
@php
    $shopTenant = $shopTenant
        ?? (function_exists('tenant') ? tenant() : null)
        ?? app(\App\Support\Tenancy\TenantContext::class)->current()
        ?? (auth('customer')->user()?->tenant ?? null)
        ?? (auth()->user()?->tenant ?? null);

    $brandName = $shopTenant?->display_name
        ?: $shopTenant?->shop_name
        ?: $shopTenant?->business_name
        ?: (string) config('app.name');

    $logoUrl = $shopTenant?->logoUrl();
    $tagline = $shopTenant?->brandTagline();
    $brandColor = $shopTenant?->brandPrimaryColor();
    $size = $size ?? 30;
    $showTagline = $showTagline ?? false;
    $textClass = $textClass ?? 'app-brand-text demo menu-text fw-bold ms-3';
@endphp

<span class="app-brand-logo demo shop-brand-logo">
    @if ($logoUrl)
        <img
            src="{{ $logoUrl }}"
            alt="{{ $brandName }}"
            width="{{ $size }}"
            height="{{ $size }}"
            style="object-fit: contain; border-radius: 6px;" />
    @else
        @include('layouts.partials.brand-logo', ['size' => $size])
    @endif
</span>
<span class="{{ $textClass }}" @if($brandColor) style="color: {{ $brandColor }};" @endif>
    {{ $brandName }}
    @if ($showTagline && $tagline)
        <span class="d-block small fw-normal text-muted text-truncate" style="max-width: 11rem; line-height: 1.2;">{{ $tagline }}</span>
    @endif
</span>
