@extends('layouts.employee-portal')

@section('title', 'Vehicles')

@section('extra-css')
    <link rel="stylesheet" href="{{ asset('assets/css/employee-orders.css') }}?v={{ filemtime(public_path('assets/css/employee-orders.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
@endsection

@section('content')
    <div class="employee-orders-page pb-5">
        <x-employee.page-header
            title="Vehicles"
            :back-url="route('employee.customers.index')"
            back-title="Back to customers"
        />

        @include('tenant.ecommerce.vehicles.partials.listing-page', [
            'vehicleSaveUrl' => $vehicleSaveUrl ?? route('employee.vehicles.save'),
        ])
    </div>
@endsection

@push('page-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
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
