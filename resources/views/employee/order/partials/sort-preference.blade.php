<div
    class="offcanvas offcanvas-start employee-orders-sort-drawer"
    tabindex="-1"
    id="employeeOrderSortPreference"
    aria-labelledby="employeeOrderSortPreferenceLabel">
    <div class="employee-orders-sort-header">
        <div class="employee-orders-sort-title-wrap">
            <button
                type="button"
                class="employee-orders-back-btn employee-orders-sort-back"
                data-bs-dismiss="offcanvas"
                aria-label="Back">
                <i class="ti tabler-arrow-left"></i>
            </button>
            <h4 class="employee-orders-sort-title" id="employeeOrderSortPreferenceLabel">Sort Preference</h4>
        </div>

        <button
            type="button"
            class="employee-orders-icon-btn employee-orders-sort-close"
            data-bs-dismiss="offcanvas"
            aria-label="Close sort preference">
            <i class="ti tabler-x"></i>
        </button>
    </div>

    <div class="offcanvas-body employee-orders-sort-body">
        <div class="employee-orders-sort-list" role="listbox" aria-label="Sort orders by">
            <button
                type="button"
                class="employee-orders-sort-option"
                data-order-sort-option="customer_name"
                role="option"
                aria-selected="false">
                Customer Name
            </button>
            <button
                type="button"
                class="employee-orders-sort-option"
                data-order-sort-option="date_opened"
                role="option"
                aria-selected="false">
                Date Opened
            </button>
            <button
                type="button"
                class="employee-orders-sort-option active"
                data-order-sort-option="order_id"
                role="option"
                aria-selected="true">
                Order ID
            </button>
            <button
                type="button"
                class="employee-orders-sort-option"
                data-order-sort-option="order_total"
                role="option"
                aria-selected="false">
                Order Total
            </button>
        </div>
    </div>
</div>
