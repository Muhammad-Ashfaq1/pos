@extends('tenant.settings.layout.settings-master')

@php
    $pageTitle = 'Shop Settings - Order & Invoice';
@endphp

@section('content-body')
    <div class="mb-4">
        <h6 class="mb-3">Order & Invoice Settings</h6>
        @include('tenant.settings.partials.shop-profile-order-invoice')
    </div>
@endsection

@section('page-script-content')
    <script>
        window.shopOrderInvoiceSettingsRoutes = {
            save: '{{ route("tenant.settings.shop-profile.order-invoice.save") }}'
        };
    </script>
    <script src="{{ asset('assets/js/settings/shop-order-invoice-settings.js') }}"></script>
@endsection
