<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class QueuedResetPassword extends ResetPassword implements ShouldQueue
{
    public function toMail($notifiable): MailMessage
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        $expireMinutes = (int) config(
            'auth.passwords.'.config('auth.defaults.passwords').'.expire',
            60
        );

        return (new MailMessage)
            ->mailer('smtp')
            ->from(
                (string) config('mail.addresses.noreply'),
                (string) config('mail.from.name')
            )
            ->subject('Reset your password — '.config('app.name'))
            ->view('emails.auth.reset-password', [
                'url' => $this->resetUrl($notifiable),
                'expireMinutes' => $expireMinutes,
                'name' => $notifiable->name ?? null,
                'title' => 'Reset your password',
            ]);
    }
}
