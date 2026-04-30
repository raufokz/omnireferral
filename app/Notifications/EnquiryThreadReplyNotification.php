<?php

namespace App\Notifications;

use App\Models\Enquiry;
use App\Models\EnquiryReply;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnquiryThreadReplyNotification extends Notification
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
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->enquiry->property?->title ?? 'Listing';
        $url = ($notifiable instanceof User && $notifiable->isStaff())
            ? route('admin.enquiries.show', $this->enquiry)
            : route('dashboard.enquiries.show', $this->enquiry);

        return (new MailMessage)
            ->subject('New reply on enquiry #' . $this->enquiry->id)
            ->greeting('Conversation update')
            ->line($this->reply->sender_display . ' replied about: ' . $title)
            ->line(\Illuminate\Support\Str::limit(strip_tags($this->reply->message), 400))
            ->action('View thread', $url);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Enquiry reply',
            'enquiry_id' => $this->enquiry->id,
            'reply_id' => $this->reply->id,
            'from' => $this->reply->sender_display,
        ];
    }
}
