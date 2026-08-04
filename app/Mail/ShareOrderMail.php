<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\Currency;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShareOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        protected string $pdfContent,
        protected string $filename
    ) {
        $this->order->loadMissing(['customer:id,name,email', 'tenant', 'creator:id,name']);
    }

    public function envelope(): Envelope
    {
        $shopName = $this->shopName();
        $documentLabel = $this->documentLabel();

        return new Envelope(
            subject: "{$documentLabel} #{$this->order->order_number} from {$shopName}",
        );
    }

    public function content(): Content
    {
        $tenant = $this->order->tenant;
        $totalAmount = (float) $this->order->total_amount;
        $paymentAmount = (float) $this->order->payment_amount;
        $balanceDue = max($totalAmount - $paymentAmount, 0);
        $isEstimate = $this->order->status === Order::STATUS_ESTIMATE;
        $documentDate = $this->order->invoice_date ?? $this->order->created_at;

        return new Content(
            view: 'emails.orders.shared',
            with: [
                'order' => $this->order,
                'documentLabel' => $this->documentLabel(),
                'customerName' => trim((string) ($this->order->customer?->name ?? '')) ?: null,
                'shopName' => $shopName = $this->shopName(),
                'brandName' => $shopName,
                'shopEmail' => $shopEmail = ($tenant?->business_email ?: $tenant?->email ?: $tenant?->owner_email),
                'shopPhone' => $shopPhone = ($tenant?->business_phone ?: $tenant?->phone ?: $tenant?->owner_phone),
                'shopAddress' => $shopAddress = $tenant?->address,
                'senderName' => $this->order->creator?->name,
                'documentDateLabel' => $documentDate?->format('M d, Y') ?? '—',
                'statusLabel' => $isEstimate
                    ? 'Estimate'
                    : str((string) $this->order->status)->replace('_', ' ')->title()->toString(),
                'totalAmountLabel' => Currency::format($totalAmount, true, $tenant),
                'balanceDue' => $balanceDue,
                'balanceDueLabel' => Currency::format($balanceDue, true, $tenant),
                'title' => $this->documentLabel().' #'.$this->order->order_number,
                'footerLines' => array_values(array_filter([
                    $shopName,
                    $shopPhone ? 'Phone: '.$shopPhone : null,
                    $shopEmail ? 'Email: '.$shopEmail : null,
                    $shopAddress,
                ])),
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->filename)
                ->withMime('application/pdf'),
        ];
    }

    private function documentLabel(): string
    {
        if ($this->order->status === Order::STATUS_ESTIMATE) {
            return 'Estimate';
        }

        if ($this->order->is_invoice) {
            return 'Invoice';
        }

        return 'Invoice';
    }

    private function shopName(): string
    {
        $tenant = $this->order->tenant;

        return $tenant?->display_name
            ?: $tenant?->name
            ?: $tenant?->business_name
            ?: $tenant?->shop_name
            ?: (string) config('app.name');
    }
}
