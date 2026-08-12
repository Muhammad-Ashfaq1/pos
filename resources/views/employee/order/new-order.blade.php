@extends($layout ?? 'layouts.employee-portal')

@section('title', !empty($invoiceMode) ? 'Create Invoice' : 'Create New Order')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/employee-orders.css') }}?v={{ filemtime(public_path('assets/css/employee-orders.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}?v={{ filemtime(public_path('assets/css/pos.css')) }}" />
@endpush

@section('content')
    @php
        $invoiceMode = ! empty($invoiceMode);
        $pageBackUrl = $invoiceMode
            ? route($orderRoutes['invoices_index'])
            : route($dashboardRoute ?? 'employee.dashboard');
        $pageBackTitle = $invoiceMode ? 'Back to invoices' : 'Back to dashboard';
        $pageTitle = $invoiceMode ? 'Invoices' : 'New Order';
    @endphp
    <div class="employee-orders-page" @if($invoiceMode) data-invoice-mode="1" @endif>
        <x-employee.page-header :title="$pageTitle" :back-url="$pageBackUrl" :back-title="$pageBackTitle" />

        <div class="order-entry-screen">

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
                                <div class="customer-discount-banner d-none mt-2"></div>
                            </div>

                            @if($invoiceMode)
                            <div class="mb-3">
                                <label for="invoice_date" class="form-label">Invoice Date <span class="text-danger">*</span></label>
                                <input
                                    type="date"
                                    id="invoice_date"
                                    name="invoice_date"
                                    class="form-control"
                                    value="{{ now()->toDateString() }}"
                                    required />
                            </div>
                            @endif

                            <div class="mb-3 @if($invoiceMode) d-none @endif">
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

                            @if($vehicleRequired)
                            <div class="mb-3" id="vehicleFieldWrapper">
                                <div class="d-flex justify-content-between">
                                    <label for="add_vehicle_filter" class="form-label">Add Vehicle</label>
                                    <a class="text-primary add-vehicle-btn" href="javascript:void(0);"
                                        data-bs-toggle="modal" data-bs-target="#vehicleModal">+ Add Vehicle</a>
                                </div>
                                <select id="add_vehicle_filter" class="form-select filter-control select2"
                                    data-placeholder="Select a vehicle (optional)" data-allow-clear="false"
                                    data-ajax-url="{{ route('tenant.ecommerce.dropdowns.vehicles') }}">
                                    <option value=""></option>
                                </select>
                            </div>
                            @endif

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
                                    <span class="text-muted small">Subtotal:</span>
                                    <span class="fw-bold small summary-subtotal">@money(0)</span>
                                </div>
                                <div class="summary-service-price-row d-none d-flex justify-content-between mb-2">
                                    <span class="text-muted small summary-service-price-title">Service Price</span>
                                    <span class="fw-bold small summary-service-price">@money(0)</span>
                                </div>
                                <div class="summary-service-price-breakdowns d-none"></div>
                                <div class="summary-service-fee-row d-none d-flex justify-content-between mb-2">
                                    <span class="text-muted small summary-service-fee-title">Service fee</span>
                                    <span class="fw-bold small summary-service-fee">@money(0)</span>
                                </div>
                                <div class="summary-service-fee-breakdowns d-none"></div>
                                <div class="summary-discount-lines d-none d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Discount</span>
                                    <span class="fw-bold small text-success summary-discount">-@money(0)</span>
                                </div>
                                <div class="summary-discount-breakdowns d-none"></div>
                                <div class="summary-tax-row d-none d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Tax</span>
                                    <span class="fw-bold small summary-tax">@money(0)</span>
                                </div>
                                <div class="summary-tax-breakdowns d-none"></div>
                                <div class="d-flex justify-content-between mb-4">
                                    <h5 class="fw-bold">Final Total</h5>
                                    <h5 class="fw-bold text-primary summary-total">@money(0)</h5>
                                </div>

                                @if($invoiceMode)
                                <div class="row g-2 mb-3 align-items-stretch">
                                    <div class="col-4">
                                        <button
                                            type="button"
                                            class="btn btn-outline-danger w-100 h-100 fw-bold btn-cancel-order d-flex flex-column align-items-center cursor-pointer justify-content-center py-2">
                                            <span class="fs-6">Cancel</span>
                                        </button>
                                    </div>
                                    <div class="col-4">
                                        <button
                                            type="button"
                                            class="btn btn-primary w-100 h-100 fw-bold btn-save-invoice d-flex flex-column align-items-center cursor-pointer justify-content-center py-2"
                                            disabled>
                                            <span class="fs-6">Save</span>
                                        </button>
                                    </div>
                                    <div class="col-4">
                                        <button
                                            type="button"
                                            class="btn btn-primary w-100 h-100 fw-bold btn-save-send-invoice d-flex flex-column align-items-center cursor-pointer justify-content-center py-2 px-1"
                                            disabled>
                                            <span class="fs-6 lh-sm text-center">Save &amp; Send Email</span>
                                        </button>
                                    </div>
                                </div>
                                @else
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
                                            <div class="fs-5 text-warning">@money(0)</div>
                                            <div class="small fw-semibold text-warning">Pay</div>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary w-100 fw-bold py-2 btn-save-estimate"
                                        disabled>
                                        <i class="ti tabler-file-text me-1"></i> Save Estimate
                                    </button>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-4">
                                        <button type="button" class="btn btn-outline-secondary w-100 py-2 btn-draft-print" disabled>
                                            <i class="ti tabler-printer"></i><br><small class="fw-bold">Print</small>
                                        </button>
                                    </div>
                                    <div class="col-4">
                                        <button type="button" class="btn btn-outline-secondary w-100 py-2 btn-draft-pdf" disabled>
                                            <i class="ti tabler-download"></i><br><small class="fw-bold">PDF</small>
                                        </button>
                                    </div>
                                    <div class="col-4">
                                        <button type="button" class="btn btn-outline-secondary w-100 py-2 btn-draft-share" disabled data-bs-toggle="modal" data-bs-target="#draftShareModal">
                                            <i class="ti tabler-send"></i><br><small class="fw-bold">Share</small>
                                        </button>
                                    </div>
                                </div>
                                @endif

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

                            <div class="row g-3 product-detail-grid">
                                <div class="col-md-4">
                                    <div class="card border-0 shadow-sm rounded-4 product-detail-card">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center gap-3 mb-3">
                                                <div class="avatar avatar-lg flex-shrink-0">
                                                    <div class="avatar-initial rounded-3 bg-label-primary product-image-container">
                                                        <i class="ti tabler-package fs-3 product-default-icon"></i>
                                                        <img src="" alt="" class="rounded-3 w-100 h-100 object-fit-cover d-none product-image" />
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 min-w-0">
                                                    <h6 class="text-wrap fw-bold text-dark mb-1 text-truncate product-name">Product Name</h6>
                                                    <div class="d-flex gap-1 flex-wrap">
                                                        <small class="badge bg-label-secondary px-2 rounded-pill fs-tiny">SKU:
                                                            <span class="product-sku">—</span></small>
                                                        <small class="badge bg-label-info px-2 rounded-pill fs-tiny">BC: <span
                                                                class="product-barcode">—</span></small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="bg-light p-2 rounded-3 mb-3 border-start border-primary border-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted fw-semibold small">Unit Price</span>
                                                    <h5 class="fw-bold text-primary mb-0 product-price">@money(0)</h5>
                                                </div>
                                            </div>

                                            <div class="product-discount-banner d-none mb-2">
                                                <small class="text-success"><i class="ti tabler-discount-2"></i> <span class="product-discount-label"></span></small>
                                            </div>

                                            <div class="d-flex gap-2 mb-3">
                                                <div class="flex-grow-1 bg-label-primary bg-opacity-10 p-2 rounded-3 border border-primary border-opacity-10 text-center">
                                                    <small class="text-muted d-block small fw-semibold text-uppercase" style="font-size: 0.55rem;">Available</small>
                                                    <span class="fw-bold text-primary product-available-stock">0</span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="input-group input-group-sm border border-primary rounded-pill overflow-hidden">
                                                        <button class="btn btn-outline-primary border-0 px-2 product-qty-minus-btn" type="button"><i class="ti tabler-minus fs-5"></i></button>
                                                        <input type="number" min="1" step="1" class="form-control border-0 text-center fw-bold product-qty-input bg-white" value="1" />
                                                        <button class="btn btn-outline-primary border-0 px-2 product-qty-plus-btn" type="button"><i class="ti tabler-plus fs-5"></i></button>
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="button" class="btn btn-primary btn-sm rounded-pill fw-bold w-100 btn-add-to-cart shadow-primary">
                                                <i class="ti tabler-shopping-cart me-1"></i> Add to Cart
                                            </button>
                                        </div>
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
            {{-- <div class="d-flex align-items-center mb-4">
                <button type="button"
                    class="btn btn-sm bg-label-primary bg-opacity-10 text-primary border-0 rounded-pill btn-circle-38 me-3 payment-back-btn">
                    <i class="ti tabler-arrow-left fs-4"></i>
                </button>
                <h4 class="fw-bold mb-0">New Order</h4>
            </div> --}}

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
                                    <span class="fw-bold payment-total">@money(0)</span>
                                </div>
                                <div class="d-flex justify-content-between small border-bottom pb-3 mb-3">
                                    <span>Sub Total:</span>
                                    <span class="fw-bold payment-subtotal">@money(0)</span>
                                </div>
                                <div class="payment-service-price-section d-none border-bottom pb-3 mb-3">
                                    <div class="payment-service-price-row d-flex justify-content-between small">
                                        <span class="payment-service-price-title">Service Price:</span>
                                        <span class="fw-bold payment-service-price">@money(0)</span>
                                    </div>
                                    <div class="payment-service-price-breakdowns mt-2"></div>
                                </div>
                                <div class="payment-service-fee-section d-none border-bottom pb-3 mb-3">
                                    <div class="payment-service-fee-row d-flex justify-content-between small">
                                        <span class="payment-service-fee-title">Service Fee:</span>
                                        <span class="fw-bold payment-service-fee">@money(0)</span>
                                    </div>
                                    <div class="payment-service-fee-breakdowns mt-2"></div>
                                </div>
                                <div class="payment-discount-section d-none border-bottom pb-3 mb-3">
                                    <div class="payment-discount-lines d-flex justify-content-between small">
                                        <span>Discount:</span>
                                        <span class="fw-bold text-success payment-discount">-@money(0)</span>
                                    </div>
                                    <div class="payment-discount-breakdowns mt-2"></div>
                                </div>
                                <div class="payment-tax-section d-none border-bottom pb-3 mb-3">
                                    <div class="payment-tax-row d-flex justify-content-between small">
                                        <span>Tax:</span>
                                        <span class="fw-bold payment-tax">@money(0)</span>
                                    </div>
                                    <div class="payment-tax-breakdowns mt-2"></div>
                                </div>
                                <div class="payment-gift-card-section d-none border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between small">
                                        <span class="payment-gift-card-name">Gift Card:</span>
                                        <span class="fw-bold text-success payment-gift-card-amount">-@money(0)</span>
                                    </div>
                                </div>
                                <div class="payment-reward-card-section d-none border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between small">
                                        <span class="payment-reward-card-name">Reward Card:</span>
                                        <span class="fw-bold text-primary payment-reward-card-points">0 points</span>
                                    </div>
                                </div>
                                <div class="payment-store-credit-section d-none border-bottom pb-3 mb-3">
                                    @include('employee.order.partials.store-credit-card', [
                                        'balanceLabel' => \App\Support\Currency::format(0),
                                        'balanceClass' => 'psc-balance payment-store-credit-balance',
                                    ])
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold mb-0">Balance Due:</h5>
                                    <h3 class="fw-bold text-primary mb-0 payment-balance-due">@money(0)</h3>
                                </div>
                            </div>

                            <div class="payment-items-list flex-grow-1"></div>

                            <div class="payment-actions-grid mt-4">
                                <button type="button" class="payment-utility-btn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDiscountCards">
                                    <i class="ti tabler-ticket"></i>
                                    <span>Discount Cards</span>
                                </button>
                                <button type="button" class="payment-utility-btn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasGiftCards">
                                    <i class="ti tabler-gift"></i>
                                    <span>Gift Cards</span>
                                </button>
                                <button type="button" class="payment-utility-btn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRewardCards">
                                    <i class="ti tabler-trophy"></i>
                                    <span>Reward Cards</span>
                                </button>
                                <button type="button" class="payment-utility-btn btn-email-receipt" data-bs-toggle="modal" data-bs-target="#emailReceiptModal">
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

                            <div class="payment-amount-display mb-3">@currency</div>
                            <div class="d-flex justify-content-between small mb-4">
                                <span>Remaining: <strong class="payment-remaining">@money(0)</strong></span>
                                <span>Change: <strong class="payment-change-due">@money(0)</strong></span>
                            </div>

                            <div class="payment-keypad mt-auto">
                                <button type="button" class="payment-key" data-payment-key="7">7</button>
                                <button type="button" class="payment-key" data-payment-key="8">8</button>
                                <button type="button" class="payment-key" data-payment-key="9">9</button>
                                <button type="button" class="payment-key" data-payment-quick="10">{{ \App\Support\Currency::symbol() }}10</button>
                                <button type="button" class="payment-key" data-payment-key="4">4</button>
                                <button type="button" class="payment-key" data-payment-key="5">5</button>
                                <button type="button" class="payment-key" data-payment-key="6">6</button>
                                <button type="button" class="payment-key" data-payment-quick="20">{{ \App\Support\Currency::symbol() }}20</button>
                                <button type="button" class="payment-key" data-payment-key="1">1</button>
                                <button type="button" class="payment-key" data-payment-key="2">2</button>
                                <button type="button" class="payment-key" data-payment-key="3">3</button>
                                <button type="button" class="payment-key" data-payment-quick="50">{{ \App\Support\Currency::symbol() }}50</button>
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
    @include('employee.cards.partials.create-modals', [
        'products' => $cardFormProducts ?? collect(),
        'currencySymbol' => $currencySymbol ?? \App\Support\Currency::symbol(),
    ])
    @include('tenant.ecommerce.customers.partials.save-modal')
    @if($vehicleRequired)
        @include('tenant.ecommerce.vehicles.partials.save-modal')
    @endif

    <div class="modal fade" id="draftShareModal" tabindex="-1" aria-labelledby="draftShareModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-start">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="draftShareModalLabel">Share Estimate PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="draft-share-form">
                    <div class="modal-body">
                        <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center" role="alert">
                            <i class="ti tabler-info-circle me-2 fs-5"></i>
                            <span>This saves the current cart as an estimate, then emails the PDF.</span>
                        </div>
                        <label for="draft_share_email" class="form-label fw-bold">Recipient Email <span class="text-danger">*</span></label>
                        <input type="email" id="draft_share_email" name="email" class="form-control" required placeholder="name@example.com">
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold btn-submit-draft-share">Send PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="emailReceiptModal" tabindex="-1" aria-labelledby="emailReceiptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-start border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="emailReceiptModalLabel">Email Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="email-receipt-form">
                    <div class="modal-body pt-3">
                        <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center" role="alert">
                            <i class="ti tabler-info-circle me-2 fs-5"></i>
                            <span>After checkout, the invoice PDF will be emailed to this address.</span>
                        </div>
                        <label for="email_receipt_email" class="form-label fw-bold">Recipient Email <span class="text-danger">*</span></label>
                        <input type="email" id="email_receipt_email" name="email" class="form-control" required placeholder="name@example.com" autocomplete="email">
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-label-secondary btn-clear-email-receipt">Don't Email</button>
                        <button type="submit" class="btn btn-primary fw-bold btn-save-email-receipt">Save Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(! empty($invoiceMode))
    <div class="modal fade" id="invoiceSaveSendModal" tabindex="-1" aria-labelledby="invoiceSaveSendModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-start">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="invoiceSaveSendModalLabel">Save &amp; Send Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="invoice-save-send-form">
                    <div class="modal-body">
                        <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center" role="alert">
                            <i class="ti tabler-info-circle me-2 fs-5"></i>
                            <span>This saves the invoice, then emails the PDF. You can edit the recipient address before sending.</span>
                        </div>
                        <label for="invoice_save_send_email" class="form-label fw-bold">
                            Recipient Email <span class="text-danger">*</span>
                        </label>
                        <input
                            type="email"
                            id="invoice_save_send_email"
                            name="email"
                            class="form-control"
                            required
                            placeholder="name@example.com"
                            autocomplete="email">
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold btn-submit-invoice-save-send">Save &amp; Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('page-script')
    <script>
        window.catalogRoutes = {
            categories: @json(route($orderRoutes['categories'])),
            subCategories: @json(route($orderRoutes['sub_categories'])),
            products: @json(route($orderRoutes['products'])),
            search: @json(route($orderRoutes['search'])),
            save: @json(route($orderRoutes['save'])),
            cartShow: @json(route($orderRoutes['cart_show'])),
            cartSave: @json(route($orderRoutes['cart_save'])),
            cartDestroy: @json(route($orderRoutes['cart_destroy'])),
            show: @json(route($orderRoutes['show'], ['order' => '__ORDER_ID__'])),
            print: @json(route($orderRoutes['print'], ['order' => '__ORDER_ID__'])),
            pdf: @json(route($orderRoutes['pdf'], ['order' => '__ORDER_ID__'])),
            share: @json(route($orderRoutes['share'], ['order' => '__ORDER_ID__'])),
            dropdownCustomers: @json(route('tenant.ecommerce.dropdowns.customers')),
            dropdownVehicles: @json(route('tenant.ecommerce.dropdowns.vehicles')),
            dropdownServices: @json(route('tenant.ecommerce.dropdowns.services')),
        };
        window.orderSettings = {
            vehicleRequired: @json($vehicleRequired),
            returnDaysAfterPurchase: @json($returnDaysAfterPurchase),
            creditMinRedeemBalance: @json((float) ($creditMinRedeemBalance ?? 50)),
            invoiceMode: @json(!empty($invoiceMode)),
            invoicesIndexUrl: @json(route($orderRoutes['invoices_index'])),
        };
        window.editOrder = @json($editOrder ?? null);
        window.employeeCards = {
            currencySymbol: @json($currencySymbol ?? \App\Support\Currency::symbol()),
            context: 'pos',
        };
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.20.0/jquery.validate.min.js"></script>
    <script src="{{ asset('assets/js/tenant/e-com/customer-manager.js') }}?v={{ filemtime(public_path('assets/js/tenant/e-com/customer-manager.js')) }}"></script>
    @if($vehicleRequired)
        <script
            src="{{ asset('assets/js/tenant/e-com/vehicle-manager.js') }}?v={{ filemtime(public_path('assets/js/tenant/e-com/vehicle-manager.js')) }}">
        </script>
    @endif

    @can('create', \App\Models\Card::class)
        <script src="{{ asset('assets/js/cards-form.js') }}?v={{ filemtime(public_path('assets/js/cards-form.js')) }}"></script>
        <script src="{{ asset('assets/js/employee/cards.js') }}?v={{ filemtime(public_path('assets/js/employee/cards.js')) }}"></script>
    @endcan

    <script src="{{ asset('assets/js/employee/catalog-api.js') }}"></script>
    <script
        src="{{ asset('assets/js/employee/new-order.js') }}?v={{ filemtime(public_path('assets/js/employee/new-order.js')) }}">
    </script>
@endpush
