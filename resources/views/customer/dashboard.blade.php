@extends('layouts.customer-portal')

@section('title', 'Overview')

@section('content')
    <div class="cp-page-heading">
        <div class="cp-page-heading-main">
            <div>
                <h1 class="cp-page-title">Overview</h1>
                <p class="cp-page-subtitle">Your visits and store credit at a glance</p>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="cp-hero h-100">
                <div class="cp-hero-label"><i class="ti tabler-wallet"></i><span>Store Credit Balance</span></div>
                <div class="cp-hero-value" id="cp-dashboard-credit">—</div>
                <div class="cp-hero-meta" id="cp-dashboard-shop"></div>
                <div id="cp-dashboard-credit-meta"></div>
                <a href="{{ route('customer.credits') }}" class="cp-hero-link">
                    View credit activity <i class="ti tabler-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="row g-3 h-100">
                <div class="col-sm-6">
                    <div class="cp-stat">
                        <div class="cp-stat-icon cp-stat-icon--indigo"><i class="ti tabler-calendar-check"></i></div>
                        <div>
                            <div class="cp-stat-label">Total Visits</div>
                            <div class="cp-stat-value" id="cp-dashboard-visits">—</div>
                            <div class="cp-stat-meta">Completed service visits</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="cp-stat">
                        <div class="cp-stat-icon cp-stat-icon--amber"><i class="ti tabler-receipt-dollar"></i></div>
                        <div>
                            <div class="cp-stat-label">Lifetime Spend</div>
                            <div class="cp-stat-value" id="cp-dashboard-lifetime">—</div>
                            <div class="cp-stat-meta">Across all paid visits</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cp-quick-actions mb-4">
        <a href="{{ route('customer.orders') }}" class="cp-quick-action">
            <span class="cp-quick-action-icon"><i class="ti tabler-history"></i></span>
            <span>
                <strong>Service History</strong>
                <small>View past orders and invoices</small>
            </span>
            <i class="ti tabler-chevron-right cp-quick-action-arrow"></i>
        </a>
        <a href="{{ route('customer.vehicles') }}" class="cp-quick-action">
            <span class="cp-quick-action-icon"><i class="ti tabler-car"></i></span>
            <span>
                <strong>My Vehicles</strong>
                <small>See vehicles on file</small>
            </span>
            <i class="ti tabler-chevron-right cp-quick-action-arrow"></i>
        </a>
        <a href="{{ route('customer.credits') }}" class="cp-quick-action">
            <span class="cp-quick-action-icon"><i class="ti tabler-wallet"></i></span>
            <span>
                <strong>Store Credit</strong>
                <small>Balance and credit history</small>
            </span>
            <i class="ti tabler-chevron-right cp-quick-action-arrow"></i>
        </a>
    </div>

    <div class="cp-panel">
        <div class="cp-panel-header">
            <div>
                <h2 class="cp-panel-title">Recent Visits</h2>
                <p class="cp-panel-subtitle">Your latest service activity</p>
            </div>
            <a class="btn btn-sm btn-link fw-semibold" href="{{ route('customer.orders') }}">View all</a>
        </div>
        <div class="cp-list" id="cp-dashboard-recent">
            <div class="cp-list-empty">
                <div class="spinner-border text-primary mb-2" role="status"></div>
                <p class="mb-0">Loading...</p>
            </div>
        </div>
    </div>
@endsection

@push('page-script')
    <script src="{{ asset('assets/js/customer/dashboard.js') }}?v={{ filemtime(public_path('assets/js/customer/dashboard.js')) }}"></script>
@endpush
