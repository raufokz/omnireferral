<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeadAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public Lead $lead)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Lead Assigned: ' . $this->lead->lead_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have been assigned a new lead for follow-up.')
            ->line('Lead name: ' . $this->lead->name)
            ->line('Intent: ' . ucfirst($this->lead->intent))
            ->line('ZIP: ' . $this->lead->zip_code)
            ->line('Package: ' . ucfirst($this->lead->package_type))
            ->line('Status: ' . ucfirst($this->lead->status))
            ->action('Open Agent Dashboard', route('dashboard.agent'))
            ->line('Please connect with this lead within 24 hours for best conversion.');
    }
}
