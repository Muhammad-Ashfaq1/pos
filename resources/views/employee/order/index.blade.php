@extends($layout ?? 'layouts.employee-portal')

@section('title', 'Orders')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/employee-orders.css') }}?v={{ filemtime(public_path('assets/css/employee-orders.css')) }}">
@endpush

@section('content')
    <div class="employee-orders-page">
        <x-employee.page-header title="Orders" :back-url="route($dashboardRoute ?? 'employee.dashboard')" back-title="Back to dashboard" />

        <div class="employee-orders-layout">
            <aside class="employee-orders-panel employee-orders-filters">
                <button
                    type="button"
                    class="employee-orders-action"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#employeeOrderAdvancedSearch"
                    aria-controls="employeeOrderAdvancedSearch">
                    <span>Advance Search</span>
                    <i class="ti tabler-chevron-right"></i>
                </button>

                <button
                    type="button"
                    class="employee-orders-action"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#employeeOrderSortPreference"
                    aria-controls="employeeOrderSortPreference">
                    <span>Sort Preference</span>
                    <i class="ti tabler-chevron-right"></i>
                </button>

                <label class="employee-orders-search">
                    <i class="ti tabler-search"></i>
                    <input type="search" class="form-control" placeholder="Search Name, Barcode or ALU" data-order-search>
                </label>
            </aside>

            <section class="employee-orders-panel employee-orders-results">
                <div class="employee-orders-tabs" role="tablist" aria-label="Order filters">
                    <button type="button" class="employee-orders-tab" data-order-tab="today">
                        Today (<span data-order-count="today">0</span>)
                    </button>
                    <button type="button" class="employee-orders-tab active" data-order-tab="all">
                        All (<span data-order-count="all">0</span>)
                    </button>
                    <button type="button" class="employee-orders-tab" data-order-tab="pending">
                        Pending (<span data-order-count="pending">0</span>)
                    </button>
                    <button type="button" class="employee-orders-tab" data-order-tab="estimates">
                        Estimates (<span data-order-count="estimates">0</span>)
                    </button>
                </div>

                <div class="employee-orders-list-heading">
                    <h5>Order Lists</h5>
                    <div class="employee-orders-list-actions">
                        <button type="button" class="employee-orders-icon-btn" data-order-refresh data-bs-toggle="tooltip" title="Refresh orders">
                            <i class="ti tabler-refresh"></i>
                        </button>
                        <a href="{{ route($orderRoutes['new']) }}" class="employee-orders-icon-btn" data-bs-toggle="tooltip" title="Create new order">
                            <i class="ti tabler-plus"></i>
                        </a>
                    </div>
                </div>


                <div class="employee-orders-list" data-order-list></div>

                <div class="employee-orders-empty d-none" data-order-empty>
                    <i class="ti tabler-clipboard-off"></i>
                    <span>No orders found.</span>
                </div>
            </section>
        </div>

        @include('employee.order.partials.advanced-search')
        @include('employee.order.partials.sort-preference')
    </div>
@endsection

@push('page-script')
    <script>
        window.employeeOrdersConfig = {
            listingUrl: @json(route($orderRoutes['listing'])),
            newOrderUrl: @json(route($orderRoutes['new'])),
            detailUrlTemplate: @json(route($orderRoutes['show'], ['order' => '__ORDER_ID__']))
        };
    </script>
    <script src="{{ asset('assets/js/employee/orders.js') }}?v={{ filemtime(public_path('assets/js/employee/orders.js')) }}"></script>
@endpush
