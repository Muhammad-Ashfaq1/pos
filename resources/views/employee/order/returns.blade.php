@extends('layouts.employee-portal')

@section('title', 'Order Returns')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/employee-orders.css') }}?v={{ filemtime(public_path('assets/css/employee-orders.css')) }}">
@endpush

@section('content')
    <div class="employee-orders-page">
        <div class="employee-orders-heading">
            <a href="{{ route('employee.dashboard') }}" class="employee-orders-back-btn" data-bs-toggle="tooltip" title="Back to dashboard">
                <i class="ti tabler-arrow-left"></i>
            </a>
            <h4 class="employee-orders-title">Order Returns</h4>
        </div>

        <div class="employee-orders-layout">
            <aside class="employee-orders-panel employee-orders-filters">
                <div class="employee-orders-info">
                    <h6>Return Policy</h6>
                    <p>Orders can be returned within <strong>{{ $returnDays }} days</strong> of payment.</p>
                </div>

                <label class="employee-orders-search">
                    <i class="ti tabler-search"></i>
                    <input type="search" class="form-control" placeholder="Search order number, customer, or vehicle" data-return-search>
                </label>
            </aside>

            <section class="employee-orders-panel employee-orders-results">
                <div class="employee-orders-list-heading">
                    <h5>Eligible Orders for Return</h5>
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
            </section>
        </div>

        <!-- Returned Orders History Section -->
        <div class="employee-orders-layout mt-4">
            <aside class="employee-orders-panel employee-orders-filters">
                <div class="employee-orders-info">
                    <h6>Returned Orders History</h6>
                    <p>View all orders that have been returned.</p>
                </div>

                <label class="employee-orders-search">
                    <i class="ti tabler-search"></i>
                    <input type="search" class="form-control" placeholder="Search returned orders" data-history-search>
                </label>
            </aside>

            <section class="employee-orders-panel employee-orders-results">
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
            </section>
        </div>
    </div>

    <!-- Return Confirmation Modal -->
    <div class="modal fade" id="returnConfirmationModal" tabindex="-1" aria-labelledby="returnConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnConfirmationModalLabel">Confirm Order Return</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="return-order-details">
                        <div class="return-detail-row">
                            <span class="return-detail-label">Order Number:</span>
                            <span class="return-detail-value" id="returnOrderNumber"></span>
                        </div>
                        <div class="return-detail-row">
                            <span class="return-detail-label">Customer:</span>
                            <span class="return-detail-value" id="returnCustomerName"></span>
                        </div>
                        <div class="return-detail-row">
                            <span class="return-detail-label">Total Amount:</span>
                            <span class="return-detail-value" id="returnTotalAmount"></span>
                        </div>
                        <div class="return-detail-row">
                            <span class="return-detail-label">Days Since Payment:</span>
                            <span class="return-detail-value" id="returnDaysSincePayment"></span>
                        </div>
                    </div>

                    <div class="return-items-section mt-3">
                        <h6 class="fw-bold mb-3">Select Items to Return</h6>
                        <div class="return-items-list" id="returnItemsList"></div>
                        <div class="return-items-summary mt-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Total Refund Amount (without tax):</span>
                                <span class="fw-bold text-success fs-5" id="calculatedRefundAmount">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <form id="returnForm" class="mt-3">
                        <div class="mb-3">
                            <label for="returnReason" class="form-label fw-bold">Return Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="returnReason" name="return_reason" rows="3" required maxlength="500" placeholder="Please provide a reason for this return"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="refundMethod" class="form-label fw-bold">Refund Method <span class="text-danger">*</span></label>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
