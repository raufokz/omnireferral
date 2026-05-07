<?php

namespace App\Policies;

use App\Models\Enquiry;
use App\Models\User;
use App\Support\AuthorizesWithPermissions;

class EnquiryPolicy
{
    use AuthorizesWithPermissions;

    public function viewAny(User $user): bool
    {
        return $user->can('enquiries.view')
            || $user->isStaff()
            || $user->hasAnyRole(['buyer', 'seller', 'agent']);
    }

    public function view(User $user, Enquiry $enquiry): bool
    {
        if ($this->canStaff($user, 'enquiries.view')) {
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
        return $this->reply($user, $enquiry);
    }

    public function export(User $user): bool
    {
        return $user->can('enquiries.export') || $user->isAdmin();
    }
}

