@extends('layouts.app')

@section('title', 'Vehicles')

@section('content')
    @include('tenant.ecommerce.vehicles.partials.listing-page', [
        'vehicleSaveUrl' => route('tenant.ecommerce.vehicles.save'),
    ])
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.20.0/jquery.validate.min.js"></script>
    <script>
        window.vehicleListingUrl = @json($listingUrl);
        window.vehicleEditUrlTemplate = @json($editUrlTemplate);
        window.customerDropdownUrl = @json($customersDropdownUrl);
        window.vehicleDropdownUrl = @json($vehiclesDropdownUrl);
    </script>
    <script src="{{ asset('assets/js/tenant/e-com/vehicle-manager.js') }}?v={{ filemtime(public_path('assets/js/tenant/e-com/vehicle-manager.js')) }}"></script>
    <script src="{{ asset('assets/js/tenant/e-com/vehicles.js') }}?v={{ filemtime(public_path('assets/js/tenant/e-com/vehicles.js')) }}"></script>
@endsection
