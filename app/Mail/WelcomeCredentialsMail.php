<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $temporaryPassword,
        public string $loginUrl,
        public string $dashboardUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.credentials_from.address', 'admin@omnireferrals.com'),
                config('mail.credentials_from.name', 'OmniReferral Admin'),
            ),
            subject: 'Welcome to OmniReferral — Your Account is Ready',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-credentials',
            with: [
                'userName' => $this->user->name,
                'userEmail' => $this->user->email,
                'temporaryPassword' => $this->temporaryPassword,
                'loginUrl' => $this->loginUrl,
                'dashboardUrl' => $this->dashboardUrl,
                'supportEmail' => config('services.omni.support_email', 'admin@omnireferrals.com'),
                'planName' => $this->user->currentPlan?->displayName(),
            ],
        );
    }
}
