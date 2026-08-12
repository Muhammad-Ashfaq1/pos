<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h4 class="mb-0">Vehicles</h4>
        <p class="text-muted mb-0 small">Lookup and add vehicles for walk-in or registered customers.</p>
    </div>

    <div class="d-flex align-items-center gap-2" id="vehicleTableActions">
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

@include('tenant.ecommerce.vehicles.partials.save-modal', [
    'vehicleSaveUrl' => $vehicleSaveUrl ?? route('tenant.ecommerce.vehicles.save'),
])
