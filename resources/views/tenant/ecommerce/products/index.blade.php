@extends($layout ?? 'layouts.app')

@section('title', ! empty($isEmployeeSurface) ? 'Product Setup' : 'Products')

@php
    $isEmployeeSurface = ! empty($isEmployeeSurface) || ($layout ?? '') === 'layouts.employee-portal';
@endphp

@if ($isEmployeeSurface)
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/employee-orders.css') }}?v={{ filemtime(public_path('assets/css/employee-orders.css')) }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/dropzone/dropzone.css') }}" />
    @endpush
@endif

@section('content')
    @if ($isEmployeeSurface)
        <div class="employee-orders-page">
            <x-employee.page-header
                title="Product Setup"
                :back-url="route($dashboardRoute ?? 'employee.dashboard')"
                back-title="Back to dashboard"
            >
                <x-slot:actions>
                    @include('tenant.ecommerce.products.partials.toolbar-actions', [
                        'productTypes' => $productTypes,
                        'showInventoryFilter' => false,
                    ])
                </x-slot:actions>
            </x-employee.page-header>

            @include('tenant.ecommerce.products.partials.table-and-modal', [
                'productTypes' => $productTypes,
                'saveUrl' => $saveUrl,
            ])
        </div>
    @else
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div class="d-flex align-items-center gap-2" id="productTableActions">
                @include('tenant.ecommerce.products.partials.toolbar-actions', [
                    'productTypes' => $productTypes,
                    'showInventoryFilter' => true,
                ])
            </div>
        </div>

        @include('tenant.ecommerce.products.partials.table-and-modal', [
            'productTypes' => $productTypes,
            'saveUrl' => $saveUrl,
        ])
    @endif
@endsection

@push('page-script')
    @if ($isEmployeeSurface)
        <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    @endif
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.20.0/jquery.validate.min.js"></script>
    <script src="{{ asset('assets/vendor/libs/dropzone/dropzone.js') }}"></script>
    <script src="{{ asset('assets/js/media-dropzone.js') }}"></script>
    <script>
        window.productListingUrl = @json($listingUrl);
        window.productEditUrlTemplate = @json($editUrlTemplate);
        window.categoryDropdownUrl = @json($categoriesDropdownUrl);
        window.subCategoryDropdownUrl = @json($subCategoriesDropdownUrl);
        window.discountDropdownUrl = @json($discountDropdownUrl);
        window.productTypes = @json($productTypes);
    </script>
    <script src="{{ asset('assets/js/tenant/e-com/products.js') }}?v={{ filemtime(public_path('assets/js/tenant/e-com/products.js')) }}"></script>
@endpush
