<?php

namespace App\Notifications;

use App\Models\Enquiry;
use App\Models\EnquiryReply;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnquiryReplyGuestNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Enquiry $enquiry,
        public EnquiryReply $reply
    ) {
        $this->enquiry->loadMissing('property');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->enquiry->property?->title ?? 'Listing';

        return (new MailMessage)
            ->subject('Reply about your enquiry: ' . $title)
            ->greeting('Hello ' . $this->enquiry->sender_name)
            ->line($this->reply->sender_display . ' posted a reply regarding your inquiry.')
            ->line(\Illuminate\Support\Str::limit(strip_tags($this->reply->message), 600))
            ->line('If you need to follow up further, you can reply to this email or submit another message from the listing page.');
    }
}
