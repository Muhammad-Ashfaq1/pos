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

    <div class="modal fade" id="vehicleModal" tabindex="-1" aria-labelledby="vehicleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <form id="vehicleForm" action="{{ route('tenant.ecommerce.vehicles.save') }}" method="POST" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="vehicle_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="vehicleModalLabel">Add Vehicle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="vehicle_customer_entry_mode" class="form-label">Customer Mode <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select
                                        id="vehicle_customer_entry_mode"
                                        name="customer_entry_mode"
                                        class="form-select select2"
                                        data-placeholder="Select customer mode"
                                        data-dropdown-parent="#vehicleModal"
                                        data-allow-clear="false"
                                        data-minimum-results-for-search="Infinity"
                                    >
                                        <option value="existing">Select Existing Customer</option>
                                        <option value="walk_in">Walk-in / Quick Entry</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="col-md-4 customer-mode-section" data-mode="existing">
                                <label for="vehicle_customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select
                                        id="vehicle_customer_id"
                                        name="customer_id"
                                        class="form-select customer-select2"
                                        data-placeholder="Select a customer"
                                        data-allow-clear="false"
                                        data-dropdown-parent="#vehicleModal"
                                    ></select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-8 customer-mode-section" data-mode="existing">
                                <div class="alert alert-label-primary mb-0">
                                    Use this option when the customer already exists and this vehicle should link directly to that record.
                                </div>
                            </div>

                            <div class="col-md-6 customer-mode-section d-none" data-mode="walk_in">
                                <label for="inline_customer_name" class="form-label">Customer Name</label>
                                <input type="text" class="form-control" id="inline_customer_name" name="inline_customer_name" maxlength="150" placeholder="Walk-in Customer">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 customer-mode-section d-none" data-mode="walk_in">
                                <label for="inline_customer_phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="inline_customer_phone" name="inline_customer_phone" maxlength="30" placeholder="Optional">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 customer-mode-section d-none" data-mode="walk_in">
                                <label for="inline_customer_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="inline_customer_email" name="inline_customer_email" maxlength="150" placeholder="Optional">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 customer-mode-section d-none" data-mode="walk_in">
                                <label for="inline_customer_address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="inline_customer_address" name="inline_customer_address" maxlength="1000" placeholder="Optional">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-12 customer-mode-section d-none" data-mode="walk_in">
                                <input type="hidden" name="save_walk_in_as_customer" value="0">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="save_walk_in_as_customer" name="save_walk_in_as_customer" value="1" checked>
                                    <label class="form-check-label" for="save_walk_in_as_customer">Save this walk-in as customer</label>
                                </div>
                                <div class="form-text">
                                    If unchecked, the system will still create a safe internal walk-in link for this vehicle.
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>

                            <div class="col-md-4">
                                <label for="vehicle_plate_number" class="form-label">Plate Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control text-uppercase" id="vehicle_plate_number" name="plate_number" maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="vehicle_registration_number" class="form-label">Registration Number</label>
                                <input type="text" class="form-control text-uppercase" id="vehicle_registration_number" name="registration_number" maxlength="80">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-3">
                                <label for="vehicle_make" class="form-label">Make</label>
                                <input type="text" class="form-control" id="vehicle_make" name="make" maxlength="100">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="vehicle_model" class="form-label">Model</label>
                                <input type="text" class="form-control" id="vehicle_model" name="model" maxlength="100">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-2">
                                <label for="vehicle_year" class="form-label">Year</label>
                                <input type="number" min="1900" max="{{ now()->year + 1 }}" class="form-control" id="vehicle_year" name="year">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-2">
                                <label for="vehicle_color" class="form-label">Color</label>
                                <input type="text" class="form-control" id="vehicle_color" name="color" maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-2">
                                <label for="vehicle_engine_type" class="form-label">Engine Type</label>
                                <input type="text" class="form-control" id="vehicle_engine_type" name="engine_type" maxlength="80">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-3">
                                <label for="vehicle_odometer" class="form-label">Odometer</label>
                                <input type="number" min="0" step="0.1" class="form-control" id="vehicle_odometer" name="odometer">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label d-block">Default Vehicle</label>
                                <input type="hidden" name="is_default" value="0">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="vehicle_is_default" name="is_default" value="1">
                                    <label class="form-check-label" for="vehicle_is_default">Default</label>
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>

                            <div class="col-md-12">
                                <label for="vehicle_notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="vehicle_notes" name="notes" rows="4" maxlength="2000"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="vehicleSubmitBtn" data-create-text="Save Vehicle" data-update-text="Update Vehicle">
                            Save Vehicle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.20.0/jquery.validate.min.js"></script>
    <script>
        window.vehicleListingUrl = @json($listingUrl);
        window.vehicleEditUrlTemplate = @json($editUrlTemplate);
        window.customerDropdownUrl = @json($customersDropdownUrl);
        window.vehicleDropdownUrl = @json($vehiclesDropdownUrl);
    </script>
    <script src="{{ asset('assets/js/tenant/e-com/vehicles.js') }}"></script>
@endsection
