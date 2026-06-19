<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAgentOnboardingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Agent Onboarding: ' . $this->user->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new agent has completed onboarding via GoHighLevel.')
            ->line('**Agent Name:** ' . $this->user->name)
            ->line('**Email:** ' . $this->user->email)
            ->line('**Phone:** ' . ($this->user->phone ?? 'N/A'))
            ->line('**Status:** Pending Approval')
            ->line('The agent account is currently pending and requires admin activation.')
            ->action('Review Agent', route('admin.users.index', ['status' => 'pending']))
            ->line('Thank you for using OmniReferral.');
    }
}
