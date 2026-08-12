@extends($layout ?? 'layouts.app')

@section('title', 'Vehicles')

@php
    $isEmployeeSurface = ! empty($isEmployeeSurface) || ($layout ?? '') === 'layouts.employee-portal';
@endphp

@if ($isEmployeeSurface)
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/employee-orders.css') }}?v={{ filemtime(public_path('assets/css/employee-orders.css')) }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    @endpush
@endif

@section('content')
    @if ($isEmployeeSurface)
        <div class="employee-orders-page pb-5">
            <x-employee.page-header
                title="Vehicles"
                :back-url="route($customersIndexRoute ?? 'employee.customers.index')"
                back-title="Back to customers"
            />

            @include('tenant.ecommerce.vehicles.partials.listing-page', [
                'vehicleSaveUrl' => $vehicleSaveUrl ?? null,
            ])
        </div>
    @else
        @include('tenant.ecommerce.vehicles.partials.listing-page', [
            'vehicleSaveUrl' => $vehicleSaveUrl ?? null,
        ])
    @endif
@endsection

@push('page-script')
    @if ($isEmployeeSurface)
        <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    @endif
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.20.0/jquery.validate.min.js"></script>
    <script>
        window.vehicleListingUrl = @json($listingUrl);
        window.vehicleEditUrlTemplate = @json($editUrlTemplate);
        window.customerDropdownUrl = @json($customersDropdownUrl);
        window.vehicleDropdownUrl = @json($vehiclesDropdownUrl);
    </script>
    <script src="{{ asset('assets/js/tenant/e-com/vehicle-manager.js') }}?v={{ filemtime(public_path('assets/js/tenant/e-com/vehicle-manager.js')) }}"></script>
    <script src="{{ asset('assets/js/tenant/e-com/vehicles.js') }}?v={{ filemtime(public_path('assets/js/tenant/e-com/vehicles.js')) }}"></script>
@endpush
