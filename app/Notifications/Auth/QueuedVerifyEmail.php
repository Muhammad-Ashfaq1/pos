<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class QueuedVerifyEmail extends VerifyEmail implements ShouldQueue
{
    public function toMail($notifiable): MailMessage
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->verificationUrl($notifiable));
        }

        $expireMinutes = (int) config('auth.verification.expire', 60);

        return (new MailMessage)
            ->mailer('smtp')
            ->from(
                (string) config('mail.addresses.noreply'),
                (string) config('mail.from.name')
            )
            ->subject('Verify your email address — '.config('app.name'))
            ->view('emails.auth.verify-email', [
                'url' => $this->verificationUrl($notifiable),
                'expireMinutes' => $expireMinutes,
                'name' => $notifiable->name ?? null,
                'title' => 'Verify your email address',
            ]);
    }
}
