@extends('layouts.app')

@section('title', 'Employee Dashboard')
@section('content_container_class', 'container-fluid flex-grow-1 container-p-y')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card bg-primary text-white overflow-hidden">
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="badge bg-white text-primary mb-3">Worker Panel</span>
                        <h2 class="text-white mb-2">{{ $tenant?->display_name ?? 'Employee Workspace' }}</h2>
                        <p class="mb-4 text-white-50">
                            Use this panel as the daily operating start point for customer lookup, vehicle lookup, product references, and the employee workspace shell.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('employee.workspace') }}" class="btn btn-light text-primary">
                                <i class="ti tabler-cash-register me-1"></i>
                                Open Workspace
                            </a>
                            <a href="{{ route('employee.account') }}" class="btn btn-outline-light">
                                <i class="ti tabler-user-circle me-1"></i>
                                View Account
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card bg-white bg-opacity-10 border-0 shadow-none mb-0">
                            <div class="card-body">
                                <small class="text-white-50 d-block mb-2">Today Summary</small>
                                <h4 class="text-white mb-1">{{ $stats['today_label'] }}</h4>
                                <p class="text-white-50 mb-3">Tenant-scoped totals only. Job and queue data will appear here once those tables exist.</p>
                                <div class="d-flex justify-content-between text-white-50">
                                    <span>Modules Ready</span>
                                    <span class="fw-semibold text-white">{{ $stats['accessible_modules'] }}</span>
                                </div>
                                <div class="d-flex justify-content-between text-white-50 mt-2">
                                    <span>Team Members</span>
                                    <span class="fw-semibold text-white">{{ $stats['team_members'] }}</span>
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
            <div class="card h-100">
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
            <div class="card-header">
                <h5 class="mb-0">Daily Actions</h5>
                <small class="text-muted">Inspired by Future-card’s operator-first flow, adapted into the POS Vuexy card system.</small>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($operatorCards as $card)
                        <div class="col-md-6">
                            <a href="{{ $card['route'] }}" class="card shadow-none border h-100 text-decoration-none">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <span class="badge bg-label-{{ $card['theme'] }} mb-2">{{ $card['badge'] }}</span>
                                            <h6 class="mb-1 text-body">{{ $card['label'] }}</h6>
                                            <p class="mb-0 text-muted">{{ $card['description'] }}</p>
                                        </div>
                                        <span class="avatar bg-label-{{ $card['theme'] }}">
                                            <i class="ti {{ $card['icon'] }}"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
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

                <div class="rounded bg-label-primary p-3 mb-3">
                    <small class="text-muted d-block mb-1">Role</small>
                    <div class="fw-semibold">Employee</div>
                </div>

                <div class="rounded bg-label-success p-3 mb-3">
                    <small class="text-muted d-block mb-1">Workspace</small>
                    <div class="fw-semibold">{{ $tenant?->display_name ?? 'Tenant Workspace' }}</div>
                </div>

                <div class="rounded bg-label-info p-3">
                    <small class="text-muted d-block mb-1">Last Login</small>
                    <div class="fw-semibold">{{ $user->last_login_at?->format('d M Y h:i A') ?? 'First recorded session' }}</div>
                </div>
            </div>
        </div>
    </div>

    @foreach($placeholders as $placeholder)
        <div class="col-xl-4 col-md-6">
            <div class="card h-100">
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
