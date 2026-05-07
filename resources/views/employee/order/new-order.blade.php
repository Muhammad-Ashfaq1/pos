@extends('layouts.employee-portal')

@section('title', 'Create New Order')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}" />
@endpush

@section('content')
    <div class="container-fluid p-4">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('employee.dashboard') }}"
                class="btn btn-sm bg-label-primary bg-opacity-10 text-primary border-0 rounded-pill btn-circle-38 me-3">
                <i class="ti tabler-arrow-left fs-4"></i>
            </a>
            <h4 class="fw-bold mb-0">New Order</h4>
        </div>

        <div class="row g-4">

            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100 pos-sidebar-card">
                    <div class="card-body d-flex flex-column">

                        <div class="mb-3">
                            <label for="customer_type_filter" class="form-label">Customer Type</label>
                            <select
                                id="customer_type_filter"
                                class="form-select filter-control select2"
                                data-placeholder="All customer types"
                                data-allow-clear="true"
                                data-minimum-results-for-search="99">
                                <option value=""></option>
                                @for ($i = 1; $i <= 3; $i++)
                                    <option value="{{ $i }}">Customer Type {{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="order_type_filter" class="form-label">Orders</label>
                            <select
                                id="order_type_filter"
                                class="form-select filter-control select2"
                                data-placeholder="All orders"
                                data-allow-clear="true"
                                data-minimum-results-for-search="99">
                                <option value=""></option>
                                @for ($i = 1; $i <= 2; $i++)
                                    <option value="{{ $i }}">Orders {{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="add_vehicle_filter" class="form-label">Add Vehicle</label>
                            <select
                                id="add_vehicle_filter"
                                class="form-select filter-control select2"
                                data-placeholder="Select a vehicle"
                                data-allow-clear="true">
                                <option value=""></option>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">Add Vehicle {{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <table class="table table-borderless align-middle mb-2">
                            <thead>
                                <tr class="border-bottom">
                                    <th class="p-0" style="width: 40px;"></th>
                                    <th class="p-0">
                                        <div class="row g-0 px-3">
                                            <div class="col-5 small fw-bold">Items</div>
                                            <div class="col-4 small fw-bold text-center">Qty</div>
                                            <div class="col-3 small fw-bold text-end">Price</div>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                        </table>

                        <div class="pos-item-list flex-grow-1 mb-3">
                            <table class="table table-borderless align-middle">
                                <tbody id="cart-items-tbody">
                                    <tr class="empty-cart-message">
                                        <td colspan="2" class="text-center py-5">
                                            <p class="text-muted fw-bold mb-0">No Items Added</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            {{-- <p class="text-muted fw-bold">No Items Added</p> --}}
                        </div>

                        <div class="mt-auto border-top pt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold small">Items :</span>
                                <span class="fw-bold summary-qty">0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Total (without tax)</span>
                                <span class="fw-bold small summary-subtotal">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-4">
                                <h5 class="fw-bold">Final Total</h5>
                                <h5 class="fw-bold text-primary summary-total">$0.00</h5>
                            </div>

                            <div class="row g-2 mb-3 align-items-stretch">
                                <div class="col-6">
                                    <button
                                        class="btn btn-outline-danger w-100 h-100 fw-bold btn-cancel-order d-flex flex-column align-items-center cursor-pointer justify-content-center py-2">
                                        <span class="fs-5">Cancel</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button
                                        class="btn btn-primary w-100 h-100 fw-bold d-flex flex-column align-items-center cursor-pointer justify-content-center py-2 btn-pay"
                                        disabled>
                                        <div class="fs-5 text-warning">$0.00</div>
                                        <div class="small fw-semibold text-warning">Pay</div>
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-3">
                                <div class="text-primary cursor-pointer d-flex flex-column align-items-center"
                                    data-bs-toggle="offcanvas" data-bs-target="#offcanvasDiscount">
                                    <i class="icon-base ti tabler-percentage fs-3 mb-1"></i>
                                    <small class="fw-bold">Discount Order</small>
                                </div>
                                <div class="text-primary cursor-pointer d-flex flex-column align-items-center"
                                    data-bs-toggle="offcanvas" data-bs-target="#offcanvasServiceFee">
                                    <i class="icon-base ti tabler-settings fs-3 mb-1"></i>
                                    <small class="fw-bold">Service Fee</small>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0 h-100 p-4 order-management">

                    {{-- Header (back button + dynamic title + unified search) --}}
                    <div class="d-flex justify-content-between align-items-center mb-4 catalog-header">
                        <div class="d-flex align-items-center gap-3">
                            <button type="button"
                                class="btn btn-sm bg-label-secondary text-primary border-0 rounded-pill btn-circle-38 catalog-back-btn d-none">
                                <i class="ti tabler-arrow-left fs-4"></i>
                            </button>
                            <h3 class="fw-bold mb-0 catalog-title">Categories</h3>
                        </div>
                        <div class="input-group w-50">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="icon-base ti tabler-search"></i></span>
                                <input type="text" class="form-control catalog-search px-3"
                                    placeholder="Search Categories, Sub Categories, Products..." />
                                <button type="button" class="btn btn-icon btn-text-secondary catalog-search-clear d-none"
                                    title="Clear search">
                                    <i class="ti tabler-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Catalog Grid (categories / sub-cats / products / search results all render here) --}}
                    <div class="catalog-view">
                        <div class="row g-3 overflow-auto catalog-grid" style="max-height: 100vh;"></div>
                    </div>

                    {{-- Search results split view (only visible during cross-resource search) --}}
                    <div class="catalog-search-view d-none">
                        <div class="search-section mb-4">
                            <h5 class="fw-bold text-muted mb-3 search-categories-heading">
                                <i class="ti tabler-category"></i> Categories
                                <span class="badge bg-label-primary ms-2 search-categories-count">0</span>
                            </h5>
                            <div class="row g-3 search-categories-grid"></div>
                        </div>
                        <div class="search-section mb-4">
                            <h5 class="fw-bold text-muted mb-3 search-sub-categories-heading">
                                <i class="ti tabler-category-plus"></i> Sub Categories
                                <span class="badge bg-label-primary ms-2 search-sub-categories-count">0</span>
                            </h5>
                            <div class="row g-3 search-sub-categories-grid"></div>
                        </div>
                        <div class="search-section">
                            <h5 class="fw-bold text-muted mb-3 search-products-heading">
                                <i class="ti tabler-package"></i> Products
                                <span class="badge bg-label-primary ms-2 search-products-count">0</span>
                            </h5>
                            <div class="row g-3 search-products-grid"></div>
                        </div>
                    </div>

                    {{-- Product Detail (qty + Add to Cart) --}}
                    <div class="product-details-view d-none mt-4">
                        <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                            <button type="button"
                                class="btn btn-sm bg-label-secondary text-primary border-0 rounded-pill btn-circle-38 btn-back-from-product">
                                <i class="ti tabler-arrow-left fs-4"></i>
                            </button>
                            <h4 class="fw-bold mb-0 product-detail-title">Product</h4>
                        </div>
                        <div class="product-info">
                            <h3 class="fw-bold mb-2 product-name"></h3>
                            <p class="text-muted mb-2">SKU: <span class="text-dark fw-semibold product-sku">—</span></p>
                            <p class="text-muted mb-4">Barcode: <span class="text-dark fw-semibold product-barcode">—</span></p>
                            <h5 class="fw-bold mb-4">
                                <span class="text-dark">1 Unit:</span>
                                <span class="text-primary ms-1 product-price">$0.00</span>
                            </h5>
                            <div class="mb-4">
                                <label class="form-label fw-bold">How many units <span class="text-danger">*</span></label>
                                <input type="number" min="1" step="1"
                                    class="form-control form-control-lg border-primary rounded-3 py-3 product-qty-input"
                                    value="1" />
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="button"
                                    class="btn bg-label-secondary text-primary border-0 px-5 py-3 rounded-3 fw-bold btn-clear-qty">Clear</button>
                                <button type="button"
                                    class="btn btn-primary px-5 py-3 rounded-3 fw-bold btn-add-to-cart">Add to Cart</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            @include('employee.order.partials.catalog-card')
        </div>
    </div>
    @include('employee.order.sidebar-modal');
    @push('page-script')
        <script>
            window.catalogRoutes = {
                categories: @json(route('employee.order.categories')),
                subCategories: @json(route('employee.order.sub-categories')),
                products: @json(route('employee.order.products')),
                search: @json(route('employee.order.search')),
            };
        </script>
        <script src="{{ asset('assets/js/employee/catalog-api.js') }}"></script>
        <script src="{{ asset('assets/js/employee/new-order.js') }}"></script>
    @endpush
@endsection
