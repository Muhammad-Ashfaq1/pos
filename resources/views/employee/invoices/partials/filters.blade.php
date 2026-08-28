<div class="dropdown">
    <button
        type="button"
        class="btn btn-label-secondary btn-icon"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        title="Filters">
        <i class="ti tabler-filter"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 340px;">
        <div class="mb-3">
            <label class="form-label" for="invoice_date_from">Date Range</label>
            <div class="row g-2">
                <div class="col-6">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti tabler-calendar"></i></span>
                        <input type="date" id="invoice_date_from" class="form-control" data-invoice-date-from data-invoice-filter-control>
                    </div>
                </div>
                <div class="col-6">
                    <input type="date" class="form-control" data-invoice-date-to data-invoice-filter-control aria-label="Date to">
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Amount Range</label>
            <div class="d-flex align-items-center gap-2">
                <div class="input-group">
                    <span class="input-group-text">@currency</span>
                    <input type="number" min="0" step="0.01" class="form-control" placeholder="Min" data-invoice-amount-min data-invoice-filter-control>
                </div>
                <span class="text-muted">to</span>
                <div class="input-group">
                    <span class="input-group-text">@currency</span>
                    <input type="number" min="0" step="0.01" class="form-control" placeholder="Max" data-invoice-amount-max data-invoice-filter-control>
                </div>
            </div>
        </div>

        <div>
            <label class="form-label" for="invoice_status">Status</label>
            <select id="invoice_status" class="form-select" data-invoice-status data-invoice-filter-control>
                <option value="">All</option>
                <option value="paid">Paid</option>
                <option value="partially_paid">Partially Paid</option>
                <option value="pending">Not Paid</option>
            </select>
        </div>
    </div>
</div>
