<?php

namespace App\Notifications;

use App\Mail\WelcomeCredentialsMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AgentCredentialsNotification extends Notification
{
    use Queueable;

    public function __construct(protected string $temporaryPassword)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): WelcomeCredentialsMail
    {
        return (new WelcomeCredentialsMail(
            user: $notifiable,
            temporaryPassword: $this->temporaryPassword,
            loginUrl: route('login'),
            dashboardUrl: $notifiable->dashboardRoute(),
        ))->to($notifiable->email);
    }
}
