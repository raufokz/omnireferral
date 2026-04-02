<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadFollowUpReminderNotification extends Notification
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
            ->subject('Follow-up reminder: Lead #' . $this->lead->lead_number)
            ->greeting('Follow-up reminder for ' . $this->lead->name)
            ->line('This lead needs attention. Please follow up as soon as possible.')
            ->line('Intent: ' . ucfirst($this->lead->intent))
            ->line('Status: ' . ucfirst($this->lead->status))
            ->line('ZIP code: ' . $this->lead->zip_code)
            ->line('Agent assigned: ' . ($this->lead->assignedAgent?->name ?? 'Unassigned'))
            ->action('View admin lead pipeline', route('admin.dashboard'))
            ->line('Tip: Add a note and update status after reaching out.');
    }
}
