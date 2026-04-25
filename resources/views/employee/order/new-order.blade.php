@extends('layouts.employee-portal')

@section('title', 'Create New Order')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}" />
@endpush

@section('content')
<div class="container-fluid p-4">
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

                    <div class="row g-0 border-bottom pb-2 mb-2 px-1">
                        <div class="col-7 small fw-bold">Items</div>
                        <div class="col-2 small fw-bold text-center">Qty</div>
                        <div class="col-3 small fw-bold text-end">Price</div>
                    </div>

                    <div class="flex-grow-1 d-flex align-items-center justify-content-center">
                        <p class="text-muted fw-bold">No Items Added</p>
                    </div>

                    <div class="mt-auto border-top pt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-bold small">Items :</span>
                            <span class="fw-bold">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Total (without tax)</span>
                            <span class="fw-bold small">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <h5 class="fw-bold">Final Total</h5>
                            <h5 class="fw-bold text-primary">$0.00</h5>
                        </div>

                        <div class="row g-2 mb-3 align-items-stretch">
                            <div class="col-6">
                                <button class="btn btn-outline-danger w-100 h-100 fw-bold btn-cancel-order d-flex flex-column align-items-center cursor-pointer justify-content-center py-2">
                                    <span class="fs-5">Cancel</span>
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-primary w-100 h-100 fw-bold d-flex flex-column align-items-center cursor-pointer justify-content-center py-2" disabled>
                                    <div class="fs-5 text-warning">$0.00</div>
                                    <div class="small fw-semibold text-warning">Pay</div>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <div class="text-primary cursor-pointer d-flex flex-column align-items-center">
                                <i class="icon-base ti tabler-percentage fs-3 mb-1"></i>
                                <small class="fw-bold">Discount Order</small>
                            </div>
                            <div class="text-primary cursor-pointer d-flex flex-column align-items-center">
                                <i class="icon-base ti tabler-settings fs-3 mb-1"></i>
                                <small class="fw-bold">Service Fee</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold">Categories</h3>
                    <div class="input-group w-50">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text" id="basic-addon-search31"><i class="icon-base ti tabler-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search Categories, Sub Categories, Products and Others..." aria-label="Search Categories, Sub Categories and Others"
                                aria-describedby="basic-addon-search31" />
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-0 rounded-4 text-center p-5 justify-content-center align-items-center category-card">
                            <h4 class="text-primary fw-bold mb-0">Fuel</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 rounded-4 text-center p-5 justify-content-center align-items-center category-card">
                            <h4 class="text-primary fw-bold mb-0">Fuel</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 rounded-4 text-center p-5 justify-content-center align-items-center category-card">
                            <h4 class="text-primary fw-bold mb-0">Fuel</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
