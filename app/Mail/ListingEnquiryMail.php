<?php

namespace App\Mail;

use App\Models\Lead;
use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListingEnquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Property $property,
        public Lead $lead,
        public string $recipientType
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->recipientType === 'admin'
            ? 'New Enquiry: ' . $this->property->title
            : 'New Enquiry on Your Listing: ' . $this->property->title;

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.listing-enquiry',
            with: [
                'property' => $this->property,
                'lead' => $this->lead,
                'recipientType' => $this->recipientType,
            ],
        );
    }
}
