@extends('layouts.app')

@section('title', 'Employee Dashboard')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card bg-primary text-white overflow-hidden">
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="badge bg-white text-primary mb-3">Employee Panel</span>
                        <h2 class="text-white mb-2">Welcome back, {{ $user->name }}</h2>
                        <p class="mb-0 text-white-50">
                            You are working inside {{ $tenant?->display_name ?? 'your tenant workspace' }} with employee-safe access and tenant-scoped data only.
                        </p>
                    </div>
                    <div class="col-lg-4 text-center d-none d-lg-block">
                        <img src="{{ asset('assets/img/illustrations/boy-with-laptop-light.png') }}" alt="Employee dashboard" class="img-fluid" style="max-height: 200px;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $summaryCards = collect([
            $stats['services_total'] !== null ? ['label' => 'Active Service Catalog', 'value' => $stats['services_total'], 'icon' => 'tabler-tool', 'badge' => 'Services'] : null,
            $stats['technician_services'] !== null ? ['label' => 'Technician Required', 'value' => $stats['technician_services'], 'icon' => 'tabler-user-cog', 'badge' => 'Work Mix'] : null,
            $stats['customers_total'] !== null ? ['label' => 'Customers Available', 'value' => $stats['customers_total'], 'icon' => 'tabler-users', 'badge' => 'Profiles'] : null,
            $stats['vehicles_total'] !== null ? ['label' => 'Vehicles In Workspace', 'value' => $stats['vehicles_total'], 'icon' => 'tabler-car', 'badge' => 'Fleet'] : null,
            $stats['products_total'] !== null ? ['label' => 'Products In Catalog', 'value' => $stats['products_total'], 'icon' => 'tabler-package', 'badge' => 'Catalog'] : null,
            $stats['low_stock_products'] !== null ? ['label' => 'Low Stock Watch', 'value' => $stats['low_stock_products'], 'icon' => 'tabler-alert-triangle', 'badge' => 'Inventory'] : null,
        ])->filter()->values();
    @endphp

    @foreach($summaryCards as $card)
        <div class="col-xl-4 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-label-primary">{{ $card['badge'] }}</span>
                        <span class="avatar avatar-sm bg-label-primary">
                            <i class="ti {{ $card['icon'] }}"></i>
                        </span>
                    </div>
                    <span class="text-muted d-block mb-1">{{ $card['label'] }}</span>
                    <h3 class="mb-0">{{ $card['value'] }}</h3>
                </div>
            </div>
        </div>
    @endforeach

    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Quick Actions</h5>
                    <small class="text-muted">Only modules allowed by your employee permissions are shown.</small>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @forelse($quickActions as $action)
                        <div class="col-md-6">
                            <a href="{{ $action['route'] }}" class="card shadow-none border h-100 text-decoration-none">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between gap-3">
                                        <div>
                                            <h6 class="mb-1 text-body">{{ $action['label'] }}</h6>
                                            <p class="mb-0 text-muted">{{ $action['description'] }}</p>
                                        </div>
                                        <span class="avatar bg-label-{{ $action['color'] }}">
                                            <i class="ti {{ $action['icon'] }}"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-primary mb-0">
                                This employee account does not currently have any module access beyond the dashboard.
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Profile & Workspace</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="avatar avatar-lg bg-label-primary">
                        <span class="fw-semibold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </span>
                    <div>
                        <h6 class="mb-1">{{ $user->name }}</h6>
                        <p class="mb-0 text-muted">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="d-flex flex-column gap-3">
                    <div class="rounded bg-label-primary p-3">
                        <small class="text-muted d-block mb-1">Role</small>
                        <div class="fw-semibold">Employee</div>
                    </div>
                    <div class="rounded bg-label-success p-3">
                        <small class="text-muted d-block mb-1">Workspace</small>
                        <div class="fw-semibold">{{ $tenant?->display_name ?? 'Tenant Workspace' }}</div>
                    </div>
                    <div class="rounded bg-label-warning p-3">
                        <small class="text-muted d-block mb-1">Team Members</small>
                        <div class="fw-semibold">{{ $stats['team_members'] }}</div>
                    </div>
                    <div class="rounded bg-label-info p-3">
                        <small class="text-muted d-block mb-1">Last Login</small>
                        <div class="fw-semibold">{{ $user->last_login_at?->format('d M Y h:i A') ?? 'First recorded session' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach($placeholderCards as $card)
        <div class="col-xl-4 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="mb-0">{{ $card['title'] }}</h6>
                        <span class="avatar avatar-sm bg-label-secondary">
                            <i class="ti {{ $card['icon'] }}"></i>
                        </span>
                    </div>
                    <span class="badge bg-label-secondary mb-3">{{ $card['badge'] }}</span>
                    <p class="text-muted mb-0">{{ $card['description'] }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
