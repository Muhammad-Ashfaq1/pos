@php
    $customerTypes = $customerTypes ?? \App\Models\Customer::typeOptions();
    $vehicleRequired = app(\App\Support\Tenancy\TenantContext::class)->current()?->isVehicleRequired() ?? true;
@endphp

<div class="pos-listing">
    <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
        <div class="pos-listing-toolbar">
            <h4 class="pos-listing-title">Customers</h4>
            <div class="pos-listing-search-slot" aria-hidden="true"></div>
            <div class="pos-listing-toolbar-tools">
                <div class="pos-listing-toolbar-actions" id="customerTableActions">
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
                                <label for="customer_type_filter" class="form-label">Customer Type</label>
                                <select
                                    id="customer_type_filter"
                                    class="form-select filter-control select2"
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
                                >
                                    <option value="latest">Latest</option>
                                    <option value="name">Name A-Z</option>
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
        </div>

        <div class="card-datatable table-responsive pos-listing-table pt-0">
            <table class="customers-datatables table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Contact</th>
                        @if($vehicleRequired)
                            <th>Vehicles</th>
                        @endif
                        <th>Last Visit</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    @include('tenant.ecommerce.customers.partials.save-modal')
</div>
