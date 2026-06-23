@extends($layout)

@section('title', $reportLabel.' Report')

@php
    $isEmployee = $layout === 'layouts.employee-portal';
    $periodLabels = [
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        'week' => 'Last 7 Days',
        'month' => 'This Month',
        'year' => 'This Year',
        'custom' => 'Custom Range',
    ];
@endphp

@if($isEmployee)
    @push('extra-css')
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    @endpush
@endif

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">{{ $reportLabel }} Report</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route($dashboardRoute) }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Reports</li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $reportLabel }}</li>
                </ol>
            </nav>
        </div>

    </div>

    {{-- Report picker --}}
    <ul class="nav nav-pills flex-column flex-md-row gap-1 gap-md-0 mb-4">
        @foreach($tabs as $tab)
            <li class="nav-item">
                <a class="nav-link {{ $tab['active'] ? 'active' : '' }}" href="{{ $tab['url'] }}">
                    {{ $tab['label'] }}
                </a>
            </li>
        @endforeach
    </ul>

    {{-- Summary cards (populated via AJAX) --}}
    <div class="row g-3 mb-4" id="report-summary"></div>

    {{-- Filter bar --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-sm-6 col-md-3">
                    <label class="form-label" for="report-period">Date Range</label>
                    <select id="report-period" class="form-select report-filter">
                        @foreach($periodLabels as $value => $label)
                            <option value="{{ $value }}" @selected($value === 'month')>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-6 col-md-3 report-custom-range d-none">
                    <label class="form-label" for="report-start">From</label>
                    <input type="date" id="report-start" class="form-control report-filter">
                </div>
                <div class="col-sm-6 col-md-3 report-custom-range d-none">
                    <label class="form-label" for="report-end">To</label>
                    <input type="date" id="report-end" class="form-control report-filter">
                </div>

                @if(count($dateColumns) > 1)
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label" for="report-date-column">Filter Date By</label>
                        <select id="report-date-column" class="form-select report-filter">
                            @foreach($dateColumns as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @foreach($filters as $filter)
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label" for="report-filter-{{ $filter['key'] }}">{{ $filter['label'] }}</label>
                        @if($filter['type'] === 'select')
                            <select id="report-filter-{{ $filter['key'] }}" class="form-select report-filter" data-filter-key="{{ $filter['key'] }}">
                                <option value="">All</option>
                                @foreach(($filter['options'] ?? []) as $optValue => $optLabel)
                                    <option value="{{ $optValue }}">{{ $optLabel }}</option>
                                @endforeach
                            </select>
                        @elseif($filter['type'] === 'boolean')
                            <select id="report-filter-{{ $filter['key'] }}" class="form-select report-filter" data-filter-key="{{ $filter['key'] }}">
                                <option value="">All</option>
                                <option value="1">Yes</option>
                            </select>
                        @else
                            <input type="number" step="any" id="report-filter-{{ $filter['key'] }}" class="form-control report-filter" data-filter-key="{{ $filter['key'] }}" placeholder="{{ $filter['label'] }}">
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0">{{ $reportLabel }} Records</h5>
            <a href="#" id="report-export" class="btn btn-success disabled" aria-disabled="true" tabindex="-1">
                <i class="ti tabler-file-spreadsheet me-1"></i> Export Excel
            </a>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table class="reports-datatable table">
                <thead class="bg-label-primary">
                    <tr>
                        <th>#</th>
                        @foreach($columns as $column)
                            <th class="text-{{ $column['align'] }}">{{ $column['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        window.reportConfig = {
            dataUrl: @json($dataUrl),
            exportUrl: @json($exportUrl),
            columns: @json($columns),
            filterKeys: @json(collect($filters)->pluck('key')->values()),
            hasDateColumn: @json(count($dateColumns) > 1),
            emptyLabel: @json('No '.strtolower($reportLabel).' found'),
        };
    </script>
    @if($isEmployee)
        <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    @endif
    <script src="{{ asset('assets/js/reports/index.js') }}"></script>
@endsection
