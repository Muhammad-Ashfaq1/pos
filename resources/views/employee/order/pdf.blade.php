<!doctype html>
<html>
@php
    $tenant = $order->tenant;
    $shopName = trim((string) (
        $tenant?->shop_name
        ?: $tenant?->business_name
        ?: $tenant?->name
        ?: $tenant?->display_name
        ?: ''
    ));
    $brandName = $shopName !== '' ? $shopName : (string) config('app.name');
    $brandColor = $tenant?->brandPrimaryColor() ?? \App\Models\Tenant::DEFAULT_BRAND_COLOR;
    $brandTagline = $tenant?->brandTagline();
    $logoDataUri = $tenant?->logoDataUri();
    $hasShopContact = $tenant && (
        filled($tenant->address)
        || filled($tenant->city)
        || filled($tenant->business_phone ?? $tenant->phone)
        || filled($tenant->business_email ?? $tenant->email)
    );
@endphp
<head>
    <meta charset="utf-8">
    <title>{{ $order->status === 'estimate' ? 'Estimate' : 'Invoice' }} #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, 'Helvetica Neue', Helvetica, Arial, sans-serif;
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
            border-bottom: 2px solid {{ $brandColor }};
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 28px;
            color: {{ $brandColor }};
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
            color: {{ $brandColor }};
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
            border-top: 2px solid {{ $brandColor }};
            border-bottom: 2px double {{ $brandColor }};
            font-size: 16px;
            font-weight: bold;
            color: {{ $brandColor }};
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
        .brand-logo {
            max-height: 56px;
            max-width: 160px;
            margin-bottom: 8px;
        }
        .brand-tagline {
            font-size: 11px;
            color: #64748b;
            font-weight: normal;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table class="header">
            <tr>
                <td>
                    @if($logoDataUri)
                        <img src="{{ $logoDataUri }}" alt="{{ $brandName }}" class="brand-logo"><br>
                    @endif
                    <div style="margin-bottom: 4px; font-size: 22px; font-weight: 800; color: {{ $brandColor }}; letter-spacing: 0.5px;">
                        {{ $brandName }}
                    </div>
                    @if($brandTagline)
                        <div class="brand-tagline" style="margin-bottom: 10px;">{{ $brandTagline }}</div>
                    @endif
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
                @if($hasShopContact)
                <td class="text-right shop-info">
                    @if($shopName !== '')
                        <div style="font-size: 16px; font-weight: bold; color: {{ $brandColor }}; margin-bottom: 5px;">
                            {{ $shopName }}
                        </div>
                    @endif
                    @if(!empty($tenant->address))
                        {{ $tenant->address }}<br>
                        @if(!empty($tenant->city))
                            {{ $tenant->city }}, {{ $tenant->state ?? '' }} {{ $tenant->country ?? '' }}<br>
                        @endif
                    @endif
                    @if(!empty($tenant->business_phone ?? $tenant->phone))
                        <strong>Phone:</strong> {{ $tenant->business_phone ?? $tenant->phone }}<br>
                    @endif
                    @if(!empty($tenant->business_email ?? $tenant->email))
                        <strong>Email:</strong> {{ $tenant->business_email ?? $tenant->email }}
                    @endif
                </td>
                @endif
            </tr>
        </table>

        <table>
            <tr>
                <td style="width: {{ $order->vehicle ? '50%' : '100%' }};">
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
                @if($order->vehicle)
                <td style="width: 50%;">
                    <div class="section-title">Vehicle</div>
                    <strong>Plate:</strong> {{ $order->vehicle->plate_number }}<br>
                    <strong>Description:</strong> {{ trim(implode(' ', array_filter([$order->vehicle->year, $order->vehicle->make, $order->vehicle->model]))) ?: '—' }}<br>
                    @if(!empty($order->vehicle->registration_number))
                        <strong>Registration:</strong> {{ $order->vehicle->registration_number }}<br>
                    @endif
                    @if(!empty($order->vehicle->odometer))
                        <strong>Odometer:</strong> {{ number_format($order->vehicle->odometer, 1) }} miles
                    @endif
                </td>
                @endif
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
                        <td class="text-right">{{ App\Support\Currency::formatPdf((float) $item->unit_price) }}</td>
                        <td class="text-right">{{ App\Support\Currency::formatPdf((float) $item->line_total) }}</td>
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
                            <td class="text-right">{{ App\Support\Currency::formatPdf((float) ($fee['amount'] ?? 0)) }}</td>
                            <td class="text-right">{{ App\Support\Currency::formatPdf((float) ($fee['amount'] ?? 0)) }}</td>
                        </tr>
                    @endforeach
                @elseif(($order->service_fee_amount ?? 0) > 0)
                    <tr>
                        <td>
                            <div class="fw-bold">Service Fees</div>
                        </td>
                        <td class="text-center">1</td>
                        <td class="text-right">{{ App\Support\Currency::formatPdf((float) $order->service_fee_amount) }}</td>
                        <td class="text-right">{{ App\Support\Currency::formatPdf((float) $order->service_fee_amount) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">{{ App\Support\Currency::formatPdf((float) ($details['subtotal_amount'] ?? $order->subtotal_amount)) }}</td>
            </tr>
            @if(($order->service_fee_amount ?? 0) > 0)
                <tr>
                    <td>Service Fees:</td>
                    <td class="text-right">+{{ App\Support\Currency::formatPdf((float) $order->service_fee_amount) }}</td>
                </tr>
            @endif
            @if(($order->discount_amount ?? 0) > 0)
                <tr style="color: #16a34a;">
                    <td>Discount:</td>
                    <td class="text-right">-{{ App\Support\Currency::formatPdf((float) $order->discount_amount) }}</td>
                </tr>
            @endif
            @if(($order->tax_amount ?? 0) > 0)
                <tr>
                    <td>Tax:</td>
                    <td class="text-right">+{{ App\Support\Currency::formatPdf((float) $order->tax_amount) }}</td>
                </tr>
            @endif
            <tr class="grand-total">
                <td>Total:</td>
                <td class="text-right">{{ App\Support\Currency::formatPdf((float) ($details['total_amount'] ?? $order->total_amount)) }}</td>
            </tr>
            @if($order->status !== 'estimate')
                @if(($order->gift_card_amount ?? 0) > 0)
                    <tr style="color: #16a34a;">
                        <td>{{ data_get($order->card_details, 'gift.name', 'Gift Card') }}:</td>
                        <td class="text-right">-{{ App\Support\Currency::formatPdf((float) $order->gift_card_amount) }}</td>
                    </tr>
                @endif
                <tr>
                    <td>Paid Amount:</td>
                    <td class="text-right">{{ App\Support\Currency::formatPdf((float) ($details['payment_amount'] ?? $order->payment_amount)) }}</td>
                </tr>
                <tr style="font-weight: bold;">
                    <td>Balance Due:</td>
                    <td class="text-right">{{ App\Support\Currency::formatPdf((float) ($details['balance_due'] ?? 0)) }}</td>
                </tr>
            @endif
        </table>

        @if(($order->reward_points_earned ?? 0) > 0)
            <p><strong>{{ data_get($order->card_details, 'reward.name', 'Reward Card') }}:</strong>
                {{ number_format($order->reward_points_earned) }} reward points earned</p>
        @endif

        @if(!empty($order->notes))
            @php
                $notes = $order->notes;
                $returnNotes = [];
                if (is_string($notes)) {
                    $decoded = json_decode($notes, true);
                    if (is_array($decoded)) {
                        // Check if this is return notes
                        if (isset($decoded[0]['return_reason'])) {
                            $returnNotes = $decoded;
                        } else {
                            // Regular notes
                            $notes = $decoded;
                        }
                    }
                }
            @endphp

            @if(!empty($returnNotes))
                <div class="section-title">Return Records</div>
                @foreach($returnNotes as $index => $return)
                    <table class="details-table" style="margin-bottom: 15px; border: 1px solid #e2e8f0;">
                        <thead>
                            <tr>
                                <th colspan="2" style="background-color: #fef2f2; color: #dc2626; font-size: 12px;">
                                    Return #{{ $index + 1 }} - {{ \Carbon\Carbon::parse($return['returned_at'] ?? now())->format('M j, Y h:i A') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="width: 40%; font-weight: bold; background-color: #f8fafc;">Return Reason</td>
                                <td>{{ e($return['return_reason'] ?? 'N/A') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; background-color: #f8fafc;">Refund Method</td>
                                <td>{{ ucfirst(str_replace('_', ' ', e($return['refund_method'] ?? 'N/A'))) }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; background-color: #f8fafc;">Refund Amount</td>
                                <td style="font-weight: bold; color: #dc2626;">{{ App\Support\Currency::formatPdf((float) ($return['refund_amount'] ?? 0)) }}</td>
                            </tr>
                            @if(!empty($return['returned_items']) && is_array($return['returned_items']))
                                <tr>
                                    <td colspan="2" style="font-weight: bold; background-color: #f1f5f9; padding: 8px;">Returned Items</td>
                                </tr>
                                @foreach($return['returned_items'] as $itemId => $qty)
                                    @php
                                        $orderItem = $order->items->firstWhere('id', $itemId);
                                        $itemName = $orderItem ? $orderItem->product_name : 'Unknown Item';
                                        $itemPrice = $orderItem ? (float) $orderItem->unit_price : 0;
                                        $itemTotal = $itemPrice * $qty;
                                    @endphp
                                    <tr>
                                        <td style="padding-left: 20px;">{{ e($itemName) }}</td>
                                        <td>Qty: {{ $qty }} × {{ App\Support\Currency::formatPdf($itemPrice) }} = {{ App\Support\Currency::formatPdf($itemTotal) }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                @endforeach
            @endif

            @if(!empty($notes) && !is_array($returnNotes) || empty($returnNotes))
                <div class="section-title">Notes</div>
                <div style="font-size: 12px; background-color: #f8fafc; border-left: 3px solid #cbd5e1; padding: 10px; color: #475569;">
                    @if(is_array($notes))
                        @foreach($notes as $note)
                            @if(is_string($note))
                                {!! nl2br(e($note)) !!}
                            @elseif(is_array($note))
                                @if(isset($note['return_reason']))
                                    {{-- Skip return notes as they're handled above --}}
                                @else
                                    {!! nl2br(e(json_encode($note, JSON_PRETTY_PRINT))) !!}
                                @endif
                            @endif
                            <br>
                        @endforeach
                    @else
                        {!! nl2br(e($notes)) !!}
                    @endif
                </div>
            @endif
        @endif

        <div class="footer">
            Thank you for your business!<br>
            Powered by {{ config('app.name') }}
        </div>
    </div>
</body>
</html>
