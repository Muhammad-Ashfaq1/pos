@extends('layouts.app')

@section('title', 'Tenant Dashboard')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card bg-primary text-white overflow-hidden">
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <span class="badge bg-white text-primary mb-3">Tenant Workspace</span>
                        <h2 class="text-white mb-2">{{ $tenant?->shop_name ?? 'Shop Dashboard' }}</h2>
                        <p class="mb-0 text-white-50">
                            Your shop is signed in under its isolated tenant context. This is the starting point for POS, services, customers, and vehicles.
                        </p>
                    </div>
                    <div class="col-lg-4 text-center d-none d-lg-block">
                        <img src="{{ asset('assets/img/illustrations/girl-with-laptop-light.png') }}" alt="Tenant dashboard" class="img-fluid" style="max-height: 200px;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card">
            <div class="card-body">
                <span class="text-muted d-block mb-2">Shop</span>
                <h4 class="mb-0">{{ $tenant?->shop_name ?? 'Shop Dashboard' }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card">
            <div class="card-body">
                <span class="text-muted d-block mb-2">Approval Status</span>
                <h4 class="mb-0 text-capitalize">{{ $stats['status'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card">
            <div class="card-body">
                <span class="text-muted d-block mb-2">Team Members</span>
                <h4 class="mb-0">{{ $stats['team_members'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="mb-3">Workspace Readiness</h5>
                <p class="text-muted">
                    Your tenant session is active and isolated. Current onboarding status:
                    <span class="fw-semibold text-capitalize">{{ $stats['onboarding_status'] }}</span>.
                </p>
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <div class="rounded bg-label-primary p-3 h-100">
                            <div class="fw-medium mb-1">Customers & Vehicles</div>
                            <small class="text-muted">Next step: add tenant-scoped customer and vehicle modules using the new tenant concern.</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="rounded bg-label-success p-3 h-100">
                            <div class="fw-medium mb-1">POS Readiness</div>
                            <small class="text-muted">Service catalog, product inventory, and billing are ready to be built on top of this tenant shell.</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="rounded bg-label-warning p-3 h-100">
                            <div class="fw-medium mb-1">Security</div>
                            <small class="text-muted">Verified, approved, active tenant users are now protected before they can access this workspace.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
