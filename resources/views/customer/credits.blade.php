@extends('layouts.customer-portal')

@section('title', 'Store Credit')

@section('content')
    <div class="cp-page-heading">
        <div class="cp-page-heading-main">
            <div>
                <h1 class="cp-page-title">Store Credit</h1>
                <p class="cp-page-subtitle">Balance and ledger activity</p>
            </div>
        </div>
    </div>

    <div class="cp-hero mb-4">
        <div class="cp-hero-label"><i class="ti tabler-wallet"></i><span>Current Balance</span></div>
        <div class="cp-hero-value" id="cp-credits-balance">—</div>
        <div id="cp-credits-meta"></div>
    </div>

    <div class="cp-filters" id="cp-credits-filters">
        <a href="#" data-type="" class="btn btn-sm cp-filter btn-primary">All</a>
        <a href="#" data-type="earn" class="btn btn-sm cp-filter btn-outline-secondary">Earned</a>
        <a href="#" data-type="redeem" class="btn btn-sm cp-filter btn-outline-secondary">Redeemed</a>
        <a href="#" data-type="adjust" class="btn btn-sm cp-filter btn-outline-secondary">Adjusted</a>
        <a href="#" data-type="expire" class="btn btn-sm cp-filter btn-outline-secondary">Expired</a>
    </div>

    <div class="cp-panel">
        <div class="cp-panel-header">
            <h2 class="cp-panel-title">Credit History</h2>
        </div>
        <div class="cp-list" id="cp-credits-list">
            <div class="cp-list-empty">
                <div class="spinner-border text-primary mb-2" role="status"></div>
                <p class="mb-0">Loading...</p>
            </div>
        </div>
        <div class="cp-panel-footer d-none" id="cp-credits-pagination"></div>
    </div>
@endsection

@push('page-script')
    <script src="{{ asset('assets/js/customer/credits.js') }}?v={{ filemtime(public_path('assets/js/customer/credits.js')) }}"></script>
@endpush
