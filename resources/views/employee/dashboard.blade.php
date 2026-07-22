@extends('layouts.employee-portal')

@section('title', 'Employee Portal Dashboard')

@php
    $user = auth()->user();
    $summaryCards = [
        ['key' => 'orders_completed_today', 'value' => number_format($orders_completed_today), 'label' => 'Orders', 'meta' => 'Completed Today', 'icon' => 'tabler-calendar-event', 'chip' => 'preview-chip--blue'],
        ['key' => 'orders_incomplete_today', 'value' => number_format($orders_incomplete_today), 'label' => 'Orders', 'meta' => 'Incompleted Today', 'icon' => 'tabler-map-pin-share', 'chip' => 'preview-chip--purple'],
        ['key' => 'products_available', 'value' => number_format($products_available), 'label' => 'Products', 'meta' => 'Available Today', 'icon' => 'tabler-search', 'chip' => 'preview-chip--violet'],
    ];

    $tiles = [
        ['label' => 'Time Clock', 'icon' => 'tabler-clock-hour-4'],
        ['label' => 'Create New Order', 'icon' => 'tabler-shopping-bag', 'url' => route('employee.order.new-order'), 'permission' => 'orders.create'],
        ['label' => 'Reports', 'icon' => 'tabler-report-search', 'url' => route('employee.reports.index', 'sales'), 'permission' => 'reports.view'],
        ['label' => 'Orders', 'icon' => 'tabler-clipboard-data', 'url' => route('employee.order.index'), 'permission' => 'orders.view'],
        ['label' => 'Returns', 'icon' => 'tabler-arrow-back-up', 'url' => route('employee.order.returns'), 'permission' => 'orders.view'],
        ['label' => 'Product Setup', 'icon' => 'tabler-package-import', 'url' => route('employee.products.index'), 'permission' => 'product.create'],
        ['label' => 'Invoices', 'icon' => 'tabler-file-invoice'],
        ['label' => 'Discounts', 'icon' => 'tabler-ticket', 'url' => route('employee.cards.type', 'discount'), 'permission' => ['cards.view', 'cards.manage']],
    ];

    $tiles = collect($tiles)
        ->filter(function ($tile) use ($user) {
            if (empty($tile['permission'])) {
                return true;
            }

            $permissions = is_array($tile['permission']) ? $tile['permission'] : [$tile['permission']];

            return collect($permissions)->contains(fn ($permission) => $user?->can($permission) ?? false);
        })
        ->values()
        ->all();

    $operations = [
        ['label' => 'End of Day Status', 'icon' => 'tabler-sun-low'],
        ['label' => 'Till Management', 'icon' => 'tabler-credit-card'],
    ];

    $bottomNav = [
        ['label' => 'POS', 'icon' => 'tabler-device-desktop'],
        ['label' => 'Customers', 'icon' => 'tabler-users'],
        ['label' => 'Inventory', 'icon' => 'tabler-package'],
        ['label' => 'Settings', 'icon' => 'tabler-settings'],
    ];
@endphp

@section('content')
    <div class="preview-grid">
        <section class="preview-left-column">
            @include('employee.partials.preview-product-mix', ['summaryCards' => $summaryCards])

            <div class="preview-card mt-4 mb-4">
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

    @include('employee.partials.preview-bottom-nav', ['bottomNav' => $bottomNav])
@endsection

@push('styles')
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
            productMixUrl: @json(route('employee.dashboard.product-mix'))
        };
    </script>
    <script src="{{ asset('assets/js/employee/dashboard.js') }}?v={{ filemtime(public_path('assets/js/employee/dashboard.js')) }}"></script>
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
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
