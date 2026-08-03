@extends('layouts.customer-portal')

@section('title', 'Store Credit')

@section('content')
    <div class="cp-page-heading">
        <div class="cp-page-heading-main">
            <div>
                <h1 class="cp-page-title">Store Credit</h1>
                <p class="cp-page-subtitle">Balance and ledger activity</p>
            </div>
        </div>
    </div>

    <div class="cp-hero mb-4">
        <div class="cp-hero-label"><i class="ti tabler-wallet"></i><span>Current Balance</span></div>
        <div class="cp-hero-value">@money($customer->credit_balance)</div>
        @if ($creditCanRedeem)
            <div class="mt-3"><span class="badge bg-label-success">Ready to use at checkout</span></div>
        @else
            <div class="cp-hero-meta mt-3">Usable when balance reaches @money($creditMinRedeemBalance).</div>
        @endif
    </div>

    <div class="cp-filters">
        @php
            $filters = [
                '' => 'All',
                'earn' => 'Earned',
                'redeem' => 'Redeemed',
                'adjust' => 'Adjusted',
                'expire' => 'Expired',
            ];
        @endphp
        @foreach ($filters as $value => $label)
            <a href="{{ route('customer.credits', array_filter(['type' => $value ?: null])) }}"
               class="btn btn-sm cp-filter {{ ($type ?? '') === $value ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="cp-panel">
        <div class="cp-panel-header">
            <h2 class="cp-panel-title">Credit History</h2>
        </div>
        <div class="cp-list">
            @forelse ($transactions as $t)
                @php
                    $badgeClass = match ($t->type) {
                        'earn' => 'bg-label-success',
                        'redeem' => 'bg-label-danger',
                        'adjust' => 'bg-label-warning',
                        default => 'bg-label-secondary',
                    };
                @endphp
                <div class="cp-list-item">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge {{ $badgeClass }} text-capitalize">{{ $t->type }}</span>
                            @if ($t->order)
                                <a href="{{ route('customer.orders.show', $t->order->id) }}" class="fw-semibold">
                                    {{ $t->order->order_number }}
                                </a>
                            @endif
                        </div>
                        <div class="small text-muted">
                            {{ $t->created_at?->format('M j, Y h:i A') }}
                            @if ($t->description) · {{ $t->description }}@endif
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold {{ (float) $t->amount >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ (float) $t->amount >= 0 ? '+' : '-' }}@money(abs((float) $t->amount))
                        </div>
                        <div class="small text-muted">Bal: @money($t->balance_after)</div>
                    </div>
                </div>
            @empty
                <div class="cp-list-empty">
                    <i class="ti tabler-wallet"></i>
                    <p class="mb-0">No credit activity yet.</p>
                </div>
            @endforelse
        </div>
        @if ($transactions->hasPages())
            <div class="cp-panel-footer">{{ $transactions->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
