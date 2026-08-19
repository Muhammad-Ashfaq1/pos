@php
    $customerTypes = $customerTypes ?? \App\Models\Customer::typeOptions();
    $vehicleRequired = app(\App\Support\Tenancy\TenantContext::class)->current()?->isVehicleRequired() ?? true;
@endphp

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h4 class="mb-0">Customers</h4>
        <p class="text-muted mb-0 small">View customers and add new walk-ins or registered accounts.</p>
    </div>

    <div class="d-flex align-items-center gap-2" id="customerTableActions">
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

<div class="{{ ! empty($isEmployeeSurface) ? 'pos-glass-card pos-tone-primary' : 'card' }}">
    <div class="card-datatable table-responsive pt-0">
        <table class="customers-datatables table">
            <thead class="bg-label-primary">
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>Contact</th>
                    @if($vehicleRequired)
                        <th>Vehicles</th>
                    @endif
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

@include('tenant.ecommerce.customers.partials.save-modal')
