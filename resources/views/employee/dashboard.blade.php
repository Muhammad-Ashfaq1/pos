@extends('layouts.employee')

@section('title', 'Employee Dashboard')
@section('content_container_class', 'container-fluid flex-grow-1 container-p-y')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card employee-shell-hero border-0 text-white">
            <div class="card-body p-4 p-lg-5 position-relative">
                <div class="row g-4 align-items-center">
                    <div class="col-xl-8">
                        <span class="badge bg-white text-primary mb-3">Worker Dashboard</span>
                        <h2 class="text-white mb-2">{{ $stats['workspace_name'] }}</h2>
                        <p class="mb-4 text-white text-opacity-75">
                            This panel follows the worker-first flow we reused from the Future-card structure: start from POS/workspace, move into customer or vehicle lookup, then reference products and services without exposing tenant admin setup screens.
                        </p>

                        <div class="d-flex flex-wrap gap-2 employee-shell-toolbar">
                            <a href="{{ route('employee.pos') }}" class="btn btn-light text-primary">
                                <i class="ti tabler-cash-register me-1"></i>
                                Open POS / Workspace
                            </a>
                            @if($user->can('customer.view') || $user->can('customers.view'))
                                <a href="{{ route('tenant.ecommerce.customers.index') }}" class="btn btn-outline-light">
                                    <i class="ti tabler-users me-1"></i>
                                    Customers Lookup
                                </a>
                            @endif
                            <a href="{{ route('employee.account') }}" class="btn btn-outline-light">
                                <i class="ti tabler-user-circle me-1"></i>
                                Profile
                            </a>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card bg-white bg-opacity-10 border-0 shadow-none mb-0">
                            <div class="card-body">
                                <small class="text-white text-opacity-75 d-block mb-2">Today Summary</small>
                                <h4 class="text-white mb-1">{{ $stats['today_full_label'] }}</h4>
                                <p class="text-white text-opacity-75 mb-3">
                                    Live cards below use real tenant-scoped product, service, customer, and vehicle data only.
                                </p>
                                <div class="d-flex justify-content-between text-white text-opacity-75">
                                    <span>Accessible Modules</span>
                                    <span class="fw-semibold text-white">{{ $stats['accessible_modules'] }}</span>
                                </div>
                                <div class="d-flex justify-content-between text-white text-opacity-75 mt-2">
                                    <span>Workspace Team</span>
                                    <span class="fw-semibold text-white">{{ $stats['team_members'] }}</span>
                                </div>
                                <div class="d-flex justify-content-between text-white text-opacity-75 mt-2">
                                    <span>Low Stock Watch</span>
                                    <span class="fw-semibold text-white">{{ $stats['low_stock_products'] ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach($summaryCards as $card)
        <div class="col-xl-3 col-md-6">
            <div class="card employee-shell-stat h-100">
                <div class="card-body">
                    <span class="avatar bg-label-{{ $card['theme'] }} mb-3">
                        <i class="ti {{ $card['icon'] }}"></i>
                    </span>
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
                    <h5 class="mb-0">Daily Flow</h5>
                    <small class="text-muted">Quick worker actions, lookup entry points, and future-ready placeholders.</small>
                </div>
                <span class="badge bg-label-primary">Operator First</span>
            </div>
            <div class="card-body">
                <div class="employee-shell-grid">
                    @foreach($actionCards as $card)
                        <a href="{{ $card['route'] }}" class="card employee-shell-card h-100 mb-0 text-decoration-none">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                                    <div>
                                        <span class="badge bg-label-{{ $card['theme'] }} mb-2">{{ $card['badge'] }}</span>
                                        <h5 class="mb-1 text-body">{{ $card['label'] }}</h5>
                                        <p class="mb-0 text-muted">{{ $card['description'] }}</p>
                                    </div>
                                    <span class="avatar bg-label-{{ $card['theme'] }}">
                                        <i class="ti {{ $card['icon'] }}"></i>
                                    </span>
                                </div>
                                <small class="text-muted">{{ $card['stat'] }}</small>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Profile / Workspace</h5>
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

                <div class="rounded bg-label-primary p-3 mb-3">
                    <small class="text-muted d-block mb-1">Role</small>
                    <div class="fw-semibold">{{ str($user->primaryRoleName() ?? 'employee')->replace('_', ' ')->title() }}</div>
                </div>

                <div class="rounded bg-label-success p-3 mb-3">
                    <small class="text-muted d-block mb-1">Workspace</small>
                    <div class="fw-semibold">{{ $stats['workspace_name'] }}</div>
                </div>

                <div class="rounded bg-label-info p-3">
                    <small class="text-muted d-block mb-1">Last Login</small>
                    <div class="fw-semibold">{{ $user->last_login_at?->format('d M Y h:i A') ?? 'First recorded session' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Today Summary</h5>
            </div>
            <div class="card-body">
                @foreach($summaryRows as $row)
                    <div class="d-flex justify-content-between align-items-center py-2 {{ $loop->last ? '' : 'border-bottom' }}">
                        <span class="text-muted">{{ $row['label'] }}</span>
                        <span class="fw-semibold">{{ $row['value'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @foreach($placeholders as $placeholder)
        <div class="col-xl-2 col-md-4">
            <div class="card employee-shell-placeholder h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="mb-0">{{ $placeholder['title'] }}</h6>
                        <span class="avatar avatar-sm bg-label-secondary">
                            <i class="ti {{ $placeholder['icon'] }}"></i>
                        </span>
                    </div>
                    <p class="text-muted mb-0">{{ $placeholder['description'] }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
