@extends('layouts.app')

@section('title', 'Customers')


    @php
        $customerTypes = \App\Models\Customer::typeOptions();
    @endphp
    @section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Customers</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Customers</li>
                    <li class="breadcrumb-item active" aria-current="page">Customers</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex align-items-center gap-2">
            <div class="dropdown">
                <button
                    type="button"
                    class="btn btn-label-secondary btn-icon"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    title="Filters"
                >
                    <i class="ti tabler-filter"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 320px;">
                    <div class="mb-3">
                        <label for="customer_type_filter" class="form-label">Customer Type</label>
                        <select
                            id="customer_type_filter"
                            class="form-select filter-control select2"
                            data-placeholder="All customer types"
                            data-allow-clear="false"
                        >
                            <option value="">All</option>
                            @foreach($customerTypes as $type => $label)
                                <option value="{{ $type }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="customer_sort" class="form-label">Sort By</label>
                        <select
                            id="customer_sort"
                            class="form-select filter-control select2"
                            data-placeholder="Sort customers"
                            data-allow-clear="false"
                            data-minimum-results-for-search="Infinity"
                        >
                            <option value="latest">Latest</option>
                            <option value="name">Name A-Z</option>
                            <option value="visits_high_low">Visits High-Low</option>
                            <option value="value_high_low">Lifetime Value High-Low</option>
                        </select>
                    </div>
                </div>
            </div>

            @can('create', \App\Models\Customer::class)
                <button
                    type="button"
                    class="btn btn-primary"
                    id="addCustomerBtn"
                    data-bs-toggle="modal"
                    data-bs-target="#customerModal"
                >
                    <i class="ti tabler-plus me-1"></i>
                    Add Customer
                </button>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="customers-datatables table">
                <thead class="bg-label-primary">
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Contact</th>
                        <th>Vehicles</th>
                        <th>Visits</th>
                        <th>Lifetime Value</th>
                        <th>Last Visit</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    @include('tenant.ecommerce.customers.partials.save-modal')
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.20.0/jquery.validate.min.js"></script>
    <script>
        window.customerListingUrl = @json($listingUrl);
        window.customerEditUrlTemplate = @json($editUrlTemplate);
        window.customerVehicleIndexUrlTemplate = @json($vehicleIndexUrlTemplate);
    </script>
    <script src="{{ asset('assets/js/tenant/e-com/customers.js') }}"></script>
    <script src="{{ asset('assets/js/tenant/e-com/customer-manager.js') }}"></script>
@endsection
