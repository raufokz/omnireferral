<?php

namespace App\Services;

use App\Models\Enquiry;
use App\Models\EnquiryReply;
use App\Models\User;
use App\Notifications\EnquiryReplyGuestNotification;
use App\Notifications\EnquiryThreadReplyNotification;
use Illuminate\Support\Facades\Notification;

class EnquiryReplyNotifier
{
    public static function notifyParticipants(Enquiry $enquiry, EnquiryReply $reply, User $actor): void
    {
        $enquiry->loadMissing(['property']);

        $users = collect();

        if ($enquiry->receiver_user_id && (int) $enquiry->receiver_user_id !== (int) $actor->id) {
            $receiver = User::query()->find($enquiry->receiver_user_id);
            if ($receiver) {
                $users->push($receiver);
            }
        }

        if ($enquiry->sender_user_id && (int) $enquiry->sender_user_id !== (int) $actor->id) {
            $sender = User::query()->find($enquiry->sender_user_id);
            if ($sender) {
                $users->push($sender);
            }
        }

        foreach (User::query()->whereIn('role', ['admin', 'staff'])->where('id', '!=', $actor->id)->cursor() as $admin) {
            $users->push($admin);
        }

        $users = $users->filter()->unique('id')->values();

        if ($users->isNotEmpty()) {
            Notification::send($users, new EnquiryThreadReplyNotification($enquiry, $reply));
        }

        if (! $enquiry->sender_user_id && $enquiry->sender_email) {
            $shouldEmailGuest = $actor->isStaff() || (int) $actor->id === (int) $enquiry->receiver_user_id;
            if ($shouldEmailGuest) {
                Notification::route('mail', $enquiry->sender_email)
                    ->notify(new EnquiryReplyGuestNotification($enquiry, $reply));
            }
        }
    }
}
