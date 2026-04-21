<?php

namespace App\Services;

use App\Models\Lead;
use App\Notifications\LeadStatusUpdatedNotification;
use Illuminate\Support\Facades\Notification;

class LeadCustomerNotifier
{
    public function notifyStatusChangeIfNeeded(Lead $lead, ?string $previousStatus): void
    {
        if ($previousStatus !== null && $previousStatus === $lead->status) {
            return;
        }

        if (! filter_var($lead->email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        Notification::route('mail', $lead->email)
            ->notify(new LeadStatusUpdatedNotification($lead, $previousStatus));
    }
}
