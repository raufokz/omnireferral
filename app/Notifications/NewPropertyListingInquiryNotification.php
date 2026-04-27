<?php

namespace App\Notifications;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPropertyListingInquiryNotification extends Notification
{
    use Queueable;

    public function __construct(public Contact $contact)
    {
        $this->contact->loadMissing(['property', 'realtorProfile.user']);
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $propertyTitle = $this->contact->property?->title ?? 'Listing';
        $agentName = $this->contact->realtorProfile?->user?->name ?? 'Listing agent';

        return (new MailMessage)
            ->subject('Property inquiry: ' . $propertyTitle)
            ->greeting('New listing inquiry')
            ->line('A visitor submitted a message about ' . $propertyTitle . '.')
            ->line('From: ' . $this->contact->name . ' <' . $this->contact->email . '>')
            ->line('Assigned agent: ' . $agentName)
            ->line('Subject: ' . ($this->contact->subject ?: '(no subject)'))
            ->line('Message:')
            ->line(strip_tags($this->contact->message))
            ->when(
                $this->contact->property,
                fn (MailMessage $mail) => $mail->action(
                    'View listing',
                    route('properties.show', $this->contact->property)
                )
            );
    }
}
