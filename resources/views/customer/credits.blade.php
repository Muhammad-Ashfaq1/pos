@extends('layouts.customer-portal')

@section('title', 'Store Credit')

@section('content')
    <div class="credit-hero p-4 mb-4">
        <div class="d-flex align-items-center gap-2 mb-2 opacity-75"><i class="ti tabler-wallet"></i><span>Current Balance</span></div>
        <div class="display-5 fw-bold">@money($customer->credit_balance)</div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header"><h6 class="mb-0 fw-bold">Credit History</h6></div>
        <div class="list-group list-group-flush">
            @forelse ($transactions as $t)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold text-capitalize">{{ $t->type }}@if ($t->order) · {{ $t->order->order_number }}@endif</div>
                        <div class="small text-muted">{{ $t->created_at?->format('M j, Y h:i A') }}@if ($t->description) · {{ $t->description }}@endif</div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold {{ (float) $t->amount >= 0 ? 'text-success' : 'text-danger' }}">{{ (float) $t->amount >= 0 ? '+' : '-' }}@money(abs((float) $t->amount))</div>
                        <div class="small text-muted">Bal: @money($t->balance_after)</div>
                    </div>
                </div>
            @empty
                <div class="list-group-item text-muted text-center py-4">No credit activity yet.</div>
            @endforelse
        </div>
        @if ($transactions->hasPages())
            <div class="card-footer">{{ $transactions->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
