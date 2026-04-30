<?php

namespace App\Notifications;

use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnquiryCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public Enquiry $enquiry)
    {
        $this->enquiry->loadMissing('property');
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->enquiry->property?->title ?? 'Listing';
        $url = ($notifiable instanceof User && $notifiable->isStaff())
            ? route('admin.enquiries.show', $this->enquiry)
            : route('dashboard.enquiries.show', $this->enquiry);

        return (new MailMessage)
            ->subject('New property enquiry: ' . $title)
            ->greeting('New enquiry')
            ->line('From: ' . $this->enquiry->sender_name . ' <' . $this->enquiry->sender_email . '>')
            ->line('Property: ' . $title)
            ->line('Preview: ' . \Illuminate\Support\Str::limit(strip_tags($this->enquiry->message), 200))
            ->action('Open conversation', $url);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New property enquiry',
            'enquiry_id' => $this->enquiry->id,
            'property_id' => $this->enquiry->property_id,
            'sender' => $this->enquiry->sender_name,
        ];
    }
}
