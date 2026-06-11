<?php

namespace App\Notifications;

use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AgentProfilePendingReviewNotification extends Notification
{
    use Queueable;

    public function __construct(
        public User $agentUser,
        public RealtorProfile $profile,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New agent profile pending review: '.$this->agentUser->publicDisplayName())
            ->greeting('Agent profile submitted')
            ->line('A new agent has submitted a profile for admin review.')
            ->line('Name: '.$this->agentUser->publicDisplayName())
            ->line('Email: '.$this->agentUser->email)
            ->line('Brokerage: '.($this->profile->brokerage_name ?: 'Not provided'))
            ->line('Service area: '.$this->profile->serviceAreaLabel())
            ->action('Review profile', route('admin.agent-profiles.show', $this->profile))
            ->line('Approve the profile to publish it in the public agent directory.');
    }
}
