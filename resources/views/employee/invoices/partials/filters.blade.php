<div
    class="offcanvas offcanvas-end employee-invoice-filters-drawer"
    tabindex="-1"
    id="employeeInvoiceFilters"
    aria-labelledby="employeeInvoiceFiltersLabel">
    <div class="offcanvas-header border-bottom">
        <div class="d-flex align-items-center gap-2">
            <button
                type="button"
                class="employee-orders-back-btn"
                data-bs-dismiss="offcanvas"
                aria-label="Back">
                <i class="ti tabler-arrow-left"></i>
            </button>
            <h5 class="offcanvas-title mb-0 fw-bold" id="employeeInvoiceFiltersLabel">Filters</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column">
        <form class="flex-grow-1" data-invoice-filter-form>
            <div class="mb-4">
                <label class="form-label fw-semibold" for="invoice_date_from">Date Range</label>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ti tabler-calendar"></i></span>
                            <input type="date" id="invoice_date_from" class="form-control" data-invoice-date-from>
                        </div>
                    </div>
                    <div class="col-6">
                        <input type="date" class="form-control" data-invoice-date-to aria-label="Date to">
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Amount Range</label>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" min="0" step="0.01" class="form-control" placeholder="Min" data-invoice-amount-min>
                    </div>
                    <span class="text-muted">to</span>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" min="0" step="0.01" class="form-control" placeholder="Max" data-invoice-amount-max>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold" for="invoice_status">Status</label>
                <select id="invoice_status" class="form-select" data-invoice-status>
                    <option value="">Select Status</option>
                    <option value="paid">Paid</option>
                    <option value="partially_paid">Partially Paid</option>
                    <option value="pending">Not Paid</option>
                </select>
            </div>
        </form>

        <button type="button" class="btn btn-primary w-100 fw-bold mt-auto" data-invoice-apply-filters>
            <i class="ti tabler-check me-1"></i>
            Apply Filters
        </button>
    </div>
</div>
