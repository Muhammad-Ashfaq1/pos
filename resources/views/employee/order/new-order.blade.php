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
                            <h5 class="fw-bold text-primary mb-1">Customer</h5>
                            <select class="form-select">
                                <option selected>Select Customer</option>
                            </select>
                        </div>

                        <select class="form-select mb-3">
                            <option selected>Orders</option>
                        </select>

                        <select class="form-select mb-3">
                            <option selected>Add Vehicle</option>
                        </select>

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
                    <div class="categories-view">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="fw-bold">Categories</h3>
                            <div class="input-group w-50">
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text" id="basic-addon-search31"><i
                                            class="icon-base ti tabler-search"></i></span>
                                    <input type="text" class="form-control"
                                        placeholder="Search Categories, Sub Categories, Products and Others..."
                                        aria-label="Search Categories, Sub Categories and Others"
                                        aria-describedby="basic-addon-search31" />
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 overflow-auto" style="max-height: 100vh;">
                            <div class="col-md-4">
                                <div
                                    class="card border-0 rounded-4 text-center p-5 justify-content-center align-items-center category-card bg-label-primary">
                                    <h4 class="text-primary fw-bold mb-0">Fuel</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div
                                    class="card border-0 rounded-4 text-center p-5 justify-content-center align-items-center category-card bg-label-primary">
                                    <h4 class="text-primary fw-bold mb-0">Fuel</h4>
                                </div>
                            </div>
                            @for ($i = 0 ; $i < 10 ; $i++)
                            <div class="col-md-4">
                                <div
                                    class="card border-0 rounded-4 text-center p-5 justify-content-center align-items-center category-card bg-label-primary">
                                    <h4 class="text-primary fw-bold mb-0">Fuel</h4>
                                </div>
                            </div>
                            @endfor
                            <div class="col-md-4">
                                <div
                                    class="card border-0 rounded-4 text-center p-5 justify-content-center align-items-center category-card bg-label-primary">
                                    <h4 class="text-primary fw-bold mb-0">Fuel</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="product-details-view d-none">
                        <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                            <button
                                class="btn btn-sm bg-label-secondary text-primary border-0 rounded-pill btn-circle-38 btn-back-to-categories">
                                <i class="ti tabler-arrow-left fs-4"></i>
                            </button>
                            <h4 class="fw-bold mb-0 category-title">Gasoline</h4>
                        </div>
                        <div class="product-info  mt-4">
                            <h3 class="fw-bold product-name mb-4">Gasoline</h3>
                            <p class="text-muted mb-4 fs-5">Barcode: <span class="product-barcode text-dark fw-semibold">EreTVgAmKe</span></p>
                            <h5 class="fw-bold mb-4 fs-4"><span class="text-dark">1 Unit:</span> <span
                                    class="text-muted ms-1 product-price">$125.000</span>
                            </h5>
                            <div class="mb-4">
                                <label class="form-label fw-bold fs-6">How many units <span
                                        class="text-danger">*</span></label>
                                <input type="number" min="1"
                                    class="form-control form-control-lg border-primary rounded-3 py-3 product-qty-input"
                                    placeholder="Enter units" value="1">
                            </div>
                            <div class="d-flex justify-content-between mt-5">
                                <button
                                    class="btn bg-label-secondary text-primary border-0 px-5 py-3 rounded-3 fw-bold btn-clear">Clear</button>
                                <button class="btn btn-primary px-5 py-3 rounded-3 fw-bold btn-add-to-cart">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('employee.order.sidebar-modal');
@push('page-script')
    <script src="{{ asset('assets/js/pos.js') }}"></script>
@endpush
@endsection
