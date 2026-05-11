<div
    class="offcanvas offcanvas-start employee-orders-advanced-drawer"
    tabindex="-1"
    id="employeeOrderAdvancedSearch"
    aria-labelledby="employeeOrderAdvancedSearchLabel">
    <div class="employee-orders-advanced-header">
        <div class="employee-orders-advanced-title-wrap">
            <button
                type="button"
                class="employee-orders-back-btn employee-orders-advanced-back"
                data-bs-dismiss="offcanvas"
                aria-label="Back">
                <i class="ti tabler-arrow-left"></i>
            </button>
            <h4 class="employee-orders-advanced-title" id="employeeOrderAdvancedSearchLabel">Advanced Search</h4>
        </div>

        <button
            type="button"
            class="employee-orders-icon-btn employee-orders-advanced-close"
            data-bs-dismiss="offcanvas"
            aria-label="Close advanced search">
            <i class="ti tabler-x"></i>
        </button>
    </div>

    <div class="offcanvas-body employee-orders-advanced-body">
        <label class="employee-orders-advanced-search">
            <i class="ti tabler-search"></i>
            <input
                type="search"
                class="form-control"
                placeholder="Search Name, Barcode or ALU"
                data-order-search
                data-order-search-source="advanced">
        </label>

        <label class="employee-orders-advanced-date">
            <i class="ti tabler-calendar-event"></i>
            <select
                class="form-select select2"
                data-order-date-preset
                data-dropdown-parent="#employeeOrderAdvancedSearch"
                data-minimum-results-for-search="Infinity"
                data-allow-clear="false"
                aria-label="Filter orders by date">
                <option value="all">All dates</option>
                <option value="today">Today</option>
                <option value="yesterday">Yesterday</option>
                <option value="last_7">Last 7 days</option>
                <option value="this_month">This month</option>
                <option value="custom">Custom range</option>
            </select>
        </label>

        <div class="employee-orders-custom-range d-none" data-order-custom-range>
            <label class="employee-orders-field">
                <span>From</span>
                <input type="date" class="form-control" data-order-date-from>
            </label>
            <label class="employee-orders-field">
                <span>To</span>
                <input type="date" class="form-control" data-order-date-to>
            </label>
        </div>

        <div class="employee-orders-advanced-fields" aria-label="Advanced search fields">
            <label class="employee-orders-check">
                <input class="form-check-input" type="checkbox" value="order_number" data-order-search-field>
                <span>Order Number</span>
            </label>
            <label class="employee-orders-check">
                <input class="form-check-input" type="checkbox" value="customer_id" data-order-search-field>
                <span>Customer ID</span>
            </label>
            <label class="employee-orders-check">
                <input class="form-check-input" type="checkbox" value="paid_status" data-order-search-field>
                <span>Paid Status</span>
            </label>
            <label class="employee-orders-check">
                <input class="form-check-input" type="checkbox" value="retailer" data-order-search-field>
                <span>Retailer</span>
            </label>
            <label class="employee-orders-check">
                <input class="form-check-input" type="checkbox" value="time" data-order-search-field>
                <span>Time</span>
            </label>
            <label class="employee-orders-check">
                <input class="form-check-input" type="checkbox" value="date" data-order-search-field>
                <span>Date</span>
            </label>
        </div>

        <div class="employee-orders-advanced-list-heading">
            <h5>Order List</h5>
        </div>

        <div class="employee-orders-loading d-none" data-advanced-order-loading>
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <span>Loading orders...</span>
        </div>

        <div class="employee-orders-advanced-list" data-advanced-order-list></div>

        <div class="employee-orders-empty d-none" data-advanced-order-empty>
            <i class="ti tabler-clipboard-off"></i>
            <span>No orders found.</span>
        </div>
    </div>
</div>
