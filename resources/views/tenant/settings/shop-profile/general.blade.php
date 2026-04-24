@extends('tenant.settings.layout.settings-master')

@php
    $pageTitle = 'Shop Settings - General';
@endphp

@section('content-body')
    <div class="mb-4">
        <h6 class="mb-3">Shop Information</h6>
        @include('tenant.settings.partials.shop-profile-general')
    </div>
@endsection

@section('page-script-content')
    <script>
        window.shopGeneralSettingsRoutes = {
            save: '{{ route("tenant.settings.shop-profile.general.save") }}'
        };
    </script>
    <script src="{{ asset('assets/js/settings/shop-general-settings.js') }}"></script>
@endsection
