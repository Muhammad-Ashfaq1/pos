@extends('layouts.app')

@section('title', 'Discounts')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Discounts & Promotions</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Ecommerce</li>
                    <li class="breadcrumb-item active" aria-current="page">Discounts</li>
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
                        <label for="discount_status" class="form-label">Status</label>
                        <select
                            id="discount_status"
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
                        <label for="discount_type_filter" class="form-label">Discount Type</label>
                        <select
                            id="discount_type_filter"
                            class="form-select filter-control select2"
                            data-placeholder="All discount types"
                            data-allow-clear="false"
                            data-minimum-results-for-search="Infinity"
                        >
                            <option value="">All</option>
                            @foreach($discountTypes as $type => $label)
                                <option value="{{ $type }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="discount_applies_to_filter" class="form-label">Applies To</label>
                        <select
                            id="discount_applies_to_filter"
                            class="form-select filter-control select2"
                            data-placeholder="All targets"
                            data-allow-clear="false"
                            data-minimum-results-for-search="Infinity"
                        >
                            <option value="">All</option>
                            @foreach($appliesToOptions as $appliesTo => $label)
                                <option value="{{ $appliesTo }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="discount_sort" class="form-label">Sort By</label>
                        <select
                            id="discount_sort"
                            class="form-select filter-control select2"
                            data-placeholder="Sort discounts"
                            data-allow-clear="false"
                            data-minimum-results-for-search="Infinity"
                        >
                            <option value="latest">Latest</option>
                            <option value="name">Name A-Z</option>
                            <option value="value_high_low">Value High-Low</option>
                            <option value="starts_at">Start Date</option>
                        </select>
                    </div>
                </div>
            </div>

            @can('create', \App\Models\Discount::class)
                <button
                    type="button"
                    class="btn btn-primary"
                    id="addDiscountBtn"
                    data-bs-toggle="modal"
                    data-bs-target="#discountModal"
                >
                    <i class="ti tabler-plus me-1"></i>
                    Add Discount
                </button>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="discounts-datatables table">
                <thead class="bg-label-primary">
                    <tr>
                        <th>#</th>
                        <th>Discount</th>
                        <th>Type</th>
                        <th>Applies To</th>
                        <th>Value</th>
                        <th>Rules</th>
                        <th>Schedule</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="discountModal" tabindex="-1" aria-labelledby="discountModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <form id="discountForm" action="{{ route('tenant.ecommerce.discounts.save') }}" method="POST" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="discount_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="discountModalLabel">Add Discount</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="discount_name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="discount_name" name="name" maxlength="150">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="discount_code" class="form-label">Code</label>
                                <input type="text" class="form-control text-uppercase" id="discount_code" name="code" maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="discount_type" class="form-label">Discount Type <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select id="discount_type" name="discount_type" class="form-select select2" data-placeholder="Select a discount type" data-dropdown-parent="#discountModal">
                                        @foreach($discountTypes as $type => $label)
                                            <option value="{{ $type }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="discount_applies_to" class="form-label">Applies To <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select id="discount_applies_to" name="applies_to" class="form-select select2" data-placeholder="Select discount target" data-dropdown-parent="#discountModal">
                                        @foreach($appliesToOptions as $appliesTo => $label)
                                            <option value="{{ $appliesTo }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="discount_value" class="form-label">Value <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="discount_value" name="value">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="discount_max_discount_amount" class="form-label">Max Discount Amount</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="discount_max_discount_amount" name="max_discount_amount">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="discount_starts_at" class="form-label">Starts At</label>
                                <input type="datetime-local" class="form-control" id="discount_starts_at" name="starts_at">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="discount_ends_at" class="form-label">Ends At</label>
                                <input type="datetime-local" class="form-control" id="discount_ends_at" name="ends_at">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-4">
                                <label for="discount_usage_limit" class="form-label">Usage Limit</label>
                                <input type="number" min="1" class="form-control" id="discount_usage_limit" name="usage_limit">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-12">
                                <label for="discount_description" class="form-label">Description</label>
                                <textarea class="form-control" id="discount_description" name="description" rows="3" maxlength="2000"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label d-block">Status</label>
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="discount_is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="discount_is_active">Active</label>
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label d-block">Combinable</label>
                                <input type="hidden" name="is_combinable" value="0">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="discount_is_combinable" name="is_combinable" value="1" checked>
                                    <label class="form-check-label" for="discount_is_combinable">Allowed</label>
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label d-block">Requires Reason</label>
                                <input type="hidden" name="requires_reason" value="0">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="discount_requires_reason" name="requires_reason" value="1">
                                    <label class="form-check-label" for="discount_requires_reason">Required</label>
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label d-block">Manager Approval</label>
                                <input type="hidden" name="requires_manager_approval" value="0">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="discount_requires_manager_approval" name="requires_manager_approval" value="1">
                                    <label class="form-check-label" for="discount_requires_manager_approval">Required</label>
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="discountSubmitBtn" data-create-text="Save Discount" data-update-text="Update Discount">
                            Save Discount
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
        window.discountListingUrl = @json($listingUrl);
        window.discountEditUrlTemplate = @json($editUrlTemplate);
    </script>
    <script src="{{ asset('assets/js/tenant/e-com/discounts.js') }}"></script>
@endsection
