@extends('layouts.customer-portal')

@section('title', 'My Account')

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-5">
            <div class="credit-hero p-4 h-100">
                <div class="d-flex align-items-center gap-2 mb-2 opacity-75"><i class="ti tabler-wallet"></i><span>Store Credit Balance</span></div>
                <div class="display-5 fw-bold">@money($customer->credit_balance)</div>
                <div class="small opacity-75 mt-2">at {{ $customer->tenant?->name ?? $customer->tenant?->shop_name }}</div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="row g-3 h-100">
                <div class="col-6"><div class="card h-100 border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Total Visits</div><div class="h3 fw-bold mb-0">{{ $customer->total_visits }}</div></div></div></div>
                <div class="col-6"><div class="card h-100 border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Lifetime Spend</div><div class="h3 fw-bold mb-0">@money($customer->lifetime_value)</div></div></div></div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Recent Visits</h6>
            <a class="btn btn-sm btn-link" href="{{ route('customer.orders') }}">View all</a>
        </div>
        <div class="list-group list-group-flush">
            @forelse ($recentOrders as $order)
                <a href="{{ route('customer.orders.show', $order->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">{{ $order->order_number }}</div>
                        <div class="small text-muted">{{ $order->created_at?->format('M j, Y h:i A') }} · {{ $order->items_count }} item(s)</div>
                        @if ($order->credit_earned > 0)
                            <div class="small text-success">+@money($order->credit_earned) credit earned</div>
                        @endif
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">@money($order->total_amount)</div>
                        <span class="badge bg-label-{{ $order->status === 'paid' ? 'success' : 'warning' }} text-capitalize">{{ str_replace('_', ' ', $order->status) }}</span>
                    </div>
                </a>
            @empty
                <div class="list-group-item text-muted text-center py-4">No visits yet.</div>
            @endforelse
        </div>
    </div>
@endsection
