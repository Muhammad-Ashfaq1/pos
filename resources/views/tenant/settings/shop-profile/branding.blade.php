@extends('tenant.settings.layout.settings-master')

@php
    $pageTitle = 'Shop Settings - Branding';
@endphp

@push('page-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/dropzone/dropzone.css') }}" />
@endpush

@section('content-body')
    @include('tenant.settings.partials.shop-profile-branding')
@endsection

@section('page-script-content')
    <script src="{{ asset('assets/vendor/libs/dropzone/dropzone.js') }}"></script>
    <script>
        window.shopBrandingSettingsRoutes = {
            save: '{{ route("tenant.settings.shop-profile.branding.save") }}'
        };
    </script>
    <script src="{{ asset('assets/js/settings/shop-branding-settings.js') }}?v={{ filemtime(public_path('assets/js/settings/shop-branding-settings.js')) }}"></script>
@endsection
