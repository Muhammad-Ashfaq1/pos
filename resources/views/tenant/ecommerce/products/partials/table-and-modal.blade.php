@php
    $isEmployeeSurface = ! empty($isEmployeeSurface);
    $renderTable = $renderTable ?? true;
    $renderModal = $renderModal ?? true;
@endphp

@if ($renderTable)
    @if ($isEmployeeSurface)
        <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
            <div class="card-datatable table-responsive pos-listing-table pt-0">
                <table class="products-datatables table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
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
    @else
        <div class="card-datatable table-responsive pos-listing-table pt-0">
            <table class="products-datatables table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
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
    @endif
@endif

@if ($renderModal)
<div class="modal fade pos-listing-modal" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form id="productForm" action="{{ $saveUrl }}" method="POST" novalidate>
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
                                <select id="product_type" name="product_type_id" class="form-select select2" data-placeholder="Select a product type" data-dropdown-parent="#productModal">
                                    @foreach($productTypes as $type => $label)
                                        <option value="{{ $type }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="product_discount_id" class="form-label">Item Discount</label>
                            <div class="position-relative">
                                <select
                                    id="product_discount_id"
                                    name="discount_id"
                                    class="form-select discount-select2"
                                    data-placeholder="Select an item discount"
                                    data-allow-clear="true"
                                    data-dropdown-parent="#productModal"
                                >
                                    <option value=""></option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="product_service_id" class="form-label">Service</label>
                            <div class="position-relative">
                                <select
                                    id="product_service_id"
                                    name="service_id"
                                    class="form-select service-select2"
                                    data-placeholder="Select a service (optional)"
                                    data-allow-clear="true"
                                    data-dropdown-parent="#productModal"
                                >
                                    <option value=""></option>
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

                        <div class="col-12">
                            <x-media.dropzone
                                id="product_images_dropzone"
                                label="Product Images"
                                inputName="images[]"
                                primaryInputName="primary_image_ref"
                                removedInputName="removed_image_ids[]"
                            />
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
                            <input type="number" step="1" min="0" inputmode="numeric" pattern="[0-9]*" class="form-control inventory-field" id="product_opening_stock" name="opening_stock" value="0">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3" data-stock-current-wrapper>
                            <label for="product_current_stock" class="form-label">Current Stock</label>
                            <input type="number" step="1" min="0" inputmode="numeric" pattern="[0-9]*" class="form-control inventory-field" id="product_current_stock" name="current_stock" value="0">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 d-none" data-stock-adjustment-wrapper>
                            <label for="product_stock_adjustment_mode" class="form-label">
                                Adjust Current Stock
                                <small class="text-muted ms-1">
                                    (Now: <strong id="product_stock_now">0</strong>
                                    → New: <strong id="product_stock_preview" class="text-primary">0</strong>)
                                </small>
                            </label>
                            <div class="d-flex gap-2">
                                <select
                                    id="product_stock_adjustment_mode"
                                    name="stock_adjustment_mode"
                                    class="form-select"
                                    style="max-width: 140px;">
                                    <option value="none" selected>No change</option>
                                    <option value="add">Add</option>
                                    <option value="subtract">Subtract</option>
                                </select>

                                <div class="input-group flex-grow-1">
                                    <button type="button" class="btn btn-outline-secondary" id="product_stock_adjustment_minus" disabled>
                                        <i class="ti tabler-minus"></i>
                                    </button>
                                    <input
                                        type="number"
                                        step="1"
                                        min="0"
                                        max="9999"
                                        class="form-control text-center"
                                        id="product_stock_adjustment_quantity"
                                        name="stock_adjustment_quantity"
                                        value="0"
                                        disabled>
                                    <button type="button" class="btn btn-outline-secondary" id="product_stock_adjustment_plus" disabled>
                                        <i class="ti tabler-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="invalid-feedback d-block" id="product_stock_adjustment_error"></div>
                        </div>
                        <div class="col-md-3">
                            <label for="product_minimum_stock_level" class="form-label">Minimum Stock</label>
                            <input type="number" step="1" min="0" inputmode="numeric" pattern="[0-9]*" class="form-control inventory-field" id="product_minimum_stock_level" name="minimum_stock_level" value="0">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3">
                            <label for="product_reorder_level" class="form-label">Reorder Level</label>
                            <input type="number" step="1" min="0" inputmode="numeric" pattern="[0-9]*" class="form-control inventory-field" id="product_reorder_level" name="reorder_level" value="0">
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
@endif
