@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card bg-primary text-white position-relative overflow-hidden">
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <span class="badge bg-white text-primary mb-3">Central Control Tower</span>
                        <h2 class="text-white mb-2">Multi-tenant operations at a glance</h2>
                        <p class="mb-4 text-white-50">
                            Review new shop registrations, track approvals, and keep tenant activation flowing from one central workspace.
                        </p>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.shops.index') }}" class="btn btn-light btn-sm">Manage Shops</a>
                        </div>
                    </div>
                    <div class="col-lg-4 text-center d-none d-lg-block">
                        <img src="{{ asset('assets/img/illustrations/card-website-analytics-1.png') }}" alt="Admin dashboard" class="img-fluid" style="max-height: 200px;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <span class="badge bg-label-primary mb-3">Network</span>
                <span class="text-muted d-block mb-2">Total Shops</span>
                <h3 class="mb-0">{{ $stats['tenants_total'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <span class="badge bg-label-warning mb-3">Action Queue</span>
                <span class="text-muted d-block mb-2">Pending Approval</span>
                <h3 class="mb-0 text-warning">{{ $stats['tenants_pending'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <span class="badge bg-label-success mb-3">Live Tenants</span>
                <span class="text-muted d-block mb-2">Approved Shops</span>
                <h3 class="mb-0 text-success">{{ $stats['tenants_approved'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <span class="badge bg-label-info mb-3">Operators</span>
                <span class="text-muted d-block mb-2">Tenant Admins</span>
                <h3 class="mb-0">{{ $stats['tenant_admins'] }}</h3>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-1">Approval Snapshot</h5>
                <p class="text-muted mb-0">Current shop activation mix</p>
            </div>
            <div class="card-body">
                @php
                    $approvedPercent = $stats['tenants_total'] > 0 ? round(($stats['tenants_approved'] / $stats['tenants_total']) * 100) : 0;
                    $pendingPercent = $stats['tenants_total'] > 0 ? round(($stats['tenants_pending'] / $stats['tenants_total']) * 100) : 0;
                @endphp
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-medium">Approved</span>
                        <span>{{ $approvedPercent }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $approvedPercent }}%"></div>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-medium">Pending</span>
                        <span>{{ $pendingPercent }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" style="width: {{ $pendingPercent }}%"></div>
                    </div>
                </div>
                <div class="rounded bg-label-primary p-3">
                    <div class="fw-medium mb-1">Recommended next step</div>
                    <p class="mb-0 text-muted">Prioritize pending tenants to reduce onboarding delay and increase active shop conversion.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Recent Shop Registrations</h5>
                    <p class="text-muted mb-0">Latest tenants entering the approval pipeline</p>
                </div>
                <a href="{{ route('admin.shops.index') }}" class="btn btn-primary btn-sm">View All Shops</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Shop</th>
                            <th>Owner</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTenants as $tenant)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $tenant->display_name }}</div>
                                    <small class="text-muted">{{ $tenant->city }}, {{ $tenant->country }}</small>
                                </td>
                                <td>{{ $tenant->owner_name }}</td>
                                <td>{{ $tenant->owner_email_address }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $tenant->status->badgeClass() }}">
                                        {{ ucfirst($tenant->status->value) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No shop registrations found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-1">Operating Principles</h5>
                <p class="text-muted mb-0">Keep tenant onboarding safe, fast, and auditable.</p>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-medium mb-2">Approve deliberately</div>
                            <p class="text-muted mb-0">Each approved shop becomes an active tenant workspace with single-database isolation enforced through tenant-aware application logic.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-medium mb-2">Keep identity clean</div>
                            <p class="text-muted mb-0">Central super admins stay separate from tenant operators, with verification and activity checks enforced.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-medium mb-2">Onboard with confidence</div>
                            <p class="text-muted mb-0">The seeded demo shop gives you a ready tenant account to test approval, login, and tenancy routing end to end.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
