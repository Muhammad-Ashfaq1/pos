@extends('tenant.settings.layout.settings-master')

@php
    $pageTitle = 'Shop Settings - Regional & Billing';
@endphp

@section('content-body')
    <div class="mb-4">
        <h6 class="mb-3">Regional & Billing Settings</h6>
        @include('tenant.settings.partials.shop-profile-regional')
    </div>
@endsection

@section('page-script-content')
    <script>
        window.shopRegionalSettingsRoutes = {
            save: '{{ route("tenant.settings.shop-profile.regional.save") }}'
        };
    </script>
    <script src="{{ asset('assets/js/settings/shop-regional-settings.js') }}"></script>
@endsection
