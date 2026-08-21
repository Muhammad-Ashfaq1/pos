@extends($layout ?? 'layouts.app')

@section('title', ! empty($isEmployeeSurface) ? 'Product Setup' : 'Products')

@php
    $isEmployeeSurface = ! empty($isEmployeeSurface) || ($layout ?? '') === 'layouts.employee-portal';
@endphp

@push('styles')
    @if ($isEmployeeSurface)
        <link rel="stylesheet" href="{{ asset('assets/css/pos-glass.css') }}?v={{ filemtime(public_path('assets/css/pos-glass.css')) }}" />
        <link rel="stylesheet" href="{{ asset('assets/css/employee-orders.css') }}?v={{ filemtime(public_path('assets/css/employee-orders.css')) }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/dropzone/dropzone.css') }}" />
    @else
        @include('partials.pos-listing-assets')
    @endif
@endpush

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
                'isEmployeeSurface' => true,
            ])
        </div>
    @else
        <div class="pos-listing">
            <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
                <div class="pos-listing-toolbar">
                    <h4 class="pos-listing-title">Products</h4>
                    <div class="pos-listing-search-slot" aria-hidden="true"></div>
                    <div class="pos-listing-toolbar-tools">
                        <div class="pos-listing-toolbar-actions" id="productTableActions">
                            @include('tenant.ecommerce.products.partials.toolbar-actions', [
                                'productTypes' => $productTypes,
                                'showInventoryFilter' => true,
                            ])
                        </div>
                    </div>
                </div>

                @include('tenant.ecommerce.products.partials.table-and-modal', [
                    'productTypes' => $productTypes,
                    'saveUrl' => $saveUrl,
                    'isEmployeeSurface' => false,
                    'renderModal' => false,
                ])
            </div>

            @include('tenant.ecommerce.products.partials.table-and-modal', [
                'productTypes' => $productTypes,
                'saveUrl' => $saveUrl,
                'isEmployeeSurface' => false,
                'renderTable' => false,
            ])
        </div>
    @endif
@endsection

@push('page-script')
    @if ($isEmployeeSurface)
        <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    @else
        <script src="{{ asset('assets/js/pos-listing-toolbar.js') }}?v={{ filemtime(public_path('assets/js/pos-listing-toolbar.js')) }}"></script>
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
        window.serviceDropdownUrl = @json($servicesDropdownUrl);
        window.productTypes = @json($productTypes);
    </script>
    <script src="{{ asset('assets/js/tenant/e-com/products.js') }}?v={{ filemtime(public_path('assets/js/tenant/e-com/products.js')) }}"></script>
@endpush
