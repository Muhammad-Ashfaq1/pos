@extends('layouts.app')

@section('title', 'Plans')

@push('styles')
    @include('partials.pos-listing-assets')
@endpush

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card bg-label-primary">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <span class="badge bg-primary mb-3">SaaS Subscription Desk</span>
                        <h3 class="mb-2">Manage SaaS Subscription Plans</h3>
                        <p class="text-muted mb-0">
                            Configure monthly and yearly pricing plans, trial periods, and feature packages for all registered shops.
                        </p>
                    </div>
                    <div class="col-lg-4 text-center d-none d-lg-block">
                        <img src="{{ asset('assets/img/illustrations/rocket.png') }}" alt="Subscription plans" class="img-fluid" style="max-height: 180px;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="pos-listing">
            <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
                <div class="pos-listing-toolbar">
                    <h4 class="pos-listing-title">Subscription Plans</h4>
                    <div class="pos-listing-search-slot" aria-hidden="true"></div>
                    <div class="pos-listing-toolbar-tools">
                        <div class="pos-listing-toolbar-actions" id="planTableActions">
                            <div class="dropdown">
                                <button type="button" class="btn btn-label-secondary btn-icon" data-bs-toggle="dropdown" title="Filters">
                                    <i class="ti tabler-filter"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 260px;">
                                    <div class="mb-3">
                                        <label for="planStatusFilter" class="form-label">Status</label>
                                        <select id="planStatusFilter" class="form-select filter-control select2" data-placeholder="All statuses" data-minimum-results-for-search="Infinity">
                                            <option value="">All</option>
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="planSortFilter" class="form-label">Sort By</label>
                                        <select id="planSortFilter" class="form-select filter-control select2" data-placeholder="Sort plans" data-minimum-results-for-search="Infinity">
                                            <option value="latest">Latest</option>
                                            <option value="name">Name A-Z</option>
                                            <option value="duration">Duration</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary" id="addPlanBtn" data-bs-toggle="modal" data-bs-target="#planModal">
                                <i class="ti tabler-plus me-1"></i> Add Plan
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-datatable table-responsive pos-listing-table pt-0">
                    <table class="plans-datatables table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Plan</th>
                                <th>Duration</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="modal fade pos-listing-modal" id="planModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <form id="planForm" action="{{ route('admin.plans.save') }}" method="POST" novalidate>
                            @csrf
                            <input type="hidden" name="id" id="plan_id">
                            <div class="modal-header">
                                <h5 class="modal-title" id="planModalLabel">Add Plan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="plan_name" class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="plan_name" name="name" maxlength="150">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="plan_duration_type" class="form-label">Duration <span class="text-danger">*</span></label>
                                        <select
                                            id="plan_duration_type"
                                            name="duration_type"
                                            class="form-select select2 modal-select2"
                                            data-dropdown-parent="#planModal"
                                            data-placeholder="Search duration"
                                        >
                                            @foreach($durations as $duration)
                                                <option value="{{ $duration->value }}">{{ $duration->label() }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="plan_price" class="form-label">Price</label>
                                        <input type="number" min="0" step="0.01" class="form-control" id="plan_price" name="price">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label d-block">Status</label>
                                        <input type="hidden" name="is_active" value="0">
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" id="plan_is_active" name="is_active" value="1" checked>
                                            <label class="form-check-label" for="plan_is_active">Active</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="plan_description" class="form-label">Description</label>
                                        <textarea class="form-control" id="plan_description" name="description" rows="3" maxlength="2000"></textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="planSubmitBtn" data-create-text="Save Plan" data-update-text="Update Plan">Save Plan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>window.planListingUrl = @json($listingUrl);</script>
    <script src="{{ asset('assets/js/pos-listing-toolbar.js') }}?v={{ filemtime(public_path('assets/js/pos-listing-toolbar.js')) }}"></script>
    <script src="{{ asset('assets/js/admin/plans.js') }}?v={{ filemtime(public_path('assets/js/admin/plans.js')) }}"></script>
@endsection
