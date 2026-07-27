@extends('layouts.customer-portal')

@section('title', 'Vehicles')

@section('content')
    <div class="cp-page-heading">
        <div class="cp-page-heading-main">
            <div>
                <h1 class="cp-page-title">Vehicles</h1>
                <p class="cp-page-subtitle">Vehicles on file at this shop</p>
            </div>
        </div>
    </div>

    <div class="cp-panel">
        <div class="cp-list">
            @forelse ($vehicles as $vehicle)
                @php
                    $label = trim(collect([$vehicle->year, $vehicle->make, $vehicle->model])->filter()->implode(' '));
                @endphp
                <div class="cp-list-item">
                    <div class="d-flex align-items-start gap-3 min-w-0">
                        <span class="cp-vehicle-icon"><i class="ti tabler-car"></i></span>
                        <div class="min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-semibold">{{ $label !== '' ? $label : 'Vehicle' }}</span>
                                @if ($vehicle->is_default)
                                    <span class="badge bg-label-primary">Default</span>
                                @endif
                            </div>
                            <div class="small text-muted mt-1">
                                @if ($vehicle->plate_number)
                                    Plate {{ $vehicle->plate_number }}
                                @elseif ($vehicle->registration_number)
                                    Reg {{ $vehicle->registration_number }}
                                @else
                                    No plate on file
                                @endif
                                @if ($vehicle->color)
                                    · {{ $vehicle->color }}
                                @endif
                                @if ($vehicle->odometer !== null)
                                    · {{ number_format((float) $vehicle->odometer, 0) }} mi
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="cp-list-empty">
                    <i class="ti tabler-car"></i>
                    <p class="mb-0">No vehicles on file yet.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
