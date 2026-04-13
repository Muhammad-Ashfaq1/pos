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
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("{$this->shopName} account {$this->status}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your shop account for {$this->shopName} is now {$this->status}.");

        if ($this->reason) {
            $mail->line("Reason: {$this->reason}");
        }

        return $mail->line('If you believe this is a mistake, please contact support.');
    }
}
