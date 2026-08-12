@extends('layouts.app')

@section('title', 'Customers')

@section('content')
    @include('tenant.ecommerce.customers.partials.listing-page', [
        'customerTypes' => \App\Models\Customer::typeOptions(),
    ])
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.20.0/jquery.validate.min.js"></script>
    <script>
        window.customerListingUrl = @json($listingUrl);
        window.customerEditUrlTemplate = @json($editUrlTemplate);
        window.customerVehicleIndexUrlTemplate = @json($vehicleIndexUrlTemplate);
        window.customerSettings = {
            vehicleRequired: @json(app(\App\Support\Tenancy\TenantContext::class)->current()?->isVehicleRequired() ?? true),
        };
    </script>
    <script src="{{ asset('assets/js/tenant/e-com/customers.js') }}?v={{ filemtime(public_path('assets/js/tenant/e-com/customers.js')) }}"></script>
    <script src="{{ asset('assets/js/tenant/e-com/customer-manager.js') }}?v={{ filemtime(public_path('assets/js/tenant/e-com/customer-manager.js')) }}"></script>
@endsection
