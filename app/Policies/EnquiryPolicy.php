<?php

namespace App\Policies;

use App\Models\Enquiry;
use App\Models\User;

class EnquiryPolicy
{
    public function viewAny(User $user): bool
    {
        // Buyer/seller/agent can use their own threads; staff/admin can use operational queues.
        return $user->isStaff() || $user->hasAnyRole(['buyer', 'seller', 'agent']);
    }

    public function view(User $user, Enquiry $enquiry): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return (int) $user->id === (int) $enquiry->receiver_user_id
            || ($enquiry->sender_user_id && (int) $user->id === (int) $enquiry->sender_user_id);
    }

    public function reply(User $user, Enquiry $enquiry): bool
    {
        if (! $this->view($user, $enquiry)) {
            return false;
        }

        return $enquiry->status !== Enquiry::STATUS_CLOSED;
    }

    public function close(User $user, Enquiry $enquiry): bool
    {
        if (! $this->view($user, $enquiry)) {
            return false;
        }

        return $enquiry->status !== Enquiry::STATUS_CLOSED;
    }

    public function export(User $user): bool
    {
        return $user->isAdmin();
    }
}

