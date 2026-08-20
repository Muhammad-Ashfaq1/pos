@extends('layouts.customer-portal')

@section('title', 'Service History')

@section('content')
    <div class="cp-page-heading">
        <div class="cp-page-heading-main">
            <div>
                <h1 class="cp-page-title">Service History</h1>
                <p class="cp-page-subtitle">Past visits and invoices</p>
            </div>
        </div>
    </div>

    <div class="cp-panel">
        <div class="cp-list" id="cp-orders-list">
            <div class="cp-list-empty">
                <div class="spinner-border text-primary mb-2" role="status"></div>
                <p class="mb-0">Loading...</p>
            </div>
        </div>
        <div class="cp-panel-footer d-none" id="cp-orders-pagination"></div>
    </div>
@endsection

@push('page-script')
    <script src="{{ asset('assets/js/customer/orders.js') }}?v={{ filemtime(public_path('assets/js/customer/orders.js')) }}"></script>
@endpush
