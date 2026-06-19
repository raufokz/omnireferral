<?php

namespace App\Notifications;

use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEnquiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Enquiry $enquiry
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $property = $this->enquiry->property;
        $senderName = $this->enquiry->sender_name;
        $senderEmail = $this->enquiry->sender_email;
        $senderPhone = $this->enquiry->sender_phone;
        $message = $this->enquiry->message;

        return (new MailMessage)
            ->subject('New Property Enquiry: ' . ($property?->title ?? 'Property Listing'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have received a new property enquiry.')
            ->line('**Property:** ' . ($property?->title ?? 'N/A'))
            ->line('**From:** ' . $senderName . ' (' . $senderEmail . ')')
            ->when($senderPhone, fn ($mail) => $mail->line('**Phone:** ' . $senderPhone))
            ->line('**Message:**')
            ->line($message)
            ->action('View Enquiry', route('admin.enquiries.show', $this->enquiry))
            ->line('Thank you for using OmniReferral.');
    }
}
