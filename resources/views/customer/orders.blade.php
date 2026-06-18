@extends('layouts.customer-portal')

@section('title', 'Service History')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-header"><h6 class="mb-0 fw-bold">Service History</h6></div>
        <div class="list-group list-group-flush">
            @forelse ($orders as $order)
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
        @if ($orders->hasPages())
            <div class="card-footer">{{ $orders->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
