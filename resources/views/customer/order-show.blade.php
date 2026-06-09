@extends('layouts.customer-portal')

@section('title', 'Order ' . $order['order_number'])

@section('content')
    <a href="{{ route('customer.orders') }}" class="btn btn-sm btn-link mb-2"><i class="ti tabler-arrow-left me-1"></i>Back to history</a>

    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">{{ $order['order_number'] }}</h6>
            <span class="badge bg-label-{{ $order['status_class'] }}">{{ $order['status_label'] }}</span>
        </div>
        <div class="card-body">
            <div class="text-muted small mb-3">{{ $order['created_at_label'] }}</div>

            <table class="table table-sm">
                <thead><tr><th>Item</th><th class="text-end">Qty</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                    @foreach ($order['items'] as $item)
                        <tr><td>{{ $item['product_name'] }}</td><td class="text-end">{{ $item['quantity_label'] }}</td><td class="text-end">{{ $item['line_total_label'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex justify-content-between"><span>Subtotal</span><strong>{{ $order['subtotal_amount_label'] }}</strong></div>
            @if ($order['discount_amount'] > 0)
                <div class="d-flex justify-content-between text-danger"><span>Discount</span><strong>-{{ $order['discount_amount_label'] }}</strong></div>
            @endif
            @if ($order['tax_amount'] > 0)
                <div class="d-flex justify-content-between"><span>Tax</span><strong>{{ $order['tax_amount_label'] }}</strong></div>
            @endif
            @if ($order['credit_applied'] > 0)
                <div class="d-flex justify-content-between text-primary"><span>Store credit used</span><strong>-{{ $order['credit_applied_label'] }}</strong></div>
            @endif
            <div class="d-flex justify-content-between border-top pt-2 mt-2"><span class="fw-bold">Total</span><strong>{{ $order['total_amount_label'] }}</strong></div>

            @if ($order['credit_earned'] > 0)
                <div class="alert alert-success mt-3 mb-0 py-2"><i class="ti tabler-coin me-1"></i>You earned {{ $order['credit_earned_label'] }} in store credit on this visit.</div>
            @endif

            @if (!empty($order['payment_history']))
                <h6 class="mt-4">Payments</h6>
                <table class="table table-sm mb-0">
                    <tbody>
                        @foreach ($order['payment_history'] as $payment)
                            <tr>
                                <td class="text-muted small">{{ $payment['created_at_label'] }}</td>
                                <td>{{ $payment['payment_method_label'] }}</td>
                                <td class="text-end">{{ $payment['amount_label'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
