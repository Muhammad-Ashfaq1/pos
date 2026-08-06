<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $status,
        private readonly string $shopName,
        private readonly ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->mailer('support')
            ->from(
                (string) config('mail.addresses.info'),
                (string) config('mail.from.name')
            )
            ->subject("{$this->shopName} account {$this->status}")
            ->view('emails.tenants.status-changed', [
                'name' => $notifiable->name ?? null,
                'shopName' => $this->shopName,
                'status' => $this->status,
                'reason' => $this->reason,
                'title' => "Shop account {$this->status}",
            ]);
    }
}
