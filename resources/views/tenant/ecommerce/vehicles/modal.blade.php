
@php
$vehicleTypes = $vehicleTypes ?? [];
@endphp

<div class="modal fade" id="vehicleModal" tabindex="-1" aria-labelledby="vehicleModalLabel" aria-hidden="true"
data-bs-backdrop="static" data-bs-keyboard="false">
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
                        <label for="vehicle_customer_entry_mode" class="form-label">Customer Mode <span
                                class="text-danger">*</span></label>
                        <div class="position-relative">
                            <select id="vehicle_customer_entry_mode" name="customer_entry_mode"
                                class="form-select select2" data-placeholder="Select customer mode"
                                data-dropdown-parent="#vehicleModal" data-allow-clear="false"
                                data-minimum-results-for-search="Infinity">
                                <option value="existing">Select Existing Customer</option>
                                <option value="walk_in">Walk-in / Quick Entry</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-md-4 customer-mode-section" data-mode="existing">
                        <label for="vehicle_customer_id" class="form-label">Customer <span
                                class="text-danger">*</span></label>
                        <div class="position-relative">
                            <select id="vehicle_customer_id" name="customer_id" class="form-select customer-select2"
                                data-placeholder="Select a customer" data-allow-clear="false"
                                data-dropdown-parent="#vehicleModal"></select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-md-8 customer-mode-section" data-mode="existing">
                        <div class="alert alert-label-primary mb-0">
                            Use this option when the customer already exists and this vehicle should link directly to
                            that record.
                        </div>
                    </div>

                    <div class="col-md-6 customer-mode-section d-none" data-mode="walk_in">
                        <label for="inline_customer_name" class="form-label">Customer Name</label>
                        <input type="text" class="form-control" id="inline_customer_name" name="inline_customer_name"
                            maxlength="150" placeholder="Walk-in Customer">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 customer-mode-section d-none" data-mode="walk_in">
                        <label for="inline_customer_phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="inline_customer_phone" name="inline_customer_phone"
                            maxlength="30" placeholder="Optional">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 customer-mode-section d-none" data-mode="walk_in">
                        <label for="inline_customer_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="inline_customer_email" name="inline_customer_email"
                            maxlength="150" placeholder="Optional">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 customer-mode-section d-none" data-mode="walk_in">
                        <label for="inline_customer_address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="inline_customer_address"
                            name="inline_customer_address" maxlength="1000" placeholder="Optional">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-12 customer-mode-section d-none" data-mode="walk_in">
                        <input type="hidden" name="save_walk_in_as_customer" value="0">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="save_walk_in_as_customer"
                                name="save_walk_in_as_customer" value="1" checked>
                            <label class="form-check-label" for="save_walk_in_as_customer">Save this walk-in as
                                customer</label>
                        </div>
                        <div class="form-text">
                            If unchecked, the system will still create a safe internal walk-in link for this vehicle.
                        </div>
                        <div class="invalid-feedback d-block"></div>
                    </div>

                    <div class="col-md-4">
                        <label for="vehicle_plate_number" class="form-label">Plate Number <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control text-uppercase" id="vehicle_plate_number"
                            name="plate_number" maxlength="50">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4">
                        <label for="vehicle_registration_number" class="form-label">Registration Number</label>
                        <input type="text" class="form-control text-uppercase" id="vehicle_registration_number"
                            name="registration_number" maxlength="80">
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
                        <input type="number" min="1900" max="{{ now()->year + 1 }}" class="form-control"
                            id="vehicle_year" name="year">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-2">
                        <label for="vehicle_color" class="form-label">Color</label>
                        <input type="text" class="form-control" id="vehicle_color" name="color" maxlength="50">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-2">
                        <label for="vehicle_engine_type" class="form-label">Engine Type</label>
                        <input type="text" class="form-control" id="vehicle_engine_type" name="engine_type"
                            maxlength="80">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-3">
                        <label for="vehicle_odometer" class="form-label">Odometer</label>
                        <input type="number" min="0" step="0.1" class="form-control" id="vehicle_odometer"
                            name="odometer">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label d-block">Default Vehicle</label>
                        <input type="hidden" name="is_default" value="0">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="vehicle_is_default"
                                name="is_default" value="1">
                            <label class="form-check-label" for="vehicle_is_default">Default</label>
                        </div>
                        <div class="invalid-feedback d-block"></div>
                    </div>

                    <div class="col-md-12">
                        <label for="vehicle_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="vehicle_notes" name="notes" rows="4"
                            maxlength="2000"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="vehicleSubmitBtn" data-create-text="Save Vehicle"
                    data-update-text="Update Vehicle">
                    Save Vehicle
                </button>
            </div>
        </form>
    </div>
</div>
</div>
