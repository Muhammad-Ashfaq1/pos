@extends('layouts.employee-portal')

@section('title', 'Create New Order')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}?v={{ filemtime(public_path('assets/css/pos.css')) }}" />
@endpush

@section('content')
    <div class="container-fluid p-4">
        <div class="order-entry-screen">
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
                                <div class="d-flex justify-content-between">
                                    <label for="customer_type_filter" class="form-label">Customers <span
                                            class="text-danger">*</span></label>
                                    <a class="text-primary add-customer-btn" href="javascript:void(0);"
                                        data-bs-toggle="modal" data-bs-target="#customerModal">+ Add Customer</a>
                                </div>
                                <select id="customer_type_filter" class="form-select filter-control select2"
                                    data-placeholder="Select a customer" data-allow-clear="false"
                                    data-ajax-url="{{ route('tenant.ecommerce.dropdowns.customers') }}">
                                    <option value=""></option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <label for="order_type_filter" class="form-label">Orders</label>
                                    <a class="text-primary add-order-btn" href="javascript:void(0);">+ Add Order</a>
                                </div>
                                <select id="order_type_filter" class="form-select filter-control select2"
                                    data-placeholder="Select order" data-allow-clear="false"
                                    data-minimum-results-for-search="Infinity">
                                    <option value=""></option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <label for="add_vehicle_filter" class="form-label">Add Vehicle <span
                                            class="text-danger">*</span></label>
                                    <a class="text-primary add-vehicle-btn" href="javascript:void(0);"
                                        data-bs-toggle="modal" data-bs-target="#vehicleModal">+ Add Vehicle</a>
                                </div>
                                <select id="add_vehicle_filter" class="form-select filter-control select2"
                                    data-placeholder="Select a vehicle" data-allow-clear="false"
                                    data-ajax-url="{{ route('tenant.ecommerce.dropdowns.vehicles') }}">
                                    <option value=""></option>
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
                                    <button type="button"
                                        class="btn btn-icon btn-text-secondary catalog-search-clear d-none"
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
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <button type="button"
                                    class="btn btn-sm bg-label-secondary text-primary border-0 rounded-pill btn-circle-38 btn-back-from-product">
                                    <i class="ti tabler-arrow-left fs-4"></i>
                                </button>
                                <h4 class="fw-bold mb-0 product-detail-title">Back to Catalog</h4>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4 ms-0" style="max-width: 480px;">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center gap-3 mb-4">
                                        <div class="avatar avatar-xl">
                                            <div class="avatar-initial rounded-3 bg-label-primary product-image-container">
                                                <i class="ti tabler-package fs-1 product-default-icon"></i>
                                                <img src="" alt="" class="rounded-3 w-100 h-100 object-fit-cover d-none product-image" />
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="fw-bold text-dark mb-0 product-name">Product Name</h4>
                                            <div class="d-flex gap-2 mt-1">
                                                <small class="badge bg-label-secondary px-2 rounded-pill fs-tiny">SKU:
                                                    <span class="product-sku">—</span></small>
                                                <small class="badge bg-label-info px-2 rounded-pill fs-tiny">BC: <span
                                                        class="product-barcode">—</span></small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-light p-3 rounded-4 mb-3 border-start border-primary border-5">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted fw-semibold">Unit Price</span>
                                            <h3 class="fw-bold text-primary mb-0 product-price">$0.00</h3>
                                        </div>
                                    </div>

                                    <div class="row g-2 mb-4">
                                        <div class="col-6">
                                            <div class="bg-label-primary bg-opacity-10 p-2 rounded-3 border border-primary border-opacity-10 text-center">
                                                <small class="text-muted d-block small fw-semibold text-uppercase" style="font-size: 0.6rem;">In Cart</small>
                                                <span class="fw-bold text-primary product-in-cart-qty">0</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-label-success bg-opacity-10 p-2 rounded-3 border border-success border-opacity-10 text-center">
                                                <small class="text-muted d-block small fw-semibold text-uppercase" style="font-size: 0.6rem;">Available</small>
                                                <span class="fw-bold text-success product-available-stock">0</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-muted small text-uppercase mb-2">Quantity</label>
                                        <div class="input-group input-group-lg border border-primary rounded-pill overflow-hidden shadow-sm">
                                            <button class="btn btn-outline-primary border-0 px-4 product-qty-minus-btn"
                                                type="button"><i class="ti tabler-minus"></i></button>
                                            <input type="number" min="1" step="1"
                                                class="form-control border-0 text-center fw-bold fs-4 product-qty-input bg-white"
                                                value="1" />
                                            <button class="btn btn-outline-primary border-0 px-4 product-qty-plus-btn"
                                                type="button"><i class="ti tabler-plus"></i></button>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="button"
                                            class="btn btn-primary btn-lg rounded-pill fw-bold btn-add-to-cart shadow-primary py-3">
                                            <i class="ti tabler-shopping-cart me-2"></i> Add to Cart
                                        </button>
                                        <button type="button" class="btn btn-link text-muted btn-sm btn-clear-qty">
                                            <i class="ti tabler-refresh me-1"></i> Clear Selection
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                @include('employee.order.partials.catalog-card')
            </div>

        </div>

        <div class="order-payment-screen d-none">
            <div class="d-flex align-items-center mb-4">
                <button type="button"
                    class="btn btn-sm bg-label-primary bg-opacity-10 text-primary border-0 rounded-pill btn-circle-38 me-3 payment-back-btn">
                    <i class="ti tabler-arrow-left fs-4"></i>
                </button>
                <h4 class="fw-bold mb-0">New Order</h4>
            </div>

            <div class="row g-4 payment-layout">
                <div class="col-lg-6">
                    <div class="card payment-panel h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="payment-summary">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                    <h5 class="fw-bold mb-0">Order No. <span
                                            class="text-primary text-decoration-underline payment-order-number">Draft</span>
                                    </h5>
                                    <span class="badge bg-label-primary payment-method-label">Cash</span>
                                </div>
                                <div class="d-flex justify-content-between small mb-3">
                                    <span>Total:</span>
                                    <span class="fw-bold payment-total">$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between small border-bottom pb-3 mb-3">
                                    <span>Sub Total:</span>
                                    <span class="fw-bold payment-subtotal">$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold mb-0">Balance Due:</h5>
                                    <h3 class="fw-bold text-primary mb-0 payment-balance-due">$0.00</h3>
                                </div>
                            </div>

                            <div class="payment-items-list flex-grow-1"></div>

                            <div class="payment-actions-grid mt-4">
                                <button type="button" class="payment-utility-btn">
                                    <i class="ti tabler-gift"></i>
                                    <span>Gift Cards</span>
                                </button>
                                <button type="button" class="payment-utility-btn">
                                    <i class="ti tabler-trophy"></i>
                                    <span>Reward Cards</span>
                                </button>
                                <button type="button" class="payment-utility-btn">
                                    <i class="ti tabler-mail"></i>
                                    <span>Email Receipt</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card payment-panel h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="fw-bold border-bottom pb-3 mb-4">Payment Amount</h5>

                            <div class="payment-amount-display mb-3">$</div>
                            <div class="d-flex justify-content-between small mb-4">
                                <span>Remaining: <strong class="payment-remaining">$0.00</strong></span>
                                <span>Change: <strong class="payment-change-due">$0.00</strong></span>
                            </div>

                            <div class="payment-keypad mt-auto">
                                <button type="button" class="payment-key" data-payment-key="7">7</button>
                                <button type="button" class="payment-key" data-payment-key="8">8</button>
                                <button type="button" class="payment-key" data-payment-key="9">9</button>
                                <button type="button" class="payment-key" data-payment-quick="10">$10</button>
                                <button type="button" class="payment-key" data-payment-key="4">4</button>
                                <button type="button" class="payment-key" data-payment-key="5">5</button>
                                <button type="button" class="payment-key" data-payment-key="6">6</button>
                                <button type="button" class="payment-key" data-payment-quick="20">$20</button>
                                <button type="button" class="payment-key" data-payment-key="1">1</button>
                                <button type="button" class="payment-key" data-payment-key="2">2</button>
                                <button type="button" class="payment-key" data-payment-key="3">3</button>
                                <button type="button" class="payment-key" data-payment-quick="50">$50</button>
                                <button type="button" class="payment-key" data-payment-key="0">0</button>
                                <button type="button" class="payment-key" data-payment-key=".">.</button>
                                <button type="button" class="payment-key" data-payment-key="clear">C</button>
                                <button type="button" class="payment-key" data-payment-key="backspace">
                                    <i class="ti tabler-arrow-left"></i>
                                </button>
                            </div>

                            <div class="payment-methods mt-3">
                                <button type="button" class="payment-method-btn"
                                    data-payment-method="cash">Cash</button>
                                <button type="button" class="payment-method-btn" data-payment-method="card">Credit/Debit
                                    Card</button>
                                <button type="button" class="payment-method-btn"
                                    data-payment-method="check">Check</button>
                            </div>

                            <button type="button" class="btn btn-primary w-100 fw-bold mt-3 btn-checkout-order" disabled>
                                Checkout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('employee.order.sidebar-modal')
    @include('tenant.ecommerce.customers.partials.save-modal')
    @include('tenant.ecommerce.vehicles.partials.save-modal')
@endsection

@push('page-script')
    <script>
        window.catalogRoutes = {
            categories: @json(route('employee.order.categories')),
            subCategories: @json(route('employee.order.sub-categories')),
            products: @json(route('employee.order.products')),
            search: @json(route('employee.order.search')),
            save: @json(route('employee.order.save')),
            dropdownCustomers: @json(route('tenant.ecommerce.dropdowns.customers')),
            dropdownVehicles: @json(route('tenant.ecommerce.dropdowns.vehicles')),
        };
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.20.0/jquery.validate.min.js"></script>
    <script src="{{ asset('assets/js/tenant/e-com/customer-manager.js') }}"></script>
    <script
        src="{{ asset('assets/js/tenant/e-com/vehicle-manager.js') }}?v={{ filemtime(public_path('assets/js/tenant/e-com/vehicle-manager.js')) }}">
    </script>

    <script src="{{ asset('assets/js/employee/catalog-api.js') }}"></script>
    <script
        src="{{ asset('assets/js/employee/new-order.js') }}?v={{ filemtime(public_path('assets/js/employee/new-order.js')) }}">
    </script>
@endpush
