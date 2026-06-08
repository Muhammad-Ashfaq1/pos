<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $order->status === 'estimate' ? 'Estimate' : 'Invoice' }} #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            font-size: 13px;
            line-height: 1.5;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }
        table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-collapse: collapse;
        }
        table td {
            padding: 8px;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
        .header {
            border-bottom: 2px solid #312e81;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 28px;
            color: #312e81;
            font-weight: bold;
            text-transform: uppercase;
        }
        .shop-info {
            font-size: 12px;
            color: #555;
        }
        .meta-info {
            font-size: 13px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #312e81;
            border-bottom: 1px solid #c7d2fe;
            padding-bottom: 5px;
            margin-top: 20px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .details-table {
            margin-bottom: 20px;
        }
        .details-table th {
            background-color: #f1f5f9;
            border-bottom: 2px solid #cbd5e1;
            color: #334155;
            font-weight: bold;
            padding: 8px;
            text-transform: uppercase;
            font-size: 11px;
        }
        .details-table td {
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 8px;
        }
        .totals-table {
            width: 300px;
            margin-left: auto;
            margin-top: 10px;
        }
        .totals-table td {
            padding: 4px 8px;
            border-bottom: 1px solid #f1f5f9;
        }
        .totals-table tr.grand-total td {
            border-top: 2px solid #312e81;
            border-bottom: 2px double #312e81;
            font-size: 16px;
            font-weight: bold;
            color: #312e81;
            padding: 8px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; }
        .badge-warning { background-color: #fef3c7; color: #b45309; }
        .badge-info { background-color: #dbeafe; color: #1d4ed8; }
        .badge-secondary { background-color: #f1f5f9; color: #475569; }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table class="header">
            <tr>
                <td>
                    <!-- Logo / Brand Header -->
                    <div style="margin-bottom: 12px;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#312e81" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 6px; display: inline-block;">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                        <span style="font-size: 22px; font-weight: 800; color: #312e81; vertical-align: middle; letter-spacing: 0.5px; display: inline-block;">OIL<span style="color: #6366f1;">POS</span></span>
                    </div>
                    <div class="title">{{ $order->status === 'estimate' ? 'Estimate' : 'Invoice' }}</div>
                    <div class="meta-info">
                        <strong>Number:</strong> {{ $order->order_number }}<br>
                        <strong>Date:</strong> {{ $details['created_at_label'] }}<br>
                        <strong>Status:</strong>
                        <span class="badge 
                            @if($order->status === 'paid') badge-success
                            @elseif($order->status === 'partially_paid') badge-warning
                            @elseif($order->status === 'estimate') badge-info
                            @else badge-secondary @endif">
                            {{ $details['status_label'] }}
                        </span>
                    </div>
                </td>
                <td class="text-right shop-info">
                    <div style="font-size: 16px; font-weight: bold; color: #312e81; margin-bottom: 5px;">
                        {{ $order->tenant->shop_name ?? $order->tenant->business_name ?? $order->tenant->name ?? 'Our POS Shop' }}
                    </div>
                    @if(!empty($order->tenant->address))
                        {{ $order->tenant->address }}<br>
                        @if(!empty($order->tenant->city))
                            {{ $order->tenant->city }}, {{ $order->tenant->state ?? '' }} {{ $order->tenant->country ?? '' }}<br>
                        @endif
                    @endif
                    @if(!empty($order->tenant->business_phone ?? $order->tenant->phone))
                        <strong>Phone:</strong> {{ $order->tenant->business_phone ?? $order->tenant->phone }}<br>
                    @endif
                    @if(!empty($order->tenant->business_email ?? $order->tenant->email))
                        <strong>Email:</strong> {{ $order->tenant->business_email ?? $order->tenant->email }}
                    @endif
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td style="width: 50%;">
                    <div class="section-title">Customer</div>
                    <strong>Name:</strong> {{ $details['customer_name'] }}<br>
                    @if(!empty($order->customer?->phone))
                        <strong>Phone:</strong> {{ $order->customer->phone }}<br>
                    @endif
                    @if(!empty($order->customer?->email))
                        <strong>Email:</strong> {{ $order->customer->email }}<br>
                    @endif
                    @if(!empty($order->customer?->address))
                        <strong>Address:</strong> {{ $order->customer->address }}
                    @endif
                </td>
                <td style="width: 50%;">
                    <div class="section-title">Vehicle</div>
                    @if($order->vehicle)
                        <strong>Plate:</strong> {{ $order->vehicle->plate_number }}<br>
                        <strong>Description:</strong> {{ trim(implode(' ', array_filter([$order->vehicle->year, $order->vehicle->make, $order->vehicle->model]))) ?: '—' }}<br>
                        @if(!empty($order->vehicle->registration_number))
                            <strong>Registration:</strong> {{ $order->vehicle->registration_number }}<br>
                        @endif
                        @if(!empty($order->vehicle->odometer))
                            <strong>Odometer:</strong> {{ number_format($order->vehicle->odometer, 1) }} miles
                        @endif
                    @else
                        No vehicle details.
                    @endif
                </td>
            </tr>
        </table>

        <div class="section-title">Items & Services</div>
        <table class="details-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th class="text-center" style="width: 15%;">Qty</th>
                    <th class="text-right" style="width: 15%;">Unit Price</th>
                    <th class="text-right" style="width: 20%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $item->product_name }}</div>
                            @if(!empty($item->sku))
                                <span style="font-size: 10px; color: #64748b;">SKU: {{ $item->sku }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            {{ number_format((float) $item->quantity, 0) }}
                            @if(!empty($item->unit))
                                <span style="font-size: 10px; color: #64748b;">{{ $item->unit }}</span>
                            @endif
                        </td>
                        <td class="text-right">{{ App\Support\Currency::format((float) $item->unit_price) }}</td>
                        <td class="text-right">{{ App\Support\Currency::format((float) $item->line_total) }}</td>
                    </tr>
                @endforeach

                {{-- Service Fees --}}
                @if(!empty($order->service_fee_details) && is_array($order->service_fee_details))
                    @foreach ($order->service_fee_details as $fee)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $fee['name'] ?? 'Service Fee' }}</div>
                                <span style="font-size: 10px; color: #64748b; text-transform: uppercase;">
                                    {{ $fee['type'] === 'service' ? 'Catalog Service' : 'Ad-hoc Fee' }}
                                </span>
                            </td>
                            <td class="text-center">1</td>
                            <td class="text-right">{{ App\Support\Currency::format((float) ($fee['amount'] ?? 0)) }}</td>
                            <td class="text-right">{{ App\Support\Currency::format((float) ($fee['amount'] ?? 0)) }}</td>
                        </tr>
                    @endforeach
                @elseif(($order->service_fee_amount ?? 0) > 0)
                    <tr>
                        <td>
                            <div class="fw-bold">Service Fees</div>
                        </td>
                        <td class="text-center">1</td>
                        <td class="text-right">{{ App\Support\Currency::format((float) $order->service_fee_amount) }}</td>
                        <td class="text-right">{{ App\Support\Currency::format((float) $order->service_fee_amount) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">{{ $details['subtotal_amount_label'] }}</td>
            </tr>
            @if(($order->service_fee_amount ?? 0) > 0)
                <tr>
                    <td>Service Fees:</td>
                    <td class="text-right">+{{ $details['service_fee_amount_label'] }}</td>
                </tr>
            @endif
            @if(($order->discount_amount ?? 0) > 0)
                <tr style="color: #16a34a;">
                    <td>Discount:</td>
                    <td class="text-right">-{{ $details['discount_amount_label'] }}</td>
                </tr>
            @endif
            @if(($order->tax_amount ?? 0) > 0)
                <tr>
                    <td>Tax:</td>
                    <td class="text-right">+{{ $details['tax_amount_label'] }}</td>
                </tr>
            @endif
            <tr class="grand-total">
                <td>Total:</td>
                <td class="text-right">{{ $details['total_amount_label'] }}</td>
            </tr>
            @if($order->status !== 'estimate')
                <tr>
                    <td>Paid Amount:</td>
                    <td class="text-right">{{ $details['payment_amount_label'] }}</td>
                </tr>
                <tr style="font-weight: bold;">
                    <td>Balance Due:</td>
                    <td class="text-right">{{ $details['balance_due_label'] }}</td>
                </tr>
            @endif
        </table>

        @if(!empty($order->notes))
            <div class="section-title">Notes</div>
            <div style="font-size: 12px; background-color: #f8fafc; border-left: 3px solid #cbd5e1; padding: 10px; color: #475569;">
                {!! nl2br(e($order->notes)) !!}
            </div>
        @endif

        <div class="footer">
            Thank you for your business!<br>
            Powered by OilPOS
        </div>
    </div>
</body>
</html>
