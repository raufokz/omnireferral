<?php

namespace App\Services;

use App\Models\Enquiry;
use App\Models\EnquiryReply;
use App\Models\User;
use App\Support\AdminAudit;
use Illuminate\Http\Request;

class EnquiryReplyService
{
    public static function senderDisplay(User $actor, Enquiry $enquiry): string
    {
        if ($actor->isStaff()) {
            return 'OmniReferral · ' . $actor->publicDisplayName();
        }

        if ((int) $actor->id === (int) $enquiry->receiver_user_id) {
            return 'Property owner · ' . $actor->publicDisplayName();
        }

        return $actor->publicDisplayName();
    }

    public static function store(
        Enquiry $enquiry,
        User $actor,
        string $message,
        ?Request $auditRequest = null
    ): EnquiryReply {
        $reply = EnquiryReply::query()->create([
            'enquiry_id' => $enquiry->id,
            'sender_user_id' => $actor->id,
            'sender_display' => self::senderDisplay($actor, $enquiry),
            'message' => $message,
        ]);

        if ($actor->isStaff() || (int) $actor->id === (int) $enquiry->receiver_user_id) {
            $enquiry->markRepliedIfNeeded();
        }

        $enquiry->refresh();

        EnquiryReplyNotifier::notifyParticipants($enquiry->fresh(['property']), $reply->fresh(), $actor);

        if ($auditRequest) {
            AdminAudit::log(
                $auditRequest,
                'enquiries.reply',
                Enquiry::class,
                (int) $enquiry->id,
                ['reply_id' => $reply->id]
            );
        }

        return $reply;
    }
}
