@extends('layouts.app')

@section('title', 'Customers')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Customers</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Ecommerce</li>
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

    <div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <form id="customerForm" action="{{ route('tenant.ecommerce.customers.save') }}" method="POST" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="customer_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="customerModalLabel">Add Customer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="customer_type" class="form-label">Customer Type <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select id="customer_type" name="customer_type" class="form-select select2" data-placeholder="Select a customer type" data-dropdown-parent="#customerModal">
                                        @foreach($customerTypes as $type => $label)
                                            <option value="{{ $type }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="customer_name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="customer_name" name="name" maxlength="150">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="customer_phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="customer_phone" name="phone" maxlength="30">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-4">
                                <label for="customer_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="customer_email" name="email" maxlength="150">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="customer_date_of_birth" class="form-label">Date Of Birth</label>
                                <input type="date" class="form-control" id="customer_date_of_birth" name="date_of_birth">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="customer_last_visit_at" class="form-label">Last Visit</label>
                                <input type="datetime-local" class="form-control" id="customer_last_visit_at" name="last_visit_at">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-12">
                                <label for="customer_address" class="form-label">Address</label>
                                <textarea class="form-control" id="customer_address" name="address" rows="3" maxlength="1000"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-12">
                                <label for="customer_notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="customer_notes" name="notes" rows="3" maxlength="2000"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-3">
                                <label for="customer_total_visits" class="form-label">Total Visits</label>
                                <input type="number" min="0" class="form-control" id="customer_total_visits" name="total_visits" value="0">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="customer_lifetime_value" class="form-label">Lifetime Value</label>
                                <input type="number" step="0.01" class="form-control" id="customer_lifetime_value" name="lifetime_value" value="0.00">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="customer_loyalty_points_balance" class="form-label">Loyalty Points</label>
                                <input type="number" min="0" class="form-control" id="customer_loyalty_points_balance" name="loyalty_points_balance" value="0">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="customer_credit_balance" class="form-label">Credit Balance</label>
                                <input type="number" step="0.01" class="form-control" id="customer_credit_balance" name="credit_balance" value="0.00">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="customerSubmitBtn" data-create-text="Save Customer" data-update-text="Update Customer">
                            Save Customer
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
        window.customerListingUrl = @json($listingUrl);
        window.customerEditUrlTemplate = @json($editUrlTemplate);
        window.customerVehicleIndexUrlTemplate = @json($vehicleIndexUrlTemplate);
    </script>
    <script src="{{ asset('assets/js/tenant/e-com/customers.js') }}"></script>
@endsection
