# Hello {{ $order->customer->name ?? 'Valued Customer' }},

Please find attached the PDF document for {{ $order->status === 'estimate' ? 'Estimate' : 'Invoice' }} **#{{ $order->order_number }}** from our shop.

**Summary Details:**
- **Date:** {{ $order->created_at->format('M d, Y') }}
- **Status:** {{ ucfirst($order->status) }}
- **Total Amount:** {{ App\Support\Currency::format($order->total_amount) }}

If you have any questions, feel free to contact us.

Thanks,  
{{ $order->tenant->name ?? 'Our Shop' }}
