@extends('layouts.customer-portal')

@section('title', 'My Account')

@section('content')
<nav class="portal-navbar py-3 mb-4">
    <div class="container d-flex align-items-center justify-content-between">
        <span class="portal-brand">OIL<span>POS</span></span>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small d-none d-sm-inline" id="nav-customer-name"></span>
            <button class="btn btn-sm btn-outline-secondary" id="logout-btn"><i class="ti tabler-logout me-1"></i>Sign out</button>
        </div>
    </div>
</nav>

<div class="container pb-5" data-app-shell hidden>
    <ul class="nav nav-pills mb-4 gap-2" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-section="dashboard">Overview</button></li>
        <li class="nav-item"><button class="nav-link" data-section="orders">Service History</button></li>
        <li class="nav-item"><button class="nav-link" data-section="credits">Store Credit</button></li>
        <li class="nav-item"><button class="nav-link" data-section="profile">Profile</button></li>
    </ul>

    {{-- DASHBOARD --}}
    <section data-pane="dashboard">
        <div class="row g-4 mb-4">
            <div class="col-md-5">
                <div class="credit-hero p-4 h-100">
                    <div class="d-flex align-items-center gap-2 mb-2 opacity-75"><i class="ti tabler-wallet"></i><span>Store Credit Balance</span></div>
                    <div class="display-5 fw-bold" id="hero-balance">—</div>
                    <div class="small opacity-75 mt-2" id="hero-shop"></div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="row g-3 h-100">
                    <div class="col-6"><div class="card h-100 border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Total Visits</div><div class="h3 fw-bold mb-0" id="stat-visits">—</div></div></div></div>
                    <div class="col-6"><div class="card h-100 border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Lifetime Spend</div><div class="h3 fw-bold mb-0" id="stat-lifetime">—</div></div></div></div>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Recent Visits</h6>
                <button class="btn btn-sm btn-link" data-section="orders">View all</button>
            </div>
            <div class="list-group list-group-flush" id="recent-orders"></div>
        </div>
    </section>

    {{-- ORDERS --}}
    <section data-pane="orders" hidden>
        <div class="card border-0 shadow-sm">
            <div class="card-header"><h6 class="mb-0 fw-bold">Service History</h6></div>
            <div class="list-group list-group-flush" id="orders-list"></div>
            <div class="card-footer text-center d-none" id="orders-more-wrap">
                <button class="btn btn-sm btn-outline-primary" id="orders-more">Load more</button>
            </div>
        </div>
    </section>

    {{-- CREDITS --}}
    <section data-pane="credits" hidden>
        <div class="credit-hero p-4 mb-4">
            <div class="d-flex align-items-center gap-2 mb-2 opacity-75"><i class="ti tabler-wallet"></i><span>Current Balance</span></div>
            <div class="display-5 fw-bold" id="credits-balance">—</div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header"><h6 class="mb-0 fw-bold">Credit History</h6></div>
            <div class="list-group list-group-flush" id="credits-list"></div>
        </div>
    </section>

    {{-- PROFILE --}}
    <section data-pane="profile" hidden>
        <div class="card border-0 shadow-sm" style="max-width:560px;">
            <div class="card-header"><h6 class="mb-0 fw-bold">My Profile</h6></div>
            <div class="card-body">
                <form id="profile-form">
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" id="profile-email" disabled></div>
                    <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
                    <button type="submit" class="btn btn-primary fw-bold">Save changes</button>
                </form>
            </div>
        </div>
    </section>
</div>

{{-- Order detail modal --}}
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="order-modal-title">Order</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="order-modal-body"></div>
        </div>
    </div>
</div>
@endsection
