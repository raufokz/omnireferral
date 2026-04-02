<?php

namespace App\Notifications;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PackagePurchasedNotification extends Notification
{
    use Queueable;

    public function __construct(public Package $package, public string $purchaseUrl)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('OmniReferral Package Purchased: ' . $this->package->name)
            ->greeting('Congratulations, ' . $notifiable->name . '!')
            ->line('Your payment for the ' . $this->package->name . ' package has been received.')
            ->line('We have started preparing your onboarding and lead delivery workflow.')
            ->action('Continue Onboarding', $this->purchaseUrl)
            ->line('If you did not initiate this transaction, please contact support immediately.');
    }
}
