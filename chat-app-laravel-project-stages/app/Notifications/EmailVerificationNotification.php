<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EmailVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Your Email Address - Whistle It')
            ->view('emails.verification', [
                'user' => $notifiable,
                'verificationUrl' => $verificationUrl
            ]);
    }

    protected function verificationUrl($notifiable)
    {
        $id = $notifiable->getKey();
        $hash = sha1($notifiable->getEmailForVerification());

        return url("/api/email/verify/{$id}/{$hash}");
    }
}
