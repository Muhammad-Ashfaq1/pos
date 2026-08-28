@extends($layout ?? 'layouts.employee-portal')

@section('title', 'Invoices')

@push('styles')
    @include('partials.pos-listing-assets')
    <link rel="stylesheet" href="{{ asset('assets/css/employee-orders.css') }}?v={{ filemtime(public_path('assets/css/employee-orders.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/employee-invoices.css') }}?v={{ filemtime(public_path('assets/css/employee-invoices.css')) }}">
@endpush

@section('content')
    @php
        $isEmployeeSurface = ! empty($isEmployeeSurface);
    @endphp
    <div class="pos-listing employee-invoices-page">
        @if($isEmployeeSurface)
            <x-employee.page-header title="Invoices" :back-url="route($dashboardRoute ?? 'employee.dashboard')" back-title="Back to dashboard" />
        @endif

        <div class="pos-glass-card pos-tone-secondary pos-listing-panel">
            <div class="pos-listing-toolbar">
                <div class="me-2">
                    <h4 class="pos-listing-title mb-0">Invoices</h4>
                    <p class="text-muted small mb-0">Billing, print, and customer email</p>
                </div>
                <div class="pos-listing-search-slot">
                    <div class="input-group input-group-merge w-100">
                        <span class="input-group-text"><i class="ti tabler-search"></i></span>
                        <input
                            type="search"
                            class="form-control"
                            placeholder="Search invoice ID, customer..."
                            data-invoice-search />
                    </div>
                </div>
                <div class="pos-listing-toolbar-tools">
                    <div class="pos-listing-toolbar-actions" id="employeeInvoiceTableActions">
                        @include('employee.invoices.partials.filters')
                        <button
                            type="button"
                            class="btn btn-label-secondary btn-icon"
                            data-invoice-reset
                            title="Reset Filters"
                        >
                            <i class="ti tabler-refresh"></i>
                        </button>
                        @canany(['orders.create', 'pos.bill'])
                            <a href="{{ route($orderRoutes['invoices_create']) }}" class="btn btn-primary">
                                <i class="ti tabler-plus me-1"></i>
                                Create Invoice
                            </a>
                        @endcanany
                    </div>
                </div>
            </div>

            <div class="card-datatable table-responsive pos-listing-table pt-0">
                <table class="table table-hover align-middle mb-0 employee-invoices-table">
                    <thead>
                        <tr>
                            <th style="min-width: 180px;">Invoice ID</th>
                            <th style="min-width: 120px;">Invoice Date</th>
                            <th style="min-width: 120px;">Due Date</th>
                            <th style="min-width: 160px;">Customer Name</th>
                            <th style="min-width: 200px;">Item Description</th>
                            <th style="min-width: 90px;" class="text-end">Price</th>
                            <th style="min-width: 90px;" class="text-center">Quantity</th>
                            <th style="min-width: 100px;" class="text-end">Tax Amount</th>
                            <th style="min-width: 110px;" class="text-end">Total Amount</th>
                            <th style="min-width: 100px;" class="text-end">Service Fee</th>
                            <th style="min-width: 100px;" class="text-end">Sub Total</th>
                            <th style="min-width: 110px;" class="text-end">Balance Due</th>
                            <th style="min-width: 110px;" class="text-center">Status</th>
                            <th style="min-width: 100px;" class="text-center">Send Email</th>
                            <th style="min-width: 90px;" class="text-center">Action</th>
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

            {{-- Not "dt-length": admin layouts.app dashboards-analytics.js adds mb-md-6 to .dt-length --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mx-3 my-2 employee-invoices-pagination d-none" data-invoice-pagination>
                <div class="d-flex align-items-center gap-2 employee-invoices-pagination-meta">
                    <div class="employee-invoices-info" data-invoice-page-label>Showing 0 to 0 of 0 entries</div>
                    <div class="employee-invoices-length">
                        <select class="form-select" data-invoice-per-page aria-label="Invoices per page">
                            <option value="10">10</option>
                            <option value="20" selected>20</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
                <nav aria-label="Invoice pagination">
                    <ul class="pagination mb-0">
                        <li class="page-item" data-invoice-prev-item>
                            <button type="button" class="page-link" data-invoice-prev aria-label="Previous page">
                                <i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>
                            </button>
                        </li>
                        <li class="page-item active" aria-current="page">
                            <span class="page-link" data-invoice-current-page>1</span>
                        </li>
                        <li class="page-item" data-invoice-next-item>
                            <button type="button" class="page-link" data-invoice-next aria-label="Next page">
                                <i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="modal fade pos-listing-modal" id="invoiceShareModal" tabindex="-1" aria-labelledby="invoiceShareModalLabel" aria-hidden="true">
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
