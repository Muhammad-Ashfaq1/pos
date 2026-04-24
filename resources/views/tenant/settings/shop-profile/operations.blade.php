@extends('tenant.settings.layout.settings-master')

@php
    $pageTitle = 'Shop Settings - Operations';
@endphp

@section('content-body')
    <div class="mb-4">
        <h6 class="mb-3">Operations Settings</h6>
        @include('tenant.settings.partials.shop-profile-operations')
    </div>
@endsection

@section('page-script-content')
    <script>
        window.shopOperationsSettingsRoutes = {
            save: '{{ route("tenant.settings.shop-profile.operations.save") }}'
        };
    </script>
    <script src="{{ asset('assets/js/settings/shop-operations-settings.js') }}"></script>
@endsection
