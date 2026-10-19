<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(public string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/reset-password').'?'.http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Reset Password KosKu')
            ->greeting('Halo '.$notifiable->name.'!')
            ->line('Kami menerima permintaan reset password untuk akun KosKu kamu.')
            ->action('Reset Password', $url)
            ->line("Tautan ini berlaku selama {$expire} menit.")
            ->line('Jika kamu tidak meminta reset password, abaikan saja email ini.');
    }
}
