@extends('layouts.app')

@section('title', 'Sub Categories')

@push('styles')
    @include('partials.pos-listing-assets')
@endpush

@section('content')
    <div class="pos-listing">
        <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
            <div class="pos-listing-toolbar">
                <h4 class="pos-listing-title">Sub Categories</h4>
                <div class="pos-listing-search-slot" aria-hidden="true"></div>
                <div class="pos-listing-toolbar-tools">
                    <div class="pos-listing-toolbar-actions" id="subCategoryTableActions">
                        <div class="dropdown">
                            <button
                                type="button"
                                class="btn btn-label-secondary btn-icon"
                                data-bs-toggle="dropdown"
                                data-bs-auto-close="outside"
                                aria-expanded="false"
                                title="Filters"
                            >
                                <i class="ti tabler-filter"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 320px;">
                                <div class="mb-3">
                                    <label for="subcategory_filter_category" class="form-label">Category</label>
                                    <select
                                        id="subcategory_filter_category"
                                        class="form-select category-select2"
                                        data-placeholder="All categories"
                                        data-allow-clear="true"
                                    >
                                        <option value=""></option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="subcategory_status" class="form-label">Status</label>
                                    <select
                                        id="subcategory_status"
                                        class="form-select filter-control select2"
                                        data-allow-clear="false"
                                    >
                                        <option value="">All</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="subcategory_sort" class="form-label">Sort By</label>
                                    <select
                                        id="subcategory_sort"
                                        class="form-select filter-control select2"
                                        data-allow-clear="false"
                                    >
                                        <option value="latest">Latest</option>
                                        <option value="category">Category A-Z</option>
                                        <option value="name">Name A-Z</option>
                                        <option value="sort_order">Sort Order Low-High</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        @can('create', \App\Models\SubCategory::class)
                            <button
                                type="button"
                                class="btn btn-primary"
                                id="addSubCategoryBtn"
                                data-bs-toggle="modal"
                                data-bs-target="#subCategoryModal"
                            >
                                <i class="ti tabler-plus me-1"></i>
                                Add Sub Category
                            </button>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="card-datatable table-responsive pos-listing-table pt-0">
                <table class="subcategories-datatables table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Category</th>
                            <th>Sub Category</th>
                            <th>Slug</th>
                            <th>Code</th>
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

        <div class="modal fade pos-listing-modal" id="subCategoryModal" tabindex="-1" aria-labelledby="subCategoryModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form id="subCategoryForm" action="{{ route('tenant.ecommerce.subcategories.save') }}" method="POST" novalidate>
                        @csrf
                        <input type="hidden" name="id" id="sub_category_id">

                        <div class="modal-header">
                            <h5 class="modal-title" id="subCategoryModalLabel">Add Sub Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <select
                                            id="category_id"
                                            name="category_id"
                                            class="form-select category-select2"
                                            data-placeholder="Select a category"
                                            data-allow-clear="false"
                                            data-dropdown-parent="#subCategoryModal"
                                            data-active-only="true"
                                        ></select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="subcategory_name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="subcategory_name" name="name" maxlength="150">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="subcategory_code" class="form-label">Code</label>
                                    <input type="text" class="form-control" id="subcategory_code" name="code" maxlength="50">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="subcategory_sort_order" class="form-label">Sort Order</label>
                                    <input type="number" min="0" class="form-control" id="subcategory_sort_order" name="sort_order" value="0">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-12">
                                    <label for="subcategory_description" class="form-label">Description</label>
                                    <textarea class="form-control" id="subcategory_description" name="description" rows="4" maxlength="1000"></textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block">Status</label>
                                    <input type="hidden" name="is_active" value="0">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="subcategory_is_active" name="is_active" value="1" checked>
                                        <label class="form-check-label" for="subcategory_is_active">Active</label>
                                    </div>
                                    <div class="invalid-feedback d-block"></div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="subCategorySubmitBtn" data-create-text="Save Sub Category" data-update-text="Update Sub Category">
                                Save Sub Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.20.0/jquery.validate.min.js"></script>
    <script>
        window.subCategoryListingUrl = @json($listingUrl);
        window.categoryDropdownUrl = @json($categoriesDropdownUrl);
    </script>
    <script src="{{ asset('assets/js/pos-listing-toolbar.js') }}?v={{ filemtime(public_path('assets/js/pos-listing-toolbar.js')) }}"></script>
    <script src="{{ asset('assets/js/tenant/e-com/subcategories.js') }}?v={{ filemtime(public_path('assets/js/tenant/e-com/subcategories.js')) }}"></script>
@endsection
