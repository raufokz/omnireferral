<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeadCreatedNotification extends Notification
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
            ->subject('New Lead: #' . $this->lead->lead_number . ' (' . ucfirst($this->lead->intent) . ')')
            ->greeting('New lead received: ' . $this->lead->name)
            ->line('A lead has been created in OmniReferral with the following details:')
            ->line('Intent: ' . ucfirst($this->lead->intent))
            ->line('Package: ' . ucfirst($this->lead->package_type))
            ->line($this->lead->locationLabel() . ': ' . $this->lead->locationSummary())
            ->line(($this->lead->intent === 'seller' ? 'Asking price' : 'Budget') . ': ' . ($this->lead->intent === 'seller'
                ? ($this->lead->asking_price ? '$' . number_format($this->lead->asking_price) : 'N/A')
                : ($this->lead->budget ? '$' . number_format($this->lead->budget) : 'N/A')))
            ->line('Timeline: ' . ($this->lead->timeline ?: 'N/A'))
            ->line('Status: ' . ucfirst($this->lead->status))
            ->action('View Lead', route('admin.dashboard'))
            ->line('Assigned agent: ' . ($this->lead->assignedAgent?->name ?: 'Unassigned'));
    }
}
