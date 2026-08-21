<div class="pos-listing">
    <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
        <div class="pos-listing-toolbar">
            <h4 class="pos-listing-title">Vehicles</h4>
            <div class="pos-listing-search-slot" aria-hidden="true"></div>
            <div class="pos-listing-toolbar-tools">
                <div class="pos-listing-toolbar-actions" id="vehicleTableActions">
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
        </div>

        <div class="card-datatable table-responsive pos-listing-table pt-0">
            <table class="vehicles-datatables table table-hover align-middle">
                <thead>
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
        'vehicleSaveUrl' => $vehicleSaveUrl ?? null,
    ])
</div>
