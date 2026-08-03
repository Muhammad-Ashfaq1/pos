@extends('layouts.customer-portal')

@section('title', 'Service History')

@section('content')
    <div class="cp-page-heading">
        <div class="cp-page-heading-main">
            <div>
                <h1 class="cp-page-title">Service History</h1>
                <p class="cp-page-subtitle">Past visits and invoices</p>
            </div>
        </div>
    </div>

    <div class="cp-panel">
        <div class="cp-list">
            @forelse ($orders as $order)
                <a href="{{ route('customer.orders.show', $order->id) }}" class="cp-list-item">
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
                <div class="cp-list-empty">
                    <i class="ti tabler-clipboard-list"></i>
                    <p class="mb-0">No visits yet.</p>
                </div>
            @endforelse
        </div>
        @if ($orders->hasPages())
            <div class="cp-panel-footer">{{ $orders->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
