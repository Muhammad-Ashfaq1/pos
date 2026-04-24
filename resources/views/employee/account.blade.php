@extends('layouts.app')

@section('title', 'Employee Account')

@section('content')
<div class="row g-4">
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <span class="avatar avatar-xl bg-label-primary mb-3">
                    <span class="fw-semibold fs-3">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                </span>
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-3">{{ $user->email }}</p>
                <span class="badge bg-label-primary">Employee</span>
                <div class="mt-4 text-start">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Workspace</span>
                        <span class="fw-semibold">{{ $tenant?->display_name ?? 'Tenant Workspace' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Team Members</span>
                        <span class="fw-semibold">{{ $stats['team_members'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Last Login</span>
                        <span class="fw-semibold">{{ $user->last_login_at?->format('d M Y h:i A') ?? 'First recorded session' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Access Summary</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    This employee account is intentionally restricted to operational screens and view-safe tenant modules. Admin settings, role management, reports, and other dangerous areas remain unavailable unless permissions are changed.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($permissionBadges as $badge)
                        <span class="badge bg-label-primary">{{ $badge }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Workspace Safety</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="rounded bg-label-success p-3 h-100">
                            <div class="fw-semibold mb-1">Tenant Scoped</div>
                            <small class="text-muted">Customer, vehicle, product, and service data is read only within the current tenant context.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="rounded bg-label-warning p-3 h-100">
                            <div class="fw-semibold mb-1">No Admin Settings</div>
                            <small class="text-muted">This panel excludes shop settings, role management, discounts management, and central administration.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="rounded bg-label-info p-3 h-100">
                            <div class="fw-semibold mb-1">Shared Login, Separate Panel</div>
                            <small class="text-muted">Employees still use the shared login screen, but successful sign-in routes them into the dedicated employee experience.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="rounded bg-label-primary p-3 h-100">
                            <div class="fw-semibold mb-1">Future POS Ready</div>
                            <small class="text-muted">The workspace shell is now prepared for future order and service-job implementation without affecting current modules.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
