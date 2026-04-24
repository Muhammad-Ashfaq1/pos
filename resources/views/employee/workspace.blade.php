@extends('layouts.employee')

@section('title', 'Employee Workspace')
@section('content_container_class', 'container-fluid flex-grow-1 container-p-y')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card employee-shell-hero border-0 text-white">
            <div class="card-body p-4 p-lg-5 position-relative">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <span class="badge bg-white text-primary mb-3">POS / Workspace</span>
                        <h3 class="text-white mb-2">Daily operations shell for {{ $stats['workspace_name'] }}</h3>
                        <p class="text-white text-opacity-75 mb-0">
                            Large action tiles keep this screen focused on cashier and employee work. It is ready to accept future order or service-job logic without borrowing tenant admin settings UX.
                        </p>
                    </div>
                    <div class="col-lg-5">
                        <div class="row g-3">
                            @foreach($catalogCards as $card)
                                <div class="col-sm-6">
                                    <div class="rounded bg-white bg-opacity-10 p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="fw-semibold text-white">{{ $card['title'] }}</span>
                                            <i class="ti {{ $card['icon'] }} text-white"></i>
                                        </div>
                                        <h4 class="mb-1 text-white">{{ $card['value'] }}</h4>
                                        <small class="text-white text-opacity-75">{{ $card['meta'] }}</small>
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
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Operator Actions</h5>
                    <small class="text-muted">Start from live lookups and catalogs while order execution is still a shell.</small>
                </div>
                <span class="badge bg-label-primary">{{ $stats['today_label'] }}</span>
            </div>
            <div class="card-body">
                <div class="employee-shell-grid">
                    @foreach($workspaceTiles as $tile)
                        @if($tile['route'])
                            <a href="{{ $tile['route'] }}" class="card employee-shell-card h-100 mb-0 text-decoration-none">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                                        <div>
                                            <span class="badge bg-label-{{ $tile['theme'] }} mb-2">{{ $tile['status'] }}</span>
                                            <h5 class="mb-1 text-body">{{ $tile['label'] }}</h5>
                                            <p class="mb-0 text-muted">{{ $tile['description'] }}</p>
                                        </div>
                                        <span class="avatar bg-label-{{ $tile['theme'] }}">
                                            <i class="ti {{ $tile['icon'] }}"></i>
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ $tile['meta'] }}</small>
                                </div>
                            </a>
                        @else
                            <div class="card employee-shell-placeholder h-100 mb-0">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                                        <div>
                                            <span class="badge bg-label-{{ $tile['theme'] }} mb-2">{{ $tile['status'] }}</span>
                                            <h5 class="mb-1">{{ $tile['label'] }}</h5>
                                            <p class="mb-0 text-muted">{{ $tile['description'] }}</p>
                                        </div>
                                        <span class="avatar bg-label-{{ $tile['theme'] }}">
                                            <i class="ti {{ $tile['icon'] }}"></i>
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ $tile['meta'] }}</small>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100" id="today-summary">
            <div class="card-header">
                <h5 class="mb-0">Today's Work Summary</h5>
            </div>
            <div class="card-body">
                @foreach($summaryRows as $row)
                    <div class="d-flex justify-content-between align-items-center py-2 {{ $loop->last ? '' : 'border-bottom' }}">
                        <span class="text-muted">{{ $row['label'] }}</span>
                        <span class="fw-semibold">{{ $row['value'] }}</span>
                    </div>
                @endforeach

                <div class="alert alert-primary mt-4 mb-0">
                    Orders, queue states, and technician assignments stay as placeholders until those tenant-scoped tables are implemented.
                </div>
            </div>
        </div>
    </div>

    @foreach($placeholders as $placeholder)
        <div class="col-xl-4 col-md-6">
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
