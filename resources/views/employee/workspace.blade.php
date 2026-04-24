@extends('layouts.app')

@section('title', 'Employee Workspace')
@section('content_container_class', 'container-fluid flex-grow-1 container-p-y')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <span class="badge bg-label-primary mb-3">POS-Style Workspace</span>
                        <h3 class="mb-2">Daily operating shell for {{ $tenant?->display_name ?? 'your workspace' }}</h3>
                        <p class="text-muted mb-0">
                            This screen mirrors the worker-first flow pattern we inspected in Future-card: large action tiles, quick lookup, and a clear place to plug in service/order execution later without forcing admin-style navigation.
                        </p>
                    </div>
                    <div class="col-lg-5">
                        <div class="row g-3">
                            @foreach($catalogCards as $card)
                                <div class="col-sm-6">
                                    <div class="rounded bg-label-{{ $card['theme'] }} p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="fw-semibold">{{ $card['title'] }}</span>
                                            <i class="ti {{ $card['icon'] }}"></i>
                                        </div>
                                        <h4 class="mb-1">{{ $card['value'] }}</h4>
                                        <small class="text-muted">{{ $card['meta'] }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="row g-3">
            @foreach($workspaceTiles as $tile)
                <div class="col-md-6">
                    @if($tile['route'])
                        <a href="{{ $tile['route'] }}" class="card h-100 text-decoration-none">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <span class="badge bg-label-{{ $tile['theme'] }} mb-2">{{ $tile['status'] }}</span>
                                        <h5 class="mb-1 text-body">{{ $tile['label'] }}</h5>
                                        <p class="mb-0 text-muted">{{ $tile['description'] }}</p>
                                    </div>
                                    <span class="avatar bg-label-{{ $tile['theme'] }}">
                                        <i class="ti {{ $tile['icon'] }}"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    @else
                        <div class="card h-100 border-dashed">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <span class="badge bg-label-secondary mb-2">{{ $tile['status'] }}</span>
                                        <h5 class="mb-1">{{ $tile['label'] }}</h5>
                                        <p class="mb-0 text-muted">{{ $tile['description'] }}</p>
                                    </div>
                                    <span class="avatar bg-label-secondary">
                                        <i class="ti {{ $tile['icon'] }}"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Today Summary</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Date</span>
                    <span class="fw-semibold">{{ $stats['today_label'] }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Active Services</span>
                    <span class="fw-semibold">{{ $stats['active_services'] ?? 'N/A' }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Customers</span>
                    <span class="fw-semibold">{{ $stats['customers_total'] ?? 'N/A' }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Vehicles</span>
                    <span class="fw-semibold">{{ $stats['vehicles_total'] ?? 'N/A' }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span class="text-muted">Low Stock Watch</span>
                    <span class="fw-semibold">{{ $stats['low_stock_products'] ?? 'N/A' }}</span>
                </div>

                <div class="alert alert-primary mt-4 mb-0">
                    Service jobs, order queue, and notifications are intentionally placeholders until their tenant-scoped tables are introduced.
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
