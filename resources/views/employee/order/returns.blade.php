@extends('layouts.employee-portal')

@section('title', 'Order Returns')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/employee-orders.css') }}?v={{ filemtime(public_path('assets/css/employee-orders.css')) }}">
    <style>
        .employee-return-summary-value {
            overflow-x: auto;
            white-space: nowrap;
            max-width: 200px;
        }

        /* Custom scrollbar styling */
        .employee-return-summary-value::-webkit-scrollbar {
            height: 6px;
        }

        .employee-return-summary-value::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .employee-return-summary-value::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .employee-return-summary-value::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
@endpush

@section('content')
    <div class="employee-orders-page">
        <x-employee.page-header title="Order Returns" :back-url="route('employee.dashboard')" back-title="Back to dashboard" />

        <div class="employee-orders-layout">
            <aside class="employee-orders-panel employee-orders-filters">
                <div class="employee-returns-policy">
                    <span class="employee-returns-policy-icon">
                        <i class="ti tabler-rotate-2"></i>
                    </span>
                    <div>
                        <h6>Return Policy</h6>
                        <p>Orders can be returned within <strong>{{ $returnDays }} days</strong> of payment. Refunds exclude tax &amp; service fees.</p>
                    </div>
                </div>

                <label class="employee-orders-search">
                    <i class="ti tabler-search"></i>
                    <input type="search" class="form-control" placeholder="Search order number, customer, or vehicle" data-returns-search>
                </label>
            </aside>

            <section class="employee-orders-panel employee-orders-results">
                <div class="employee-orders-tabs" role="tablist" aria-label="Return views">
                    <button type="button" class="employee-orders-tab active" data-returns-tab="eligible">
                        Eligible for Return (<span data-returns-count="eligible">0</span>)
                    </button>
                    <button type="button" class="employee-orders-tab" data-returns-tab="history">
                        Return History (<span data-returns-count="history">0</span>)
                    </button>
                </div>

                {{-- Eligible orders --}}
                <div data-returns-view="eligible">
                    <div class="employee-orders-list-heading">
                        <h5>Eligible Orders</h5>
                        <div class="employee-orders-list-actions">
                            <button type="button" class="employee-orders-icon-btn" data-return-refresh data-bs-toggle="tooltip" title="Refresh orders">
                                <i class="ti tabler-refresh"></i>
                            </button>
                        </div>
                    </div>

                    <div class="employee-orders-loading d-none" data-return-loading>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <span>Loading orders...</span>
                    </div>

                    <div class="employee-orders-list" data-return-list></div>

                    <div class="employee-orders-empty d-none" data-return-empty>
                        <i class="ti tabler-clipboard-off"></i>
                        <span>No eligible orders found for return.</span>
                    </div>
                </div>

                {{-- Return history --}}
                <div class="d-none" data-returns-view="history">
                    <div class="employee-orders-list-heading">
                        <h5>Returned Orders</h5>
                        <div class="employee-orders-list-actions">
                            <button type="button" class="employee-orders-icon-btn" data-history-refresh data-bs-toggle="tooltip" title="Refresh history">
                                <i class="ti tabler-refresh"></i>
                            </button>
                        </div>
                    </div>

                    <div class="employee-orders-loading d-none" data-history-loading>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <span>Loading returned orders...</span>
                    </div>

                    <div class="employee-orders-list" data-history-list></div>

                    <div class="employee-orders-empty d-none" data-history-empty>
                        <i class="ti tabler-clipboard-off"></i>
                        <span>No returned orders found.</span>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- Return Confirmation Modal --}}
    <div class="modal fade employee-return-modal" id="returnConfirmationModal" tabindex="-1" aria-labelledby="returnConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="employee-return-modal-heading">
                        <span class="employee-return-modal-icon"><i class="ti tabler-rotate-2"></i></span>
                        <div>
                            <h5 class="modal-title" id="returnConfirmationModalLabel">Process Return - <span id="returnOrderNumber"></span></h5>
                            <p class="employee-return-modal-subtitle">Select the items the customer is returning.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="employee-return-summary">
                        <div class="employee-return-summary-item">
                            <span class="employee-return-summary-label">Customer</span>
                            <span class="employee-return-summary-value" id="returnCustomerName"></span>
                        </div>
                        <div class="employee-return-summary-item">
                            <span class="employee-return-summary-label">Total Paid</span>
                            <span class="employee-return-summary-value" id="returnTotalAmount"></span>
                        </div>
                        <div class="employee-return-summary-item">
                            <span class="employee-return-summary-label">Days Since Payment</span>
                            <span class="employee-return-summary-value" id="returnDaysSincePayment"></span>
                        </div>
                    </div>

                    <div class="employee-return-section">
                        <div class="employee-return-section-title">
                            <i class="ti tabler-box"></i>
                            <span>Select Items to Return</span>
                        </div>
                        <div class="employee-return-items" id="returnItemsList"></div>
                    </div>

                    <div class="employee-return-refund">
                        <div>
                            <span class="employee-return-refund-label">Total Refund Amount</span>
                            <span class="employee-return-refund-note">Tax &amp; service fees are non-refundable</span>
                        </div>
                        <span class="employee-return-refund-value" id="calculatedRefundAmount">$0.00</span>
                    </div>

                    <form id="returnForm" class="employee-return-form">
                        <div class="employee-orders-field">
                            <label for="returnReason">Return Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="returnReason" name="return_reason" rows="2" required maxlength="500" placeholder="Why is this order being returned?"></textarea>
                        </div>
                        <div class="employee-orders-field">
                            <label for="refundMethod">Refund Method <span class="text-danger">*</span></label>
                            <select class="form-select" id="refundMethod" name="refund_method" required>
                                <option value="">Select refund method</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="check">Check</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmReturnBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <span class="btn-text">Process Return</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-script')
    <script>
        window.employeeReturnsConfig = {
            listingUrl: @json(route('employee.order.returns.listing')),
            historyUrl: @json(route('employee.order.returns.history')),
            returnUrlTemplate: @json(route('employee.order.return', ['order' => '__ORDER_ID__'])),
            returnDays: @json($returnDays)
        };
    </script>
    <script src="{{ asset('assets/js/employee/order-returns.js') }}?v={{ filemtime(public_path('assets/js/employee/order-returns.js')) }}"></script>
@endpush
