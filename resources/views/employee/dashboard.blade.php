@extends('layouts.employee-portal')

@section('title', 'Employee Portal Dashboard')

@php
    use App\Support\EmployeeNavigation;

    $user = auth()->user();
    $productMixPeriod = $product_mix_period ?? 'today';
    $summaryCards = $summary_cards ?? [];

    $tiles = EmployeeNavigation::dashboardTiles($user);

    $operations = [
        ['label' => 'End of Day Status', 'icon' => 'tabler-sun-low'],
        ['label' => 'Till Management', 'icon' => 'tabler-credit-card'],
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
                    'topProducts' => $top_products ?? [],
                    'salesByCategory' => $sales_by_category ?? [],
                    'currencySymbol' => $currency_symbol ?? \App\Support\Currency::symbol(),
                ])

                <div class="preview-card pos-glass-card pos-tone-info mt-4 mb-4">
                    <div class="preview-card-header">
                        <div>
                            <h2 class="preview-card-title">Performance Trend (Last 7 Days)</h2>
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
            chartPalette: @json(\App\Support\ProductMixCards::chartPalette())
        };
    </script>
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/js/employee/dashboard.js') }}?v={{ filemtime(public_path('assets/js/employee/dashboard.js')) }}"></script>
    <script>
        $(function() {
            var options = {
                chart: {
                    type: 'area',
                    height: 220,
                    parentHeightOffset: 0,
                    toolbar: { show: false }
                },
                series: [
                    { name: 'Sales', data: @json($trend_sales) },
                    { name: 'Estimates', data: @json($trend_estimates) }
                ],
                stroke: { curve: 'smooth', width: 2.5 },
                fill: { type: 'gradient', opacity: [0.15, 0.1] },
                colors: ['#28c76f', '#ff9f43'],
                xaxis: {
                    categories: @json($trend_labels),
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        formatter: function(val) {
                            return '{{ \App\Support\Currency::symbol() }}' + Number(val).toLocaleString(undefined, { minimumFractionDigits: 0 });
                        }
                    }
                },
                grid: { borderColor: '#e2e8f0', strokeDashArray: 5 },
                dataLabels: { enabled: false },
                tooltip: {
                    shared: true,
                    y: {
                        formatter: function(val) {
                            return '{{ \App\Support\Currency::symbol() }}' + Number(val).toLocaleString(undefined, { minimumFractionDigits: 2 });
                        }
                    }
                }
            };

            var el = document.getElementById('employeePerformanceChart');
            if (el) {
                new ApexCharts(el, options).render();
            }
        });
    </script>
@endpush
