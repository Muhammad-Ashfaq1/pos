@extends('layouts.app')

@section('title', 'Admin Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pos-glass.css') }}?v={{ filemtime(public_path('assets/css/pos-glass.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/admin-dashboard.css') }}?v={{ filemtime(public_path('assets/css/admin-dashboard.css')) }}" />
@endpush

@section('content')
<div class="row g-4 pos-ad">
    <div class="col-12">
        <div class="pos-glass-card pos-tone-primary">
            <div class="pos-glass-intro">
                <div class="pos-glass-intro-copy">
                    <h4 class="pos-glass-intro-title">Multi-tenant operations at a glance</h4>
                    <p class="pos-glass-intro-subtitle">
                        Review new shop registrations, track approvals, and keep tenant activation flowing from one central workspace.
                    </p>
                </div>
                <div class="pos-glass-intro-actions d-flex flex-wrap gap-2 align-items-center">
                    <a href="{{ route('admin.shops.index') }}" class="btn btn-sm btn-primary">Manage Shops</a>
                    <a href="{{ route('admin.demo-requests.index') }}" class="btn btn-sm btn-label-secondary position-relative">
                        Demo Requests
                        @if ($stats['demo_requests_new'] > 0)
                            <span class="badge rounded-pill bg-warning text-dark ms-1">{{ $stats['demo_requests_new'] }} new</span>
                        @endif
                    </a>
                    <span class="pos-glass-pill pos-tone-info">
                        <i class="icon-base ti tabler-building-store" aria-hidden="true"></i>
                        {{ $stats['tenants_total'] }} shops
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="pos-glass-card pos-tone-primary h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-building-store" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Total Shops</h6>
                </div>
                <p class="pos-stat-value">{{ $stats['tenants_total'] }}</p>
                <p class="pos-stat-desc mb-0">Platform-wide registrations</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="pos-glass-card pos-tone-warning h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-clock" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Pending Approval</h6>
                </div>
                <p class="pos-stat-value">{{ $stats['tenants_pending'] }}</p>
                <p class="pos-stat-desc mb-0">Action needed — requires review</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="pos-glass-card pos-tone-success h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-circle-check" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Approved Shops</h6>
                </div>
                <p class="pos-stat-value">{{ $stats['tenants_approved'] }}</p>
                <p class="pos-stat-desc mb-0">
                    {{ $stats['tenants_total'] > 0 ? round(($stats['tenants_approved'] / $stats['tenants_total']) * 100) : 0 }}% conversion rate
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="pos-glass-card pos-tone-info h-100">
            <div class="pos-stat-body">
                <div class="pos-stat-head">
                    <span class="pos-stat-icon"><i class="icon-base ti tabler-users" aria-hidden="true"></i></span>
                    <h6 class="pos-stat-label">Tenant Admins</h6>
                </div>
                <p class="pos-stat-value">{{ $stats['tenant_admins'] }}</p>
                <p class="pos-stat-desc mb-0">Active system operators</p>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="pos-glass-card pos-tone-primary h-100 pos-ad-panel">
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
        <div class="pos-glass-card pos-tone-info h-100 pos-ad-panel">
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
        <div class="pos-glass-card pos-tone-secondary pos-ad-panel">
            <div class="card-header">
                <h5 class="card-title mb-1">Operating Principles</h5>
                <p class="text-muted mb-0">Keep tenant onboarding safe, fast, and auditable.</p>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="pos-glass-card pos-tone-primary h-100">
                            <div class="pos-stat-body">
                                <h6 class="pos-stat-label">Approve deliberately</h6>
                                <p class="pos-stat-desc mb-0">Each approved shop becomes an active tenant workspace with single-database isolation enforced through tenant-aware application logic.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="pos-glass-card pos-tone-info h-100">
                            <div class="pos-stat-body">
                                <h6 class="pos-stat-label">Keep identity clean</h6>
                                <p class="pos-stat-desc mb-0">Central super admins stay separate from tenant operators, with verification and activity checks enforced.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="pos-glass-card pos-tone-success h-100">
                            <div class="pos-stat-body">
                                <h6 class="pos-stat-label">Onboard with confidence</h6>
                                <p class="pos-stat-desc mb-0">The seeded demo shop gives you a ready tenant account to test approval, login, and tenancy routing end to end.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
