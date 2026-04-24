@extends('layouts.app')

@section('title', 'Services')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Services</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Catalog &amp; Services</li>
                    <li class="breadcrumb-item active" aria-current="page">Services</li>
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
                        <label for="service_filter_category" class="form-label">Category</label>
                        <select
                            id="service_filter_category"
                            class="form-select category-select2"
                            data-placeholder="All categories"
                            data-allow-clear="true"
                        >
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="service_status" class="form-label">Status</label>
                        <select
                            id="service_status"
                            class="form-select filter-control select2"
                            data-placeholder="All statuses"
                            data-allow-clear="false"
                            data-minimum-results-for-search="Infinity"
                        >
                            <option value="">All</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="service_requires_technician_filter" class="form-label">Technician</label>
                        <select
                            id="service_requires_technician_filter"
                            class="form-select filter-control select2"
                            data-placeholder="All services"
                            data-allow-clear="false"
                            data-minimum-results-for-search="Infinity"
                        >
                            <option value="">All</option>
                            <option value="1">Required</option>
                            <option value="0">Not Required</option>
                        </select>
                    </div>
                    <div>
                        <label for="service_sort" class="form-label">Sort By</label>
                        <select
                            id="service_sort"
                            class="form-select filter-control select2"
                            data-placeholder="Sort services"
                            data-allow-clear="false"
                            data-minimum-results-for-search="Infinity"
                        >
                            <option value="latest">Latest</option>
                            <option value="category">Category A-Z</option>
                            <option value="name">Name A-Z</option>
                            <option value="price_low_high">Price Low-High</option>
                            <option value="duration_low_high">Duration Low-High</option>
                        </select>
                    </div>
                </div>
            </div>

            @can('create', \App\Models\Service::class)
                <button
                    type="button"
                    class="btn btn-primary"
                    id="addServiceBtn"
                    data-bs-toggle="modal"
                    data-bs-target="#serviceModal"
                >
                    <i class="ti tabler-plus me-1"></i>
                    Add Service
                </button>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="services-datatables table">
                <thead class="bg-label-primary">
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Service</th>
                        <th>Code</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Mapped Products</th>
                        <th>Technician</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <form id="serviceForm" action="{{ route('tenant.ecommerce.services.save') }}" method="POST" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="service_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="serviceModalLabel">Add Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="service_category_id" class="form-label">Category</label>
                                <div class="position-relative">
                                    <select
                                        id="service_category_id"
                                        name="category_id"
                                        class="form-select category-select2"
                                        data-placeholder="Select a category"
                                        data-allow-clear="true"
                                        data-dropdown-parent="#serviceModal"
                                    ></select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="service_name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="service_name" name="name" maxlength="150">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="service_code" class="form-label">Code</label>
                                <input type="text" class="form-control" id="service_code" name="code" maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-12">
                                <label for="service_description" class="form-label">Description</label>
                                <textarea class="form-control" id="service_description" name="description" rows="3" maxlength="2000"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-3">
                                <label for="service_standard_price" class="form-label">Standard Price <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" id="service_standard_price" name="standard_price" value="0.00">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="service_estimated_duration_minutes" class="form-label">Duration (Minutes)</label>
                                <input type="number" min="0" class="form-control" id="service_estimated_duration_minutes" name="estimated_duration_minutes">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="service_tax_percentage" class="form-label">Tax %</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control" id="service_tax_percentage" name="tax_percentage">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="service_reminder_interval_days" class="form-label">Reminder Days</label>
                                <input type="number" min="0" class="form-control" id="service_reminder_interval_days" name="reminder_interval_days">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-3">
                                <label for="service_mileage_interval" class="form-label">Mileage Interval</label>
                                <input type="number" min="0" class="form-control" id="service_mileage_interval" name="mileage_interval">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label d-block">Technician Required</label>
                                <input type="hidden" name="requires_technician" value="0">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="service_requires_technician" name="requires_technician" value="1">
                                    <label class="form-check-label" for="service_requires_technician">Required</label>
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label d-block">Status</label>
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="service_is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="service_is_active">Active</label>
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>
                        </div>

                        <div class="border rounded p-3 mt-4">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                                <div>
                                    <h6 class="mb-1">Product Consumption Mapping</h6>
                                    <p class="text-muted small mb-0">Map inventory items consumed when this service is billed later in POS.</p>
                                </div>
                                <button type="button" class="btn btn-sm btn-label-primary" id="addServiceMappingRowBtn">
                                    <i class="ti tabler-plus me-1"></i>
                                    Add Product
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0" id="serviceMappingsTable">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 280px;">Product</th>
                                            <th style="min-width: 140px;">Quantity</th>
                                            <th style="min-width: 160px;">Unit</th>
                                            <th style="min-width: 120px;">Required</th>
                                            <th class="text-center" style="width: 60px;">#</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div class="invalid-feedback d-block mt-2" id="service_mappings_feedback"></div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="serviceSubmitBtn" data-create-text="Save Service" data-update-text="Update Service">
                            Save Service
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
        window.serviceListingUrl = @json($listingUrl);
        window.serviceEditUrlTemplate = @json($editUrlTemplate);
        window.categoryDropdownUrl = @json($categoriesDropdownUrl);
        window.serviceProductDropdownUrl = @json($productsDropdownUrl);
    </script>
    <script src="{{ asset('assets/js/tenant/e-com/services.js') }}"></script>
@endsection
