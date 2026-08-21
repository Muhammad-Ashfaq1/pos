@extends($layout ?? 'layouts.app')

@section('title', 'Customers')

@php
    $isEmployeeSurface = ! empty($isEmployeeSurface) || ($layout ?? '') === 'layouts.employee-portal';
@endphp

@if ($isEmployeeSurface)
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/pos-glass.css') }}?v={{ filemtime(public_path('assets/css/pos-glass.css')) }}" />
        <link rel="stylesheet" href="{{ asset('assets/css/employee-orders.css') }}?v={{ filemtime(public_path('assets/css/employee-orders.css')) }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    @endpush
@endif

@section('content')
    @if ($isEmployeeSurface)
        <div class="employee-orders-page pb-5">
            <x-employee.page-header
                title="Customers"
                :back-url="route($dashboardRoute ?? 'employee.dashboard')"
                back-title="Back to dashboard"
            />

            @include('tenant.ecommerce.customers.partials.listing-page', [
                'customerTypes' => $customerTypes,
                'customerSaveUrl' => $customerSaveUrl ?? null,
            ])
        </div>
    @else
        @include('tenant.ecommerce.customers.partials.listing-page', [
            'customerTypes' => $customerTypes,
            'customerSaveUrl' => $customerSaveUrl ?? null,
        ])
    @endif
@endsection

@push('page-script')
    @if ($isEmployeeSurface)
        <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    @endif
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
@endpush
