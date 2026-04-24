@extends('tenant.settings.layout.settings-master')

@php
    $pageTitle = 'Shop Settings - Notifications';
@endphp

@section('content-body')
    <div class="mb-4">
        <h6 class="mb-3">Notification & Loyalty Settings</h6>
        @include('tenant.settings.partials.shop-profile-notifications')
    </div>
@endsection

@section('page-script-content')
    <script>
        window.shopNotificationsSettingsRoutes = {
            save: '{{ route("tenant.settings.shop-profile.notifications.save") }}'
        };
    </script>
    <script src="{{ asset('assets/js/settings/shop-notifications-settings.js') }}"></script>
@endsection
