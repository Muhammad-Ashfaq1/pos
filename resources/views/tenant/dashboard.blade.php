@extends('layouts.app')

@section('title', 'Dashboard')

@php($sym = $currencySymbol)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pos-glass.css') }}?v={{ filemtime(public_path('assets/css/pos-glass.css')) }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/tenant-dashboard.css') }}?v={{ filemtime(public_path('assets/css/tenant-dashboard.css')) }}" />
@endpush

@section('content')
<div id="dashboard-content" class="pos-td" data-url="{{ route('tenant.dashboard') }}">
    {{-- Date-range filter --}}
    @php($periods = [
        'today' => ['Today', 'tabler-calendar'],
        'yesterday' => ['Yesterday', 'tabler-calendar-minus'],
        'week' => ['Last 7 Days', 'tabler-calendar-week'],
        'month' => ['This Month', 'tabler-calendar-month'],
        'year' => ['This Year', 'tabler-calendar-stats'],
    ])
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h5 class="mb-0">Dashboard</h5>
            <small class="text-muted">Showing: <span class="fw-semibold text-heading">{{ $range['label'] }}</span></small>
        </div>
        <div class="dropdown">
            <button class="btn btn-label-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                <i class="ti tabler-filter me-1"></i>{{ $range['label'] }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width: 16rem;">
                @foreach ($periods as $key => [$label, $icon])
                    <li>
                        <a class="dropdown-item rounded @if($range['period'] === $key) active @endif"
                           href="javascript:void(0)" data-period="{{ $key }}">
                            <i class="ti {{ $icon }} me-2"></i>{{ $label }}
                        </a>
                    </li>
                @endforeach
                <li><hr class="dropdown-divider"></li>
                <li class="px-2">
                    <form data-dashboard-custom>
                        <input type="hidden" name="period" value="custom">
                        <label class="form-label small mb-1 fw-semibold"><i class="ti tabler-calendar-event me-1"></i>Custom range</label>
                        <div class="mb-2">
                            <input type="text" name="start" class="form-control form-control-sm app-datepicker" placeholder="YYYY-MM-DD" value="{{ $range['period'] === 'custom' ? $range['start'] : '' }}" autocomplete="off" required>
                        </div>
                        <div class="mb-2">
                            <input type="text" name="end" class="form-control form-control-sm app-datepicker" placeholder="YYYY-MM-DD" value="{{ $range['period'] === 'custom' ? $range['end'] : '' }}" autocomplete="off" required>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary w-100">Apply custom range</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="pos-glass-card pos-tone-primary">
                <div class="pos-glass-intro">
                    <div class="pos-glass-intro-copy">
                        <h4 class="pos-glass-intro-title">Welcome back to {{ $application['name'] }}</h4>
                        <p class="pos-glass-intro-subtitle">
                            {{ ucfirst($application['status']) }} workspace ·
                            {{ $sym }}{{ number_format($cards['total_sales'], 2) }} booked across
                            {{ number_format($cards['orders_total']) }} orders ·
                            {{ number_format($cards['estimates_total'] ?? 0) }} estimates active
                        </p>
                    </div>
                    <div class="pos-glass-intro-actions d-flex flex-wrap gap-2 align-items-center">
                        <a href="{{ route('tenant.order.new-order') }}" class="btn btn-sm btn-primary">
                            <i class="ti tabler-plus me-1" aria-hidden="true"></i> New Order
                        </a>
                        <a href="{{ route('tenant.order.index') }}" class="btn btn-sm btn-label-secondary">View Orders</a>
                        @php($up = $cards['sales_month_change'] >= 0)
                        <span class="pos-glass-pill pos-tone-{{ $up ? 'success' : 'danger' }}">
                            <i class="icon-base ti tabler-trending-{{ $up ? 'up' : 'down' }}" aria-hidden="true"></i>
                            Sales ({{ $range['label'] }}): {{ $sym }}{{ number_format($cards['sales_this_month'], 2) }}
                            ({{ $up ? '+' : '' }}{{ $cards['sales_month_change'] }}%)
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="row g-4 mb-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 justify-content-center">
        @php($statCards = [
            ['label' => 'Total Sales', 'value' => $sym.number_format($cards['total_sales'], 2), 'icon' => 'tabler-currency-dollar', 'tone' => 'primary', 'sub' => 'Avg order '.$sym.number_format($cards['avg_order_value'], 2)],
            ['label' => 'Orders', 'value' => number_format($cards['orders_total']), 'icon' => 'tabler-shopping-cart', 'tone' => 'info', 'sub' => $cards['orders_this_month'].' this month'],
            ['label' => 'Estimates', 'value' => number_format($cards['estimates_total'] ?? 0), 'icon' => 'tabler-file-analytics', 'tone' => 'warning', 'sub' => 'Value: '.$sym.number_format($cards['estimates_value'] ?? 0, 2)],
            ['label' => 'Customers', 'value' => number_format($cards['customers_total']), 'icon' => 'tabler-users', 'tone' => 'success', 'sub' => $cards['items_sold'].' items sold'],
            ['label' => 'Products', 'value' => number_format($cards['products_total']), 'icon' => 'tabler-package', 'tone' => $cards['low_stock_count'] > 0 ? 'warning' : 'secondary', 'sub' => $cards['low_stock_count'].' low / out of stock'],
        ])
        @foreach ($statCards as $c)
            <div class="col">
                <div class="pos-glass-card pos-tone-{{ $c['tone'] }} h-100">
                    <div class="pos-stat-body">
                        <div class="pos-stat-head">
                            <span class="pos-stat-icon"><i class="icon-base ti {{ $c['icon'] }}" aria-hidden="true"></i></span>
                            <h6 class="pos-stat-label">{{ $c['label'] }}</h6>
                        </div>
                        <p class="pos-stat-value">{{ $c['value'] }}</p>
                        <p class="pos-stat-desc mb-0">{{ $c['sub'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Secondary key figures --}}
    <div class="row g-4 mb-4">
        @php($figures = [
            ['label' => 'Collected', 'value' => $sym.number_format($cards['collected'], 2), 'icon' => 'tabler-cash', 'tone' => 'success'],
            ['label' => 'Outstanding', 'value' => $sym.number_format($cards['outstanding'], 2), 'icon' => 'tabler-clock-dollar', 'tone' => 'danger'],
            ['label' => 'Avg Order Value', 'value' => $sym.number_format($cards['avg_order_value'], 2), 'icon' => 'tabler-receipt', 'tone' => 'info'],
            ['label' => 'Items Sold', 'value' => number_format($cards['items_sold']), 'icon' => 'tabler-box', 'tone' => 'primary'],
        ])
        @foreach ($figures as $f)
            <div class="col-xl-3 col-sm-6">
                <div class="pos-glass-card pos-tone-{{ $f['tone'] }} h-100">
                    <div class="pos-stat-body">
                        <div class="pos-stat-head">
                            <span class="pos-stat-icon"><i class="icon-base ti {{ $f['icon'] }}" aria-hidden="true"></i></span>
                            <h6 class="pos-stat-label">{{ $f['label'] }}</h6>
                        </div>
                        <p class="pos-stat-value">{{ $f['value'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Sales overview + orders by status --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="pos-glass-card pos-tone-primary h-100 pos-td-chart-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Sales Overview</h5>
                        <small class="text-muted">Revenue &amp; orders · {{ $range['label'] }}</small>
                    </div>
                    <span class="badge bg-label-primary">{{ $sym }}{{ number_format(array_sum($revenueTrend['revenue']), 2) }}</span>
                </div>
                <div class="card-body">
                    <div id="salesOverviewChart"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="pos-glass-card pos-tone-info h-100 pos-td-chart-card">
                <div class="card-header"><h5 class="mb-0">Orders by Status</h5></div>
                <div class="card-body">
                    <div id="ordersStatusChart"></div>
                    <div class="mt-3">
                        @foreach ($ordersByStatus as $s)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">{{ $s['label'] }}</span>
                                <span class="fw-semibold">{{ number_format($s['count']) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment methods + top products --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="pos-glass-card pos-tone-success h-100 pos-td-chart-card">
                <div class="card-header"><h5 class="mb-0">Payment Methods</h5></div>
                <div class="card-body">
                    @if (count($paymentMethods))
                        <div id="paymentMethodsChart"></div>
                        <div class="mt-3">
                            @foreach ($paymentMethods as $p)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted"><i class="ti tabler-point-filled me-1"></i>{{ $p['label'] }}</span>
                                    <span class="fw-semibold">{{ $sym }}{{ number_format($p['amount'], 2) }} <small class="text-muted">({{ $p['orders'] }})</small></span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center my-5">No payments recorded yet.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="pos-glass-card pos-tone-warning h-100 pos-td-chart-card">
                <div class="card-header"><h5 class="mb-0">Top Selling Products</h5></div>
                <div class="card-body">
                    @if (count($topProducts))
                        <div id="topProductsChart"></div>
                    @else
                        <p class="text-muted text-center my-5">No products sold yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Category sales + customers by type + revenue breakdown --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="pos-glass-card pos-tone-primary h-100 pos-td-chart-card">
                <div class="card-header"><h5 class="mb-0">Sales by Category</h5></div>
                <div class="card-body">
                    @if (count($salesByCategory))
                        <div id="categorySalesChart"></div>
                    @else
                        <p class="text-muted text-center my-5">No category sales yet.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="pos-glass-card pos-tone-info h-100 pos-td-chart-card">
                <div class="card-header"><h5 class="mb-0">Customers</h5></div>
                <div class="card-body">
                    <div id="customersTypeChart"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="pos-glass-card pos-tone-success h-100 pos-td-chart-card">
                <div class="card-header"><h5 class="mb-0">Revenue Breakdown</h5></div>
                <div class="card-body">
                    <div id="revenueBreakdownChart"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent orders + low stock --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="pos-glass-card pos-tone-primary h-100 pos-td-chart-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Orders</h5>
                    <a href="{{ route('tenant.order.index') }}" class="btn btn-sm btn-label-primary">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th class="text-end">Total</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentOrders as $o)
                                <tr>
                                    <td><a href="{{ route('tenant.order.show', $o['id']) }}" class="fw-medium">{{ $o['order_number'] }}</a></td>
                                    <td>{{ $o['customer'] }}</td>
                                    <td><span class="badge {{ $o['status_class'] }}">{{ $o['status_label'] }}</span></td>
                                    <td class="text-end fw-medium">{{ $o['total'] }}</td>
                                    <td><small class="text-muted text-nowrap">{{ $o['created_at'] }}</small></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-5">No orders yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="pos-glass-card pos-tone-warning h-100 pos-td-chart-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Low Stock Alerts</h5>
                    <a href="{{ route('tenant.ecommerce.products.index') }}" class="btn btn-sm btn-label-primary">Manage</a>
                </div>
                <div class="card-body">
                    @forelse ($lowStock as $item)
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar avatar-sm">
                                    <span class="avatar-initial rounded bg-label-{{ $item['is_out'] ? 'danger' : 'warning' }}"><i class="ti tabler-package"></i></span>
                                </div>
                                <div>
                                    <span class="fw-medium d-block">{{ \Illuminate\Support\Str::limit($item['name'], 32) }}</span>
                                    <small class="text-muted">{{ $item['sku'] ?? '—' }}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-label-{{ $item['is_out'] ? 'danger' : 'warning' }}">{{ $item['current_stock'] }} {{ $item['unit'] }}</span>
                                <small class="text-muted d-block">reorder @ {{ $item['reorder_level'] }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="ti tabler-circle-check icon-lg text-success d-block mb-2"></i>
                            All products are well stocked.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Application overview --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="pos-glass-card pos-tone-secondary pos-td-chart-card">
                <div class="card-header"><h5 class="mb-0">Application Overview</h5></div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <span class="text-muted d-block">Shop</span>
                            <span class="fw-medium">{{ $application['name'] }}</span>
                        </div>
                        <div class="col-md-3 col-6">
                            <span class="text-muted d-block">Status</span>
                            <span class="badge bg-label-success text-capitalize">{{ $application['status'] }}</span>
                        </div>
                        <div class="col-md-3 col-6">
                            <span class="text-muted d-block">Onboarding</span>
                            <span class="fw-medium text-capitalize">{{ str_replace('_', ' ', $application['onboarding']) }}</span>
                        </div>
                        <div class="col-md-3 col-6">
                            <span class="text-muted d-block">Team Members</span>
                            <span class="fw-medium">{{ $application['team_members'] }}</span>
                        </div>
                        <div class="col-md-3 col-6">
                            <span class="text-muted d-block">Currency</span>
                            <span class="fw-medium">{{ $application['currency'] }}</span>
                        </div>
                        <div class="col-md-3 col-6">
                            <span class="text-muted d-block">Timezone</span>
                            <span class="fw-medium">{{ $application['timezone'] }}</span>
                        </div>
                        <div class="col-md-3 col-6">
                            <span class="text-muted d-block">Established</span>
                            <span class="fw-medium">{{ $application['created_at'] ?? '—' }}</span>
                        </div>
                    </div>

                    <h6 class="mb-3">Catalog &amp; Records</h6>
                    <?php
                        $vehicleFeatureEnabled = app(\App\Support\Tenancy\TenantContext::class)->current()?->isVehicleRequired() ?? true;
                        $catalogUrls = [
                            'Categories' => route('tenant.ecommerce.categories.index'),
                            'Sub Categories' => route('tenant.ecommerce.subcategories.index'),
                            'Product Types' => route('tenant.ecommerce.product-types.index'),
                            'Products' => route('tenant.ecommerce.products.index'),
                            'Services' => route('tenant.ecommerce.services.index'),
                            'Discounts' => route('tenant.ecommerce.discounts.index'),
                            'Discount Groups' => route('tenant.discounts.group.index'),
                            'Customers' => route('tenant.ecommerce.customers.index'),
                            'Vehicles' => route('tenant.ecommerce.vehicles.index'),
                            'Orders' => route('tenant.order.index'),
                            'Estimates' => route('tenant.order.index', ['tab' => 'estimates']),
                        ];
                        $catalogMeta = [
                            'Categories' => ['icon' => 'tabler-category', 'tone' => 'primary'],
                            'Sub Categories' => ['icon' => 'tabler-folders', 'tone' => 'info'],
                            'Product Types' => ['icon' => 'tabler-components', 'tone' => 'dark'],
                            'Products' => ['icon' => 'tabler-package', 'tone' => 'success'],
                            'Services' => ['icon' => 'tabler-settings', 'tone' => 'danger'],
                            'Discounts' => ['icon' => 'tabler-tag', 'tone' => 'warning'],
                            'Discount Groups' => ['icon' => 'tabler-tags', 'tone' => 'secondary'],
                            'Customers' => ['icon' => 'tabler-users', 'tone' => 'primary'],
                            'Vehicles' => ['icon' => 'tabler-car', 'tone' => 'info'],
                            'Orders' => ['icon' => 'tabler-shopping-cart', 'tone' => 'success'],
                            'Estimates' => ['icon' => 'tabler-file-analytics', 'tone' => 'warning'],
                        ];
                    ?>
                    <div class="row g-3">
                        @foreach ($application['catalog'] as $label => $count)
                            @if (! $vehicleFeatureEnabled && $label === 'Vehicles')
                                @continue
                            @endif
                            <?php
                                $targetUrl = $catalogUrls[$label] ?? '#';
                                $meta = $catalogMeta[$label] ?? ['icon' => 'tabler-circle', 'tone' => 'secondary'];
                            ?>
                            <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                                <a href="{{ $targetUrl }}" class="pos-td-catalog-link h-100">
                                    <div class="pos-glass-card pos-tone-{{ $meta['tone'] }} h-100 pos-td-catalog-item">
                                        <div class="pos-stat-body text-center align-items-center">
                                            <span class="pos-stat-icon"><i class="icon-base ti {{ $meta['icon'] }}" aria-hidden="true"></i></span>
                                            <p class="pos-stat-value">{{ number_format($count) }}</p>
                                            <p class="pos-stat-label mb-0">{{ $label }}</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart dataset for this range; re-read by dashboard.js after each AJAX swap. --}}
    @php($dashboardData = [
        'currencySymbol' => $currencySymbol,
        'revenueTrend' => $revenueTrend,
        'ordersByStatus' => $ordersByStatus,
        'paymentMethods' => $paymentMethods,
        'topProducts' => $topProducts,
        'salesByCategory' => $salesByCategory,
        'customersByType' => $customersByType,
        'revenueBreakdown' => $revenueBreakdown,
    ])
    <script type="application/json" id="dashboardData">{!! json_encode($dashboardData) !!}</script>
</div>{{-- /#dashboard-content --}}
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/shared/sales-mix-charts.js') }}?v={{ filemtime(public_path('assets/js/shared/sales-mix-charts.js')) }}"></script>
    <script src="{{ asset('assets/js/tenant/dashboard.js') }}?v={{ filemtime(public_path('assets/js/tenant/dashboard.js')) }}"></script>
@endsection
