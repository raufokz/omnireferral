<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        // Admin/staff operational views; agents via agent portal; buyers/sellers via their own dashboards.
        return $user->isStaff() || $user->hasAnyRole(['agent', 'buyer', 'seller']);
    }

    public function view(User $user, Lead $lead): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        if ($user->isAgent()) {
            return (int) $lead->assigned_agent_id === (int) $user->id;
        }

        if ($user->isBuyer() || $user->isSeller()) {
            $userEmail = Lead::normalizeEmail($user->email);
            $userPhone = Lead::normalizePhone($user->phone);

            $leadEmail = Lead::normalizeEmail($lead->email);
            $leadPhone = Lead::normalizePhone($lead->phone);

            return ($userEmail && $leadEmail && $userEmail === $leadEmail)
                || ($userPhone && $leadPhone && $userPhone === $leadPhone);
        }

        return false;
    }

    public function updateStatus(User $user, Lead $lead): bool
    {
        return $user->isStaff();
    }

    public function assign(User $user, Lead $lead): bool
    {
        return $user->isStaff();
    }

    public function addActivity(User $user, Lead $lead): bool
    {
        return $user->isStaff();
    }
}

