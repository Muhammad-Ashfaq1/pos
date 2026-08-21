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
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/pos-glass.css') }}?v={{ filemtime(public_path('assets/css/pos-glass.css')) }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/css/employee-orders.css') }}?v={{ filemtime(public_path('assets/css/employee-orders.css')) }}" />
    @endpush
@endif

@section('content')
@if($isEmployee)
    <div class="employee-orders-page employee-orders-glass">
        <x-employee.page-header
            title="Reports"
            :back-url="route($dashboardRoute)"
            back-title="Back to dashboard"
        />
@else
<div class="container-fluid flex-grow-1 container-p-y">
@endif

    {{-- Report picker (tabs) --}}
    <ul class="nav nav-tabs mb-3">
        @foreach($tabs as $tab)
            <li class="nav-item">
                <a class="nav-link {{ $tab['active'] ? 'active' : '' }}" href="{{ $tab['url'] }}">
                    {{ $tab['label'] }}
                </a>
            </li>
        @endforeach
    </ul>

    {{-- Filter toolbar: search · date range · filters · reset --}}
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <div class="input-group" style="width: 200px;">
            <span class="input-group-text"><i class="ti tabler-search"></i></span>
            <input type="search" id="report-search" class="form-control" placeholder="Search…" aria-label="Search records">
        </div>

        <div style="width: 160px;">
            <select id="report-period" class="form-select report-filter" data-placeholder="Date Range" aria-label="Date Range">
                @foreach($periodLabels as $value => $label)
                    <option value="{{ $value }}" @selected($value === 'month')>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="report-custom-range d-none" style="width: 150px;">
            <input type="text" id="report-start" class="form-control report-filter app-datepicker" placeholder="YYYY-MM-DD" aria-label="From date" autocomplete="off">
        </div>
        <div class="report-custom-range d-none" style="width: 150px;">
            <input type="text" id="report-end" class="form-control report-filter app-datepicker" placeholder="YYYY-MM-DD" aria-label="To date" autocomplete="off">
        </div>

        @if(count($dateColumns) > 1)
            <div style="width: 150px;">
                <select id="report-date-column" class="form-select report-filter" data-placeholder="Filter Date By" aria-label="Filter Date By">
                    @foreach($dateColumns as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @foreach($filters as $filter)
            <div style="width: 150px;">
                @if($filter['type'] === 'select')
                    <select id="report-filter-{{ $filter['key'] }}" class="form-select report-filter" data-filter-key="{{ $filter['key'] }}" data-placeholder="{{ $filter['label'] }}" aria-label="{{ $filter['label'] }}">
                        <option value="">{{ $filter['label'] }}: All</option>
                        @foreach(($filter['options'] ?? []) as $optValue => $optLabel)
                            <option value="{{ $optValue }}">{{ $optLabel }}</option>
                        @endforeach
                    </select>
                @elseif($filter['type'] === 'boolean')
                    <select id="report-filter-{{ $filter['key'] }}" class="form-select report-filter" data-filter-key="{{ $filter['key'] }}" data-placeholder="{{ $filter['label'] }}" aria-label="{{ $filter['label'] }}">
                        <option value="">{{ $filter['label'] }}: All</option>
                        <option value="1">Yes</option>
                    </select>
                @else
                    <input type="number" step="any" id="report-filter-{{ $filter['key'] }}" class="form-control report-filter" data-filter-key="{{ $filter['key'] }}" placeholder="{{ $filter['label'] }}" aria-label="{{ $filter['label'] }}">
                @endif
            </div>
        @endforeach

        <div class="d-flex flex-wrap gap-2 ms-lg-auto">
            <button type="button" id="report-reset" class="btn btn-label-secondary">
                <i class="ti tabler-refresh me-1"></i> Reset Filters
            </button>
            <a href="#" id="report-export" class="btn btn-primary disabled" aria-disabled="true" tabindex="-1">
                <i class="ti tabler-download me-1"></i> Download Report
            </a>
        </div>
    </div>

    {{-- Summary strip (populated via AJAX) --}}
    <div class="mb-3" id="report-summary"></div>

    <div class="{{ $isEmployee ? 'pos-glass-card pos-tone-primary' : 'card' }}">
        <div class="card-datatable table-responsive pt-0">
            <table class="reports-datatable table table-hover align-middle">
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
@if($isEmployee)
    </div>
@else
</div>
@endif
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
    <script src="{{ asset('assets/js/reports/index.js') }}?v={{ filemtime(public_path('assets/js/reports/index.js')) }}"></script>
@endsection
