@extends('layouts.app')

@section('title', 'Dashboard')

@php($sym = $currencySymbol)

@section('content')
<div id="dashboard-content" data-url="{{ route('tenant.dashboard') }}">
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
                            <input type="date" name="start" class="form-control form-control-sm" value="{{ $range['period'] === 'custom' ? $range['start'] : '' }}" required>
                        </div>
                        <div class="mb-2">
                            <input type="date" name="end" class="form-control form-control-sm" value="{{ $range['period'] === 'custom' ? $range['end'] : '' }}" required>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary w-100">Apply custom range</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Welcome / shop hero --}}
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-body d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                    <div>
                        <span class="badge bg-label-primary mb-2">{{ ucfirst($application['status']) }} workspace</span>
                        <h4 class="mb-1">Welcome back to {{ $application['name'] }} 👋</h4>
                        <p class="mb-3 text-muted">
                            Here's how your shop is performing. You've booked
                            <span class="fw-semibold text-heading">{{ $sym }}{{ number_format($cards['total_sales'], 2) }}</span>
                            across <span class="fw-semibold text-heading">{{ number_format($cards['orders_total']) }}</span> orders,
                            and have <span class="fw-semibold text-heading">{{ number_format($cards['estimates_total'] ?? 0) }}</span> estimates active.
                        </p>
                        <a href="{{ route('employee.order.new-order') }}" class="btn btn-sm btn-primary">
                            <i class="ti tabler-plus me-1"></i> New Order
                        </a>
                        <a href="{{ route('employee.order.index') }}" class="btn btn-sm btn-label-secondary">View Orders</a>
                    </div>
                    <div class="d-none d-lg-block text-center">
                        <img src="{{ asset('assets/img/illustrations/girl-with-laptop-light.png') }}" alt="Dashboard" class="img-fluid" style="max-height: 150px;">
                    </div>
                </div>
            </div>
        </div>

        {{-- Sales this month highlight --}}
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted d-block mb-1">Sales ({{ $range['label'] }})</span>
                            <h4 class="mb-1">{{ $sym }}{{ number_format($cards['sales_this_month'], 2) }}</h4>
                            @php($up = $cards['sales_month_change'] >= 0)
                            <span class="badge bg-label-{{ $up ? 'success' : 'danger' }}">
                                <i class="ti tabler-trending-{{ $up ? 'up' : 'down' }} me-1"></i>{{ $up ? '+' : '' }}{{ $cards['sales_month_change'] }}%
                            </span>
                            <small class="text-muted ms-1">vs previous period</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary"><i class="ti tabler-chart-pie"></i></span>
                        </div>
                    </div>
                    <div id="salesMonthChart" class="mt-2"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="row g-4 mb-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 justify-content-center">
        @php($statCards = [
            ['label' => 'Total Sales', 'value' => $sym.number_format($cards['total_sales'], 2), 'icon' => 'tabler-currency-dollar', 'color' => 'primary', 'sub' => 'Avg order '.$sym.number_format($cards['avg_order_value'], 2)],
            ['label' => 'Orders', 'value' => number_format($cards['orders_total']), 'icon' => 'tabler-shopping-cart', 'color' => 'info', 'sub' => $cards['orders_this_month'].' this month'],
            ['label' => 'Estimates', 'value' => number_format($cards['estimates_total'] ?? 0), 'icon' => 'tabler-file-analytics', 'color' => 'warning', 'sub' => 'Value: '.$sym.number_format($cards['estimates_value'] ?? 0, 2)],
            ['label' => 'Customers', 'value' => number_format($cards['customers_total']), 'icon' => 'tabler-users', 'color' => 'success', 'sub' => $cards['items_sold'].' items sold'],
            ['label' => 'Products', 'value' => number_format($cards['products_total']), 'icon' => 'tabler-package', 'color' => $cards['low_stock_count'] > 0 ? 'warning' : 'secondary', 'sub' => $cards['low_stock_count'].' low / out of stock'],
        ])
        @foreach ($statCards as $c)
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-{{ $c['color'] }}"><i class="ti {{ $c['icon'] }}"></i></span>
                            </div>
                        </div>
                        <span class="text-muted d-block">{{ $c['label'] }}</span>
                        <h4 class="mb-1">{{ $c['value'] }}</h4>
                        <small class="text-muted text-nowrap">{{ $c['sub'] }}</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Secondary key figures --}}
    <div class="row g-4 mb-4">
        @php($figures = [
            ['label' => 'Collected', 'value' => $sym.number_format($cards['collected'], 2), 'icon' => 'tabler-cash', 'color' => 'success'],
            ['label' => 'Outstanding', 'value' => $sym.number_format($cards['outstanding'], 2), 'icon' => 'tabler-clock-dollar', 'color' => 'danger'],
            ['label' => 'Avg Order Value', 'value' => $sym.number_format($cards['avg_order_value'], 2), 'icon' => 'tabler-receipt', 'color' => 'info'],
            ['label' => 'Items Sold', 'value' => number_format($cards['items_sold']), 'icon' => 'tabler-box', 'color' => 'primary'],
        ])
        @foreach ($figures as $f)
            <div class="col-xl-3 col-sm-6">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-{{ $f['color'] }}"><i class="ti {{ $f['icon'] }}"></i></span>
                        </div>
                        <div>
                            <span class="text-muted d-block">{{ $f['label'] }}</span>
                            <h5 class="mb-0">{{ $f['value'] }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Sales overview + orders by status --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card h-100">
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
            <div class="card h-100">
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
            <div class="card h-100">
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
            <div class="card h-100">
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
            <div class="card h-100">
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
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0">Customers</h5></div>
                <div class="card-body">
                    <div id="customersTypeChart"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card h-100">
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
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Orders</h5>
                    <a href="{{ route('employee.order.index') }}" class="btn btn-sm btn-label-primary">View all</a>
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
                                    <td><a href="{{ route('employee.order.show', $o['id']) }}" class="fw-medium">{{ $o['order_number'] }}</a></td>
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
            <div class="card h-100">
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
            <div class="card">
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
                            'Orders' => route('employee.order.index'),
                            'Estimates' => route('employee.order.index', ['tab' => 'estimates']),
                        ];
                        $catalogMeta = [
                            'Categories' => ['icon' => 'tabler-category', 'color' => 'primary'],
                            'Sub Categories' => ['icon' => 'tabler-folders', 'color' => 'info'],
                            'Product Types' => ['icon' => 'tabler-components', 'color' => 'dark'],
                            'Products' => ['icon' => 'tabler-package', 'color' => 'success'],
                            'Services' => ['icon' => 'tabler-settings', 'color' => 'danger'],
                            'Discounts' => ['icon' => 'tabler-tag', 'color' => 'warning'],
                            'Discount Groups' => ['icon' => 'tabler-tags', 'color' => 'secondary'],
                            'Customers' => ['icon' => 'tabler-users', 'color' => 'primary'],
                            'Vehicles' => ['icon' => 'tabler-car', 'color' => 'info'],
                            'Orders' => ['icon' => 'tabler-shopping-cart', 'color' => 'success'],
                            'Estimates' => ['icon' => 'tabler-file-analytics', 'color' => 'warning'],
                        ];
                    ?>
                    <style>
                        .catalog-card-link {
                            text-decoration: none !important;
                            display: block;
                        }
                        .catalog-card-item {
                            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
                            border: 1px solid transparent;
                        }
                        .catalog-card-item:hover {
                            transform: translateY(-4px);
                            box-shadow: 0 8px 16px rgba(115, 103, 240, 0.12);
                            border-color: rgba(115, 103, 240, 0.3);
                        }
                    </style>
                    <div class="row g-3">
                        @foreach ($application['catalog'] as $label => $count)
                            @if (! $vehicleFeatureEnabled && $label === 'Vehicles')
                                @continue
                            @endif
                            <?php
                                $targetUrl = $catalogUrls[$label] ?? '#';
                                $meta = $catalogMeta[$label] ?? ['icon' => 'tabler-circle', 'color' => 'secondary'];
                                $icon = $meta['icon'];
                                $color = $meta['color'];
                            ?>
                            <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                                <a href="{{ $targetUrl }}" class="catalog-card-link h-100">
                                    <div class="rounded bg-label-{{ $color }} p-3 text-center h-100 catalog-card-item d-flex flex-column align-items-center justify-content-center">
                                        <div class="avatar avatar-sm mb-2">
                                            <span class="avatar-initial rounded bg-{{ $color }}"><i class="ti {{ $icon }} fs-4 text-white"></i></span>
                                        </div>
                                        <h5 class="mb-0 fw-bold text-heading mt-1">{{ number_format($count) }}</h5>
                                        <small class="text-muted text-nowrap">{{ $label }}</small>
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
    <script src="{{ asset('assets/js/tenant/dashboard.js') }}?v={{ filemtime(public_path('assets/js/tenant/dashboard.js')) }}"></script>
@endsection
