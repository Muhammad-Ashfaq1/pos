@extends($layout ?? 'layouts.employee-portal')

@section('title', 'Invoices')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/employee-orders.css') }}?v={{ filemtime(public_path('assets/css/employee-orders.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/employee-invoices.css') }}?v={{ filemtime(public_path('assets/css/employee-invoices.css')) }}">
@endpush

@section('content')
    <div class="employee-invoices-page">
        <x-employee.page-header title="Invoices" :back-url="route($dashboardRoute ?? 'employee.dashboard')" back-title="Back to dashboard" />

        <div class="card employee-invoices-card border-0 shadow-sm">
            <div class="card-body">
                <div class="employee-invoices-toolbar">
                    <h5 class="mb-0 fw-bold">Invoices</h5>
                    <div class="employee-invoices-toolbar-actions">
                        <button
                            type="button"
                            class="btn btn-link employee-invoices-link-btn"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#employeeInvoiceFilters"
                            aria-controls="employeeInvoiceFilters">
                            <i class="ti tabler-filter"></i>
                            Filter
                        </button>
                        <button type="button" class="btn btn-link employee-invoices-link-btn" data-invoice-reset>
                            <i class="ti tabler-refresh"></i>
                            Reset Filters
                        </button>
                        @canany(['orders.create', 'pos.bill'])
                            <a href="{{ route($orderRoutes['invoices_create']) }}" class="btn btn-primary fw-semibold">
                                <i class="ti tabler-plus me-1"></i>
                                Create Invoice
                            </a>
                        @endcanany
                    </div>
                </div>

                <div class="employee-invoices-search mb-4">
                    <label class="form-label fw-semibold">Search</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti tabler-search"></i></span>
                        <input
                            type="search"
                            class="form-control"
                            placeholder="Search invoice ID, customer..."
                            data-invoice-search />
                    </div>
                </div>

                <div class="table-responsive employee-invoices-table-wrap">
                    <table class="table employee-invoices-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Invoice ID</th>
                                <th>Invoice Date</th>
                                <th>Due Date</th>
                                <th>Customer Name</th>
                                <th>Item Description</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Tax Amount</th>
                                <th>Total Amount</th>
                                <th>Service Fee</th>
                                <th>Sub Total</th>
                                <th>Balance Due</th>
                                <th>Status</th>
                                <th>Send Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody data-invoice-list>
                            <tr data-invoice-loading>
                                <td colspan="15" class="text-center text-muted py-5">Loading invoices…</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="employee-invoices-empty d-none text-center py-5" data-invoice-empty>
                    <i class="ti tabler-file-off fs-1 text-muted d-block mb-2"></i>
                    <span class="text-muted">No invoices found.</span>
                </div>

                <div class="employee-invoices-pagination d-none mt-3" data-invoice-pagination>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-invoice-prev disabled>Previous</button>
                    <span class="mx-3 small text-muted" data-invoice-page-label></span>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-invoice-next disabled>Next</button>
                </div>
            </div>
        </div>

        @include('employee.invoices.partials.filters')

        <div class="modal fade" id="invoiceShareModal" tabindex="-1" aria-labelledby="invoiceShareModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold" id="invoiceShareModalLabel">Send Invoice Email</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="invoice-share-form">
                        <div class="modal-body">
                            <input type="hidden" name="share_url" data-invoice-share-url>
                            <label for="invoice_share_email" class="form-label fw-bold">
                                Recipient Email <span class="text-danger">*</span>
                            </label>
                            <p class="small text-muted mb-2">Customer email is prefilled when available. You can edit it before sending.</p>
                            <input
                                type="email"
                                id="invoice_share_email"
                                name="email"
                                class="form-control"
                                required
                                placeholder="name@example.com"
                                data-invoice-share-email
                                autocomplete="email">
                        </div>
                        <div class="modal-footer border-top">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary fw-bold" data-invoice-share-submit>Send PDF</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-script')
    <script>
        window.employeeInvoicesConfig = {
            listingUrl: @json(route($orderRoutes['invoices_listing'])),
            createUrl: @json(route($orderRoutes['invoices_create'])),
            csrfToken: @json(csrf_token()),
        };
    </script>
    <script src="{{ asset('assets/js/employee/invoices.js') }}?v={{ filemtime(public_path('assets/js/employee/invoices.js')) }}"></script>
@endpush
