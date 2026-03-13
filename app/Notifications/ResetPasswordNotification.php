<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $user;
    public $token;
    public $resetUrl;

    public function __construct($user, $token)
    {
        $this->user = $user;
        $this->token = $token;
        $this->resetUrl = url("/api/reset-password/{$token}");
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Reset Password')
            ->view('emails.otp-forgot-password', [
                'name' => $this->user->name,
                'otp' => $this->token,
            ]);
    }
}
