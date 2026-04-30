<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Enquiry;
use App\Models\Property;
use App\Models\User;
use App\Notifications\EnquiryCreatedNotification;
use Illuminate\Support\Facades\Notification;

class EnquiryFromContactService
{
    public static function createFromContact(Contact $contact, ?int $senderUserId = null): ?Enquiry
    {
        if (! $contact->property_id || ! $contact->recipient_user_id) {
            return null;
        }

        if (Enquiry::query()->where('contact_id', $contact->id)->exists()) {
            return Enquiry::query()->where('contact_id', $contact->id)->first();
        }

        $property = Property::query()->find($contact->property_id);
        if (! $property) {
            return null;
        }

        $receiverId = (int) ($property->owner_user_id ?: $contact->recipient_user_id);
        $receiver = User::query()->find($receiverId);
        if (! $receiver) {
            return null;
        }

        $enquiry = Enquiry::create([
            'property_id' => $property->id,
            'contact_id' => $contact->id,
            'sender_user_id' => $senderUserId,
            'sender_name' => $contact->name,
            'sender_email' => $contact->email,
            'sender_phone' => $contact->phone,
            'receiver_user_id' => $receiverId,
            'subject' => $contact->subject,
            'message' => $contact->message,
            'status' => Enquiry::STATUS_PENDING,
        ]);

        $recipients = User::query()
            ->whereIn('role', ['admin', 'staff'])
            ->get();

        if (! $recipients->contains(fn (User $u) => (int) $u->id === $receiverId)) {
            $recipients->push($receiver);
        }

        $recipients = $recipients->unique('id')->values();

        Notification::send($recipients, new EnquiryCreatedNotification($enquiry->fresh(['property'])));

        return $enquiry;
    }
}
