@extends('layouts.app')

@section('title', 'Service Categories')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-2" id="serviceCategoryTableActions">
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
                <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 260px;">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select
                            id="status"
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
                    <div>
                        <label for="sort" class="form-label">Sort By</label>
                        <select
                            id="sort"
                            class="form-select filter-control select2"
                            data-placeholder="Sort service categories"
                            data-allow-clear="false"
                            data-minimum-results-for-search="Infinity"
                        >
                            <option value="latest">Latest</option>
                            <option value="name">Name A-Z</option>
                            <option value="sort_order">Sort Order Low-High</option>
                        </select>
                    </div>
                </div>
            </div>

            @can('create', \App\Models\ServiceCategory::class)
                <button
                    type="button"
                    class="btn btn-primary"
                    id="addServiceCategoryBtn"
                    data-bs-toggle="modal"
                    data-bs-target="#serviceCategoryModal"
                >
                    <i class="ti tabler-plus me-1"></i>
                    Add Service Category
                </button>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="service-categories-datatables table">
                <thead class="bg-label-primary">
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="serviceCategoryModal" tabindex="-1" aria-labelledby="serviceCategoryModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="serviceCategoryForm" action="{{ route('tenant.ecommerce.service-categories.save') }}" method="POST" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="service_category_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="serviceCategoryModalLabel">Add Service Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" maxlength="150" autocomplete="off">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="slug"
                                    name="slug"
                                    maxlength="170"
                                    readonly
                                    tabindex="-1"
                                    autocomplete="off"
                                >
                                <div class="invalid-feedback"></div>
                                <div class="form-text">Auto generated from name.</div>
                            </div>
                            <div class="col-md-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" maxlength="1000"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" min="0" class="form-control" id="sort_order" name="sort_order" value="0">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label d-block">Status</label>
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="serviceCategorySubmitBtn" data-create-text="Save Service Category" data-update-text="Update Service Category">
                            Save Service Category
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
        window.serviceCategoryListingUrl = @json($listingUrl);
    </script>
    <script src="{{ asset('assets/js/tenant/e-com/service-categories.js') }}?v={{ filemtime(public_path('assets/js/tenant/e-com/service-categories.js')) }}"></script>
@endsection
