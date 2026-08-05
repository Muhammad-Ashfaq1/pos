<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sends a set-password link to a portal customer. Used for both the staff
 * invite flow and the self-service forgot-password flow.
 */
class CustomerPortalLinkNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $link,
        private readonly string $shopName,
        private readonly bool $isInvite,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->mailer('smtp')
            ->from(
                (string) config('mail.addresses.noreply'),
                (string) config('mail.from.name')
            )
            ->subject($this->isInvite
                ? "Activate your {$this->shopName} customer account"
                : "Reset your {$this->shopName} customer password");

        if ($this->isInvite) {
            $message
                ->greeting('Welcome!')
                ->line("{$this->shopName} has set up a customer portal account for you, where you can view your service history and store-credit balance.")
                ->action('Set your password', $this->link)
                ->line('This link expires in 60 minutes.');
        } else {
            $message
                ->line("You requested a password reset for your {$this->shopName} customer account.")
                ->action('Reset password', $this->link)
                ->line('This link expires in 60 minutes. If you did not request this, you can ignore this email.');
        }

        return $message;
    }
}
