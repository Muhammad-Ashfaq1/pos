{{-- Shared product filters + Add button (employee header actions / admin toolbar) --}}
@php
    $showInventoryFilter = $showInventoryFilter ?? false;
@endphp

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
                data-allow-clear="false"
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
        @if ($showInventoryFilter)
            <div class="mb-3">
                <label for="product_track_inventory" class="form-label">Inventory Tracking</label>
                <select
                    id="product_track_inventory"
                    class="form-select filter-control select2"
                    data-placeholder="All tracking types"
                    data-allow-clear="false"
                >
                    <option value="">All</option>
                    <option value="1">Tracked</option>
                    <option value="0">Not Tracked</option>
                </select>
            </div>
        @endif
        <div>
            <label for="product_sort" class="form-label">Sort By</label>
            <select
                id="product_sort"
                class="form-select filter-control select2"
                data-placeholder="Sort products"
                data-allow-clear="false"
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
