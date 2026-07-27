@extends('layouts.customer-portal')

@section('title', 'Overview')

@section('content')
    <div class="cp-page-heading">
        <div class="cp-page-heading-main">
            <div>
                <h1 class="cp-page-title">Overview</h1>
                <p class="cp-page-subtitle">Your visits and store credit at a glance</p>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="cp-hero h-100">
                <div class="cp-hero-label"><i class="ti tabler-wallet"></i><span>Store Credit Balance</span></div>
                <div class="cp-hero-value">@money($customer->credit_balance)</div>
                <div class="cp-hero-meta">at {{ $customer->tenant?->name ?? $customer->tenant?->shop_name }}</div>
                @if ($creditCanRedeem)
                    <div class="mt-3"><span class="badge bg-label-success">Ready to use at checkout</span></div>
                @else
                    <div class="cp-hero-meta mt-3">
                        Usable when balance reaches @money($creditMinRedeemBalance).
                    </div>
                @endif
                <a href="{{ route('customer.credits') }}" class="cp-hero-link">
                    View credit activity <i class="ti tabler-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="row g-3 h-100">
                <div class="col-sm-6">
                    <div class="cp-stat">
                        <div class="cp-stat-icon cp-stat-icon--indigo"><i class="ti tabler-calendar-check"></i></div>
                        <div>
                            <div class="cp-stat-label">Total Visits</div>
                            <div class="cp-stat-value">{{ $customer->total_visits }}</div>
                            <div class="cp-stat-meta">Completed service visits</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="cp-stat">
                        <div class="cp-stat-icon cp-stat-icon--amber"><i class="ti tabler-receipt-dollar"></i></div>
                        <div>
                            <div class="cp-stat-label">Lifetime Spend</div>
                            <div class="cp-stat-value">@money($customer->lifetime_value)</div>
                            <div class="cp-stat-meta">Across all paid visits</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cp-quick-actions mb-4">
        <a href="{{ route('customer.orders') }}" class="cp-quick-action">
            <span class="cp-quick-action-icon"><i class="ti tabler-history"></i></span>
            <span>
                <strong>Service History</strong>
                <small>View past orders and invoices</small>
            </span>
            <i class="ti tabler-chevron-right cp-quick-action-arrow"></i>
        </a>
        <a href="{{ route('customer.vehicles') }}" class="cp-quick-action">
            <span class="cp-quick-action-icon"><i class="ti tabler-car"></i></span>
            <span>
                <strong>My Vehicles</strong>
                <small>See vehicles on file</small>
            </span>
            <i class="ti tabler-chevron-right cp-quick-action-arrow"></i>
        </a>
        <a href="{{ route('customer.credits') }}" class="cp-quick-action">
            <span class="cp-quick-action-icon"><i class="ti tabler-wallet"></i></span>
            <span>
                <strong>Store Credit</strong>
                <small>Balance and credit history</small>
            </span>
            <i class="ti tabler-chevron-right cp-quick-action-arrow"></i>
        </a>
    </div>

    <div class="cp-panel">
        <div class="cp-panel-header">
            <div>
                <h2 class="cp-panel-title">Recent Visits</h2>
                <p class="cp-panel-subtitle">Your latest service activity</p>
            </div>
            <a class="btn btn-sm btn-link fw-semibold" href="{{ route('customer.orders') }}">View all</a>
        </div>
        <div class="cp-list">
            @forelse ($recentOrders as $order)
                <a href="{{ route('customer.orders.show', $order->id) }}" class="cp-list-item">
                    <div class="d-flex align-items-start gap-3">
                        <span class="cp-order-icon"><i class="ti tabler-file-invoice"></i></span>
                        <div>
                        <div class="fw-semibold">{{ $order->order_number }}</div>
                        <div class="small text-muted">{{ $order->created_at?->format('M j, Y h:i A') }} · {{ $order->items_count }} item(s)</div>
                        @if ($order->vehicle)
                            <div class="small text-muted mt-1">
                                <i class="ti tabler-car me-1"></i>{{ trim(collect([$order->vehicle->year, $order->vehicle->make, $order->vehicle->model])->filter()->implode(' ')) }}
                                @if ($order->vehicle->plate_number) · {{ $order->vehicle->plate_number }} @endif
                            </div>
                        @endif
                        @if ($order->credit_earned > 0)
                            <div class="small text-success">+@money($order->credit_earned) credit earned</div>
                        @endif
                        </div>
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
    </div>
@endsection
