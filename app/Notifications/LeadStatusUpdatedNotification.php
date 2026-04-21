<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Lead $lead,
        public ?string $previousStatus = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->lead;

        return (new MailMessage)
            ->subject('Your OmniReferral request was updated')
            ->greeting('Hi ' . $lead->name . ',')
            ->line('We have an update on your ' . strtoupper((string) $lead->intent) . ' request (' . $lead->lead_number . ').')
            ->when($this->previousStatus, fn (MailMessage $m) => $m->line('Previous status: ' . str_replace('_', ' ', (string) $this->previousStatus)))
            ->line('Current status: ' . $lead->statusLabel())
            ->line('Thank you for using OmniReferral.');
    }
}
