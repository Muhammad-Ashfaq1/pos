@extends('layouts.customer-portal')

@section('title', 'Order')

@section('content')
    <div class="cp-page-heading" id="cp-order-show">
        <div class="cp-page-heading-main">
            <a href="{{ route('customer.orders') }}" class="cp-back-btn" aria-label="Back to history">
                <i class="ti tabler-arrow-left"></i>
            </a>
            <div>
                <h1 class="cp-page-title" id="cp-order-title">Order</h1>
                <p class="cp-page-subtitle" id="cp-order-subtitle"></p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="#" id="cp-order-pdf" class="btn btn-sm btn-primary fw-semibold d-none">
                <i class="ti tabler-download me-1"></i>Download PDF
            </a>
            <span class="badge bg-label-secondary" id="cp-order-status"></span>
        </div>
    </div>

    <div class="cp-panel">
        <div class="cp-panel-body" id="cp-order-body">
            <div class="cp-list-empty">
                <div class="spinner-border text-primary mb-2" role="status"></div>
                <p class="mb-0">Loading...</p>
            </div>
        </div>
    </div>
@endsection

@push('page-script')
    <script>
        window.customerOrderId = {{ (int) $orderId }};
    </script>
    <script src="{{ asset('assets/js/customer/order-show.js') }}?v={{ filemtime(public_path('assets/js/customer/order-show.js')) }}"></script>
@endpush
