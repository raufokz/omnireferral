<?php

namespace App\Mail;

use App\Models\User;
use App\Services\PasswordSetupService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PortalAccessSetupMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $setupUrl,
        public string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.credentials_from.address', 'admin@omnireferrals.com'),
                config('mail.credentials_from.name', 'OmniReferral Team'),
            ),
            subject: 'Your OmniReferral Portal Access Is Ready',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.portal-access-setup',
            with: [
                'userName'     => $this->user->name,
                'userEmail'    => $this->user->email,
                'setupUrl'     => $this->setupUrl,
                'loginUrl'     => $this->loginUrl,
                'expiresHours' => PasswordSetupService::TTL_HOURS,
                'supportEmail' => config('omnireferral.company.support_email', 'support@omnireferrals.com'),
            ],
        );
    }
}
