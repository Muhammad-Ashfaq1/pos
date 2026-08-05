<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class QueuedVerifyEmail extends VerifyEmail implements ShouldQueue
{
    public function toMail($notifiable): MailMessage
    {
        $mail = parent::toMail($notifiable);

        return $mail
            ->mailer('smtp')
            ->from(
                (string) config('mail.addresses.noreply'),
                (string) config('mail.from.name')
            );
    }
}
