@extends('layouts.employee-portal')

@section('title', 'Employee Portal Dashboard')

@php
    use App\Support\EmployeeNavigation;

    $user = auth()->user();
    $productMixPeriod = $product_mix_period ?? 'today';
    $dashboardRange = $dashboard_range ?? ['period' => 'today', 'label' => 'Today', 'start' => '', 'end' => ''];
    $summaryCards = $summary_cards ?? [];

    $tiles = EmployeeNavigation::dashboardTiles($user);

    $operations = [
        ['label' => 'End of Day Status', 'icon' => 'tabler-sun-low'],
        ['label' => 'Till Management', 'icon' => 'tabler-credit-card'],
    ];

    $initialDashboardData = [
        'period' => $productMixPeriod,
        'period_label' => $product_mix_period_label ?? 'Today',
        'dashboard_range' => $dashboardRange,
        'summary_cards' => $summaryCards,
        'top_products' => $top_products ?? [],
        'sales_by_category' => $sales_by_category ?? [],
        'trend' => [
            'labels' => $trend_labels ?? [],
            'sales' => $trend_sales ?? [],
            'estimates' => $trend_estimates ?? [],
        ],
    ];
@endphp

@section('content')
    <div class="employee-dashboard">
        <div class="pos-ed-banner">
            <div class="pos-glass-card pos-tone-primary">
                <div class="pos-glass-intro">
                    <div class="pos-glass-intro-copy">
                        <h4 class="pos-glass-intro-title">Employee workspace</h4>
                        <p class="pos-glass-intro-subtitle">Today’s orders, product mix, and quick actions for the floor.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="preview-grid">
            <section class="preview-left-column">
                @include('employee.partials.preview-product-mix', [
                    'summaryCards' => $summaryCards,
                    'productMixPeriod' => $productMixPeriod,
                    'productMixPeriodLabel' => $product_mix_period_label ?? 'Today',
                    'dashboardRange' => $dashboardRange,
                    'topProducts' => $top_products ?? [],
                    'salesByCategory' => $sales_by_category ?? [],
                    'currencySymbol' => $currency_symbol ?? \App\Support\Currency::symbol(),
                ])

                <div class="preview-card pos-glass-card pos-tone-info mt-4 mb-4" id="employee-performance-chart-card">
                    <div class="preview-card-header">
                        <div>
                            <h2 class="preview-card-title">Performance Trend</h2>
                            <p class="preview-card-subtitle mb-0" data-performance-range-label>{{ $product_mix_period_label ?? 'Today' }}</p>
                        </div>
                    </div>
                    <div class="preview-card-body">
                        <div id="employeePerformanceChart" style="min-height: 220px;"></div>
                    </div>
                </div>

                @include('employee.partials.preview-operations', ['operations' => $operations])
            </section>

            <section class="preview-right-column">
                @include('employee.partials.preview-tiles-grid', ['tiles' => $tiles])
            </section>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pos-glass.css') }}?v={{ filemtime(public_path('assets/css/pos-glass.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/employee-dashboard.css') }}?v={{ filemtime(public_path('assets/css/employee-dashboard.css')) }}" />
    <style>
        .employee-admin-preview .preview-refresh-btn.is-refreshing i {
            animation: product-mix-spin 0.8s linear infinite;
        }

        .employee-admin-preview .preview-status-dot.is-live {
            background: #22c55e;
        }

        @keyframes product-mix-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
@endpush

@push('page-script')
    <script>
        window.employeeDashboardConfig = {
            productMixUrl: @json(route('employee.dashboard.product-mix')),
            currencySymbol: @json($currency_symbol ?? \App\Support\Currency::symbol()),
            chartPalette: @json(\App\Support\ProductMixCards::chartPalette()),
            trendChartId: 'employeePerformanceChart',
            initialData: @json($initialDashboardData)
        };
    </script>
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/js/shared/sales-mix-charts.js') }}?v={{ filemtime(public_path('assets/js/shared/sales-mix-charts.js')) }}"></script>
    <script src="{{ asset('assets/js/employee/dashboard.js') }}?v={{ filemtime(public_path('assets/js/employee/dashboard.js')) }}"></script>
@endpush
