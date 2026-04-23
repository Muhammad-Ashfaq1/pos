@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Products</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Ecommerce</li>
                    <li class="breadcrumb-item active" aria-current="page">Products</li>
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
                        <label for="product_filter_category" class="form-label">Category</label>
                        <select
                            id="product_filter_category"
                            class="form-select category-select2"
                            data-placeholder="All categories"
                            data-allow-clear="true"
                        >
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="product_filter_sub_category" class="form-label">Sub Category</label>
                        <select
                            id="product_filter_sub_category"
                            class="form-select subcategory-select2"
                            data-placeholder="All sub categories"
                            data-allow-clear="true"
                        >
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="product_status" class="form-label">Status</label>
                        <select
                            id="product_status"
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
                        <label for="product_type_filter" class="form-label">Product Type</label>
                        <select
                            id="product_type_filter"
                            class="form-select filter-control select2"
                            data-placeholder="All product types"
                            data-allow-clear="false"
                        >
                            <option value="">All</option>
                            @foreach($productTypes as $type => $label)
                                <option value="{{ $type }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="product_track_inventory" class="form-label">Inventory Tracking</label>
                        <select
                            id="product_track_inventory"
                            class="form-select filter-control select2"
                            data-placeholder="All tracking types"
                            data-allow-clear="false"
                            data-minimum-results-for-search="Infinity"
                        >
                            <option value="">All</option>
                            <option value="1">Tracked</option>
                            <option value="0">Not Tracked</option>
                        </select>
                    </div>
                    <div>
                        <label for="product_sort" class="form-label">Sort By</label>
                        <select
                            id="product_sort"
                            class="form-select filter-control select2"
                            data-placeholder="Sort products"
                            data-allow-clear="false"
                            data-minimum-results-for-search="Infinity"
                        >
                            <option value="latest">Latest</option>
                            <option value="name">Name A-Z</option>
                            <option value="price_low_high">Price Low-High</option>
                            <option value="stock_low_high">Stock Low-High</option>
                        </select>
                    </div>
                </div>
            </div>

            @can('create', \App\Models\Product::class)
                <button
                    type="button"
                    class="btn btn-primary"
                    id="addProductBtn"
                    data-bs-toggle="modal"
                    data-bs-target="#productModal"
                >
                    <i class="ti tabler-plus me-1"></i>
                    Add Product
                </button>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="products-datatables table">
                <thead class="bg-label-primary">
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Sub Category</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>SKU</th>
                        <th>Brand</th>
                        <th>Sale Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <form id="productForm" action="{{ route('tenant.ecommerce.products.save') }}" method="POST" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="product_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalLabel">Add Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="product_category_id" class="form-label">Category</label>
                                <div class="position-relative">
                                    <select
                                        id="product_category_id"
                                        name="category_id"
                                        class="form-select category-select2"
                                        data-placeholder="Select a category"
                                        data-allow-clear="true"
                                        data-dropdown-parent="#productModal"
                                    ></select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="product_sub_category_id" class="form-label">Sub Category</label>
                                <div class="position-relative">
                                    <select
                                        id="product_sub_category_id"
                                        name="sub_category_id"
                                        class="form-select subcategory-select2"
                                        data-placeholder="Select a sub category"
                                        data-allow-clear="true"
                                        data-dropdown-parent="#productModal"
                                    ></select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="product_type" class="form-label">Product Type <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select id="product_type" name="product_type" class="form-select select2" data-placeholder="Select a product type" data-dropdown-parent="#productModal">
                                        @foreach($productTypes as $type => $label)
                                            <option value="{{ $type }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="product_name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="product_name" name="name" maxlength="150">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="product_sku" class="form-label">SKU</label>
                                <input type="text" class="form-control" id="product_sku" name="sku" maxlength="80">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="product_barcode" class="form-label">Barcode</label>
                                <input type="text" class="form-control" id="product_barcode" name="barcode" maxlength="80">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-4">
                                <label for="product_brand" class="form-label">Brand</label>
                                <input type="text" class="form-control" id="product_brand" name="brand" maxlength="120">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="product_unit" class="form-label">Unit</label>
                                <input type="text" class="form-control" id="product_unit" name="unit" maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-block">Status</label>
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="product_is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="product_is_active">Active</label>
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>

                            <div class="col-md-12">
                                <label for="product_description" class="form-label">Description</label>
                                <textarea class="form-control" id="product_description" name="description" rows="3" maxlength="2000"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-3">
                                <label for="product_cost_price" class="form-label">Cost Price <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" id="product_cost_price" name="cost_price" value="0.00">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="product_sale_price" class="form-label">Sale Price <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" id="product_sale_price" name="sale_price" value="0.00">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="product_tax_percentage" class="form-label">Tax %</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control" id="product_tax_percentage" name="tax_percentage">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label d-block">Track Inventory</label>
                                <input type="hidden" name="track_inventory" value="0">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="product_track_inventory_toggle" name="track_inventory" value="1" checked>
                                    <label class="form-check-label" for="product_track_inventory_toggle">Enabled</label>
                                </div>
                                <div class="invalid-feedback d-block"></div>
                            </div>

                            <div class="col-md-3">
                                <label for="product_opening_stock" class="form-label">Opening Stock</label>
                                <input type="number" step="0.001" min="0" class="form-control inventory-field" id="product_opening_stock" name="opening_stock" value="0">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="product_current_stock" class="form-label">Current Stock</label>
                                <input type="number" step="0.001" min="0" class="form-control inventory-field" id="product_current_stock" name="current_stock" value="0">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="product_minimum_stock_level" class="form-label">Minimum Stock</label>
                                <input type="number" step="0.001" min="0" class="form-control inventory-field" id="product_minimum_stock_level" name="minimum_stock_level" value="0">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="product_reorder_level" class="form-label">Reorder Level</label>
                                <input type="number" step="0.001" min="0" class="form-control inventory-field" id="product_reorder_level" name="reorder_level" value="0">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="productSubmitBtn" data-create-text="Save Product" data-update-text="Update Product">
                            Save Product
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
        window.productListingUrl = @json($listingUrl);
        window.categoryDropdownUrl = @json($categoriesDropdownUrl);
        window.subCategoryDropdownUrl = @json($subCategoriesDropdownUrl);
        window.productTypes = @json($productTypes);
    </script>
    <script src="{{ asset('assets/js/tenant/e-com/products.js') }}"></script>
@endsection
