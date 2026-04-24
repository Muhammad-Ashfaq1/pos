@extends('layouts.employee-portal')

@section('title', 'Employee Portal Dashboard')

@php
    // Static demo data for UI preview only. Backend/auth wiring will be connected later.
    $summaryCards = [
        ['label' => "Today's Jobs", 'value' => '12', 'meta' => 'Assigned this shift', 'icon' => 'tabler-briefcase', 'variant' => 'primary'],
        ['label' => 'Pending Jobs', 'value' => '04', 'meta' => 'Waiting to begin', 'icon' => 'tabler-loader', 'variant' => 'secondary'],
        ['label' => 'In Progress', 'value' => '03', 'meta' => 'Vehicles in bay', 'icon' => 'tabler-car-garage', 'variant' => 'accent'],
    ];

    $actionTiles = [
        ['label' => 'Time Clock', 'icon' => 'tabler-clock-hour-4'],
        ['label' => 'Start Job', 'icon' => 'tabler-player-play'],
        ['label' => 'Assigned Jobs', 'icon' => 'tabler-list-details'],
        ['label' => 'Service Orders', 'icon' => 'tabler-clipboard-text'],
        ['label' => 'Update Job Status', 'icon' => 'tabler-refresh-dot'],
        ['label' => 'Customer Vehicle', 'icon' => 'tabler-car'],
        ['label' => 'Reports', 'icon' => 'tabler-report-analytics'],
        ['label' => 'Invoices', 'icon' => 'tabler-file-invoice'],
    ];

    $operations = [
        ['label' => 'End of Shift Status', 'icon' => 'tabler-sun-low', 'meta' => 'Review handoff checklist'],
        ['label' => 'Till Management', 'icon' => 'tabler-credit-card', 'meta' => 'Preview cash drawer summary'],
        ['label' => 'Bay Assignment', 'icon' => 'tabler-garage', 'meta' => 'See current service bay placement'],
        ['label' => 'Vehicle Lookup', 'icon' => 'tabler-search', 'meta' => 'Open customer vehicle preview'],
    ];

    $recentJobs = [
        ['customer' => 'Daniel Carter', 'vehicle' => 'Toyota Corolla 2020 - ABC-102', 'service' => 'Oil Change + Filter', 'status' => 'In Progress', 'theme' => 'info'],
        ['customer' => 'Ava Rodriguez', 'vehicle' => 'Honda Civic 2019 - KLM-441', 'service' => 'Engine Flush', 'status' => 'Pending', 'theme' => 'warning'],
        ['customer' => 'Michael Turner', 'vehicle' => 'Ford F-150 2021 - TX-9821', 'service' => 'Brake Inspection', 'status' => 'Completed', 'theme' => 'success'],
        ['customer' => 'Sophia Bennett', 'vehicle' => 'Nissan Altima 2018 - GHJ-908', 'service' => 'Transmission Check', 'status' => 'Assigned', 'theme' => 'primary'],
    ];
@endphp

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="employee-preview-title mb-1">Welcome back, Employee</h3>
            <p class="text-muted mb-0">Rapid Lube Downtown · Service Floor Workspace</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge bg-label-primary">25 Apr 2026</span>
            <span class="badge bg-label-success">Shift Active</span>
            <span class="badge bg-label-warning">Morning Shift</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card employee-surface-card mb-4">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="employee-preview-title mb-1">Product Mix</h4>
                        <p class="text-muted mb-0">Today's operator snapshot for the service floor.</p>
                    </div>

                    <div class="d-flex align-items-start gap-3">
                        <select class="form-select form-select-sm" aria-label="Employee dashboard filter">
                            <option selected>Today (Default)</option>
                            <option>This Week</option>
                            <option>This Month</option>
                        </select>

                        <div class="text-end">
                            <small class="employee-updated-text fw-semibold d-block">Updated</small>
                            <small class="text-muted">19 seconds ago</small>
                        </div>

                        <a href="javascript:void(0)" class="btn btn-icon btn-sm btn-label-primary">
                            <i class="ti tabler-refresh"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body pt-2">
                    <div class="row g-3">
                        @foreach($summaryCards as $card)
                            <div class="col-md-6 {{ $loop->last ? 'col-lg-6' : '' }}">
                                <div class="employee-dashboard-chip employee-dashboard-chip--{{ $card['variant'] }}">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <h2 class="mb-1">{{ $card['value'] }}</h2>
                                            <div class="fw-semibold text-body">{{ $card['label'] }}</div>
                                            <small class="text-muted">{{ $card['meta'] }}</small>
                                        </div>
                                        <i class="ti {{ $card['icon'] }} fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card employee-surface-card h-100">
                <div class="card-header">
                    <h4 class="employee-preview-title mb-1">Operations</h4>
                    <p class="text-muted mb-0">Quick links for employee desk and service floor actions.</p>
                </div>
                <div class="card-body pt-2">
                    @foreach($operations as $operation)
                        <div class="employee-ops-item d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center gap-3">
                                <span class="employee-ops-icon fs-4">
                                    <i class="ti {{ $operation['icon'] }}"></i>
                                </span>
                                <div>
                                    <div class="fw-semibold">{{ $operation['label'] }}</div>
                                    <small class="text-muted">{{ $operation['meta'] }}</small>
                                </div>
                            </div>

                            <a href="javascript:void(0)" class="employee-preview-link">
                                <i class="ti tabler-arrow-up-right fs-5"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="employee-tile-grid">
                @foreach($actionTiles as $tile)
                    <div class="card employee-tile-card employee-surface-card mb-0">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-4">
                            <span class="employee-tile-icon mb-3">
                                <i class="ti {{ $tile['icon'] }}"></i>
                            </span>
                            <h4 class="employee-tile-title mb-0">{{ $tile['label'] }}</h4>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-12">
            <div class="card employee-surface-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="employee-preview-title mb-1">Recent Jobs</h5>
                        <p class="text-muted mb-0">Static demo rows for the employee portal preview.</p>
                    </div>
                    <span class="badge bg-label-primary">UI Only</span>
                </div>
                <div class="table-responsive">
                    <table class="table employee-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentJobs as $job)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="avatar avatar-sm bg-label-primary">
                                                <span class="fw-semibold">{{ strtoupper(substr($job['customer'], 0, 1)) }}</span>
                                            </span>
                                            <span class="fw-medium">{{ $job['customer'] }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $job['vehicle'] }}</td>
                                    <td>{{ $job['service'] }}</td>
                                    <td>
                                        <span class="badge bg-label-{{ $job['theme'] }}">{{ $job['status'] }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary">View Job</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
