@extends('layouts.employee-portal')

@section('title', 'Order Details')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/employee-order-details.css') }}?v={{ filemtime(public_path('assets/css/employee-order-details.css')) }}">
@endpush

@section('content')
    <div class="employee-order-details-page">
        <div class="employee-order-details-heading">
            <a href="{{ route('employee.order.index') }}" class="employee-order-details-back-btn" data-bs-toggle="tooltip" title="Back to orders">
                <i class="ti tabler-arrow-left"></i>
            </a>
            <h4 class="employee-order-details-title">Order Details</h4>
        </div>

        <div class="employee-order-details-layout">
            <section class="employee-order-details-panel employee-order-details-items-panel">
                <div class="employee-order-details-panel-header">
                    <h5>Order details</h5>
                </div>

                <div class="employee-order-details-table-head">
                    <span>Orders</span>
                    <span>Qty</span>
                    <span>Price</span>
                </div>

                <div class="employee-order-details-items">
                    @forelse ($order['items'] as $item)
                        <div class="employee-order-details-item">
                            <span class="employee-order-details-item-name">{{ $item['product_name'] }}</span>
                            <span class="employee-order-details-item-qty">{{ $item['quantity_label'] }}</span>
                            <span class="employee-order-details-item-price">{{ $item['line_total_label'] }}</span>
                        </div>
                    @empty
                        <div class="employee-order-details-empty">No items found.</div>
                    @endforelse
                </div>

                <div class="employee-order-details-summary">
                    <div class="employee-order-details-summary-row">
                        <span>Items:</span>
                        <strong>{{ $order['items_count'] }}</strong>
                    </div>
                    <div class="employee-order-details-summary-row">
                        <span>Sub Total:</span>
                        <strong>{{ $order['subtotal_amount_label'] }}</strong>
                    </div>
                    @if (($order['service_price_amount'] ?? 0) > 0)
                        <div class="employee-order-details-summary-row employee-order-details-service-fee-row">
                            <span>Service Price:</span>
                            <strong>+{{ $order['service_price_amount_label'] }}</strong>
                        </div>
                    @endif
                    @if (($order['manual_service_fee_amount'] ?? $order['service_fee_amount']) > 0)
                        <div class="employee-order-details-summary-row employee-order-details-service-fee-row">
                            <span>Service Fee:</span>
                            <strong>+{{ $order['manual_service_fee_amount_label'] ?? $order['service_fee_amount_label'] }}</strong>
                        </div>
                    @endif
                    @if ($order['discount_amount'] > 0)
                        <div class="employee-order-details-summary-row employee-order-details-discount-row">
                            <span>Discount:</span>
                            <strong>-{{ $order['discount_amount_label'] }}</strong>
                        </div>
                    @endif
                    @if ($order['tax_amount'] > 0)
                        <div class="employee-order-details-summary-row">
                            <span>Tax:</span>
                            <strong>+{{ $order['tax_amount_label'] }}</strong>
                        </div>
                    @endif
                    <div class="employee-order-details-total-row">
                        <span>Total</span>
                        <strong>{{ $order['total_amount_label'] }}</strong>
                    </div>
                </div>
            </section>

            <section class="employee-order-details-panel employee-order-details-payment-panel">
                <div class="employee-order-details-order-title">
                    <h5>
                        <span>Order No.</span>
                        <span>{{ $order['order_number'] }}</span>
                    </h5>
                </div>

                <div class="employee-order-details-payment-lines">
                    <div class="employee-order-details-payment-line">
                        <span>Payment Method:</span>
                        <strong>{{ $order['payment_method_label'] }}</strong>
                    </div>
                    <div class="employee-order-details-payment-line">
                        <span>Status:</span>
                        <strong class="employee-order-details-status {{ $order['status_class'] }}">{{ $order['status_label'] }}</strong>
                    </div>
                    @if (($order['service_price_amount'] ?? 0) > 0)
                        <div class="employee-order-details-payment-line employee-order-details-service-fee-row">
                            <span>Service Price:</span>
                            <strong>+{{ $order['service_price_amount_label'] }}</strong>
                        </div>
                    @endif
                    @if (($order['manual_service_fee_amount'] ?? $order['service_fee_amount']) > 0)
                        <div class="employee-order-details-payment-line employee-order-details-service-fee-row">
                            <span>Service Fee:</span>
                            <strong>+{{ $order['manual_service_fee_amount_label'] ?? $order['service_fee_amount_label'] }}</strong>
                        </div>
                    @endif
                    @if ($order['discount_amount'] > 0)
                        <div class="employee-order-details-payment-line employee-order-details-discount-row">
                            <span>Discount:</span>
                            <strong>-{{ $order['discount_amount_label'] }}</strong>
                        </div>
                    @endif
                    @if ($order['tax_amount'] > 0)
                        <div class="employee-order-details-payment-line">
                            <span>Tax:</span>
                            <strong>+{{ $order['tax_amount_label'] }}</strong>
                        </div>
                    @endif
                    <div class="employee-order-details-payment-line">
                        <span>Total:</span>
                        <strong>{{ $order['total_amount_label'] }}</strong>
                    </div>
                </div>



                <div class="employee-order-details-balance">
                    <span>Balance Due:</span>
                    <strong>{{ $order['balance_due_label'] }}</strong>
                </div>

                <div class="d-grid gap-2 mt-4">
                    @if($order['status'] === 'estimate')
                        <button type="button" class="btn btn-primary btn-lg w-100 fw-bold btn-process-order" data-bs-toggle="modal" data-bs-target="#paymentModal">
                            <i class="ti tabler-check me-1"></i> Process Order
                        </button>
                    @elseif($order['status'] !== 'paid')
                        <button type="button" class="btn btn-primary btn-lg w-100 fw-bold btn-pay-balance" data-bs-toggle="modal" data-bs-target="#paymentModal">
                            <i class="ti tabler-coin me-1"></i> Pay Balance
                        </button>
                    @endif
                </div>

                <div class="row g-2 mt-2">
                    <div class="col-4">
                        <a href="{{ route('employee.order.print', $order['id']) }}" target="_blank" class="btn btn-outline-secondary w-100 py-2">
                            <i class="ti tabler-printer"></i><br><small class="fw-bold">Print</small>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="{{ route('employee.order.pdf', $order['id']) }}" class="btn btn-outline-secondary w-100 py-2">
                            <i class="ti tabler-download"></i><br><small class="fw-bold">PDF</small>
                        </a>
                    </div>
                    <div class="col-4">
                        <button type="button" class="btn btn-outline-secondary w-100 py-2 btn-share-pdf" data-order-id="{{ $order['id'] }}">
                            <i class="ti tabler-send"></i><br><small class="fw-bold">Share</small>
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-start">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="paymentModalLabel">
                        {{ $order['status'] === 'estimate' ? 'Process Order' : 'Collect Payment' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="payment-form" method="POST" action="{{ route('employee.order.pay', $order['id']) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Balance Due</label>
                            <div class="fs-4 fw-bold text-primary">{{ $order['balance_due_label'] }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="payment_method" class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                            <select id="payment_method" name="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="card">Credit/Debit Card</option>
                                <option value="check">Check</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="payment_amount" class="form-label fw-bold">Payment Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ \App\Support\Currency::symbol() }}</span>
                                <input type="number" id="payment_amount" name="payment_amount" class="form-control" 
                                    step="0.01" min="0.01" max="999999.99" 
                                    value="{{ round(max($order['total_amount'] - $order['payment_amount'], 0), 2) }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold" id="btn-submit-payment">
                            Submit Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('page-script')
    <script>
        $(function() {
            const paymentModal = $('#paymentModal');
            const paymentForm = $('#payment-form');
            const submitBtn = $('#btn-submit-payment');

            paymentForm.on('submit', function(e) {
                e.preventDefault();

                if (submitBtn.prop('disabled')) return;

                submitBtn.prop('disabled', true).text('Processing...');

                $.ajax({
                    url: paymentForm.attr('action'),
                    method: 'POST',
                    data: paymentForm.serialize(),
                    dataType: 'json'
                }).done(function(response) {
                    if (typeof window.appNotify === 'function') {
                        window.appNotify('success', response.message);
                    } else if (window.Notiflix && window.Notiflix.Notify) {
                        window.Notiflix.Notify.success(response.message);
                    }
                    paymentModal.modal('hide');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }).fail(function(xhr) {
                    const message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'An error occurred while processing the payment.';
                    
                    if (typeof window.appNotify === 'function') {
                        window.appNotify('error', message);
                    } else if (window.Notiflix && window.Notiflix.Notify) {
                        window.Notiflix.Notify.failure(message);
                    }
                    submitBtn.prop('disabled', false).text('Submit Payment');
                });
            });

            // Share button handling
            $('.btn-share-pdf').on('click', function() {
                const btn = $(this);

                if (btn.prop('disabled')) return;

                const originalHtml = btn.html();
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');

                $.ajax({
                    url: '{{ route('employee.order.share', $order['id']) }}',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    dataType: 'json'
                }).done(function(response) {
                    if (typeof window.appNotify === 'function') {
                        window.appNotify('success', response.message);
                    } else if (window.Notiflix && window.Notiflix.Notify) {
                        window.Notiflix.Notify.success(response.message);
                    }
                }).fail(function(xhr) {
                    const message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'An error occurred while sharing the document.';
                    
                    if (typeof window.appNotify === 'function') {
                        window.appNotify('error', message);
                    } else if (window.Notiflix && window.Notiflix.Notify) {
                        window.Notiflix.Notify.failure(message);
                    }
                }).always(function() {
                    btn.prop('disabled', false).html(originalHtml);
                });
            });
        });
    </script>
@endpush
