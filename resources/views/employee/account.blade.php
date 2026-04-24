@extends('layouts.employee')

@section('title', 'Employee Account')
@section('content_container_class', 'container-fluid flex-grow-1 container-p-y')

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
                <span class="badge bg-label-primary">{{ str($user->primaryRoleName() ?? 'employee')->replace('_', ' ')->title() }}</span>

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
                    This employee account stays inside the worker-facing panel and view-safe tenant modules. Shop settings, staff management, roles, reports, discounts management, and other admin areas remain outside this flow.
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
                            <small class="text-muted">Customer, vehicle, product, and service data is constrained by the active tenant context and model policies.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="rounded bg-label-warning p-3 h-100">
                            <div class="fw-semibold mb-1">No Admin Settings</div>
                            <small class="text-muted">The employee flow excludes shop settings, role management, discounts management, and central admin tools.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="rounded bg-label-info p-3 h-100">
                            <div class="fw-semibold mb-1">Shared Login, Separate Panel</div>
                            <small class="text-muted">Employees still sign in through the shared login screen, but successful authentication sends them into the dedicated employee panel.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="rounded bg-label-primary p-3 h-100">
                            <div class="fw-semibold mb-1">POS Ready Shell</div>
                            <small class="text-muted">The workspace is structured so future POS, order, or service-job modules can plug in without reshaping the employee UX.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
