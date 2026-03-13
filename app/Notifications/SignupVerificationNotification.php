<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SignupVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $user;
    public $token;
    public $verificationUrl;

    public function __construct($user, $token)
    {
        $this->user = $user;
        $this->token = $token;
        $this->verificationUrl = url("/api/verify-signup/{$token}");
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Signup Verification Email')
            ->view('emails.otp-registration', [
                'name' => $this->user->name,
                'otp' => $this->token,
            ]);
    }
}
