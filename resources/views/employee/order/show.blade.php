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
                        <span>Subtotal:</span>
                        <strong>{{ $order['subtotal_amount_label'] }}</strong>
                    </div>
                    <div class="employee-order-details-summary-row">
                        <span>Discount:</span>
                        <strong>{{ $order['discount_amount_label'] }}</strong>
                    </div>
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
                    <div class="employee-order-details-payment-line">
                        <span>Total:</span>
                        <strong>{{ $order['total_amount_label'] }}</strong>
                    </div>
                </div>

                <div class="employee-order-details-tax">
                    <h6>Tax Details:</h6>
                    @if ($order['tax_amount'] > 0)
                        @foreach ($order['items'] as $item)
                            <div class="employee-order-details-tax-line">
                                <span>{{ $item['tax_detail_label'] }}</span>
                                <strong>{{ $loop->first ? $order['tax_amount_label'] : '$0.00' }}</strong>
                            </div>
                        @endforeach
                    @else
                        <div class="employee-order-details-tax-line">
                            <span>No tax applied</span>
                            <strong>{{ $order['tax_amount_label'] }}</strong>
                        </div>
                    @endif
                </div>

                <div class="employee-order-details-balance">
                    <span>Balance Due:</span>
                    <strong>{{ $order['balance_due_label'] }}</strong>
                </div>
            </section>
        </div>
    </div>
@endsection
