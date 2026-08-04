@extends('emails.layout')

@section('content')
    <h1 class="email-title">{{ $documentLabel ?? 'Document' }} #{{ $order->order_number }}</h1>

    <p class="email-text">
        Hello{{ ! empty($customerName) ? ' '.$customerName : '' }},
    </p>

    <p class="email-text">
        Please find attached the PDF for
        <strong>{{ $documentLabel ?? 'Document' }} #{{ $order->order_number }}</strong>
        from <strong>{{ $shopName ?? config('app.name') }}</strong>.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 0 0 20px; border: 1px solid rgba(15, 23, 42, 0.08); border-radius: 12px; overflow: hidden;">
        <tr>
            <td style="padding: 14px 16px; background: #f8fafc; font-size: 13px; color: #64748b; width: 40%;">Date</td>
            <td style="padding: 14px 16px; font-size: 14px; color: #0b1220; font-weight: 600;">{{ $documentDateLabel ?? '—' }}</td>
        </tr>
        <tr>
            <td style="padding: 14px 16px; background: #f8fafc; font-size: 13px; color: #64748b; border-top: 1px solid rgba(15, 23, 42, 0.06);">Status</td>
            <td style="padding: 14px 16px; font-size: 14px; color: #0b1220; font-weight: 600; border-top: 1px solid rgba(15, 23, 42, 0.06);">{{ $statusLabel ?? '—' }}</td>
        </tr>
        <tr>
            <td style="padding: 14px 16px; background: #f8fafc; font-size: 13px; color: #64748b; border-top: 1px solid rgba(15, 23, 42, 0.06);">Total Amount</td>
            <td style="padding: 14px 16px; font-size: 14px; color: #0b1220; font-weight: 700; border-top: 1px solid rgba(15, 23, 42, 0.06);">{{ $totalAmountLabel ?? '—' }}</td>
        </tr>
        @if(! empty($balanceDueLabel) && ($balanceDue ?? 0) > 0)
        <tr>
            <td style="padding: 14px 16px; background: #f8fafc; font-size: 13px; color: #64748b; border-top: 1px solid rgba(15, 23, 42, 0.06);">Balance Due</td>
            <td style="padding: 14px 16px; font-size: 14px; color: #0b1220; font-weight: 700; border-top: 1px solid rgba(15, 23, 42, 0.06);">{{ $balanceDueLabel }}</td>
        </tr>
        @endif
    </table>

    <p class="email-text">
        If you have any questions about this {{ strtolower($documentLabel ?? 'document') }}, reply to this email or contact the shop using the details below.
    </p>

    <p class="email-text" style="margin-bottom: 4px;">
        Thanks,<br />
        <strong>{{ $shopName ?? config('app.name') }}</strong>
        @if(! empty($senderName))
            <br /><span style="color: #64748b; font-size: 13px;">Sent by {{ $senderName }}</span>
        @endif
    </p>

    @if(! empty($shopPhone) || ! empty($shopEmail) || ! empty($shopAddress))
        <p class="email-text" style="margin-top: 16px; margin-bottom: 0; font-size: 13px; color: #64748b;">
            @if(! empty($shopAddress)){{ $shopAddress }}<br />@endif
            @if(! empty($shopPhone))Phone: {{ $shopPhone }}@endif
            @if(! empty($shopPhone) && ! empty($shopEmail)) &middot; @endif
            @if(! empty($shopEmail))Email: {{ $shopEmail }}@endif
        </p>
    @endif
@endsection
