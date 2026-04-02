<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your OmniReferral agent workspace is ready')
            ->greeting('Welcome to OmniReferral, ' . $notifiable->name . '!')
            ->line('Your onboarding form has been completed and your agent account has been provisioned.')
            ->line('Email: ' . $notifiable->email)
            ->line('Temporary password: ' . $this->temporaryPassword)
            ->action('Open Agent Dashboard', route('login'))
            ->line('For security, please sign in and update your password right away.');
    }
}
