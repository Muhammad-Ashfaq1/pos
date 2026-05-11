@extends('layouts.app')

@section('title', 'Vehicles')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Vehicles</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Customers</li>
                    <li class="breadcrumb-item active" aria-current="page">Vehicles</li>
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
                <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 340px;">
                    <div class="mb-3">
                        <label for="vehicle_filter_customer" class="form-label">Customer</label>
                        <select
                            id="vehicle_filter_customer"
                            class="form-select customer-select2"
                            data-placeholder="All customers"
                            data-allow-clear="true"
                        >
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="vehicle_default_filter" class="form-label">Default Status</label>
                        <select
                            id="vehicle_default_filter"
                            class="form-select filter-control select2"
                            data-placeholder="All vehicles"
                            data-allow-clear="false"
                            data-minimum-results-for-search="Infinity"
                        >
                            <option value="">All</option>
                            <option value="1">Default</option>
                            <option value="0">Standard</option>
                        </select>
                    </div>
                    <div>
                        <label for="vehicle_sort" class="form-label">Sort By</label>
                        <select
                            id="vehicle_sort"
                            class="form-select filter-control select2"
                            data-placeholder="Sort vehicles"
                            data-allow-clear="false"
                            data-minimum-results-for-search="Infinity"
                        >
                            <option value="latest">Latest</option>
                            <option value="customer">Customer A-Z</option>
                            <option value="plate">Plate A-Z</option>
                            <option value="year_desc">Year High-Low</option>
                        </select>
                    </div>
                </div>
            </div>

            @can('create', \App\Models\Vehicle::class)
                <button
                    type="button"
                    class="btn btn-primary"
                    id="addVehicleBtn"
                    data-bs-toggle="modal"
                    data-bs-target="#vehicleModal"
                >
                    <i class="ti tabler-plus me-1"></i>
                    Add Vehicle
                </button>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="vehicles-datatables table">
                <thead class="bg-label-primary">
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Plate</th>
                        <th>Registration</th>
                        <th>Vehicle</th>
                        <th>Odometer</th>
                        <th>Default</th>
                        <th>Created</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    @include('tenant.ecommerce.vehicles.partials.save-modal')
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.20.0/jquery.validate.min.js"></script>
    <script>
        window.vehicleListingUrl = @json($listingUrl);
        window.vehicleEditUrlTemplate = @json($editUrlTemplate);
        window.customerDropdownUrl = @json($customersDropdownUrl);
        window.vehicleDropdownUrl = @json($vehiclesDropdownUrl);
    </script>
    <script src="{{ asset('assets/js/tenant/e-com/vehicle-manager.js') }}?v={{ filemtime(public_path('assets/js/tenant/e-com/vehicle-manager.js')) }}"></script>
    <script src="{{ asset('assets/js/tenant/e-com/vehicles.js') }}"></script>
@endsection
