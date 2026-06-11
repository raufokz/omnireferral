<?php

namespace App\Policies;

use App\Models\RealtorProfile;
use App\Models\User;

class RealtorProfilePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('realtor_profiles.view') || $actor->isStaff();
    }

    public function view(User $actor, RealtorProfile $profile): bool
    {
        return $this->viewAny($actor);
    }

    public function update(User $actor, RealtorProfile $profile): bool
    {
        if ($actor->can('realtor_profiles.update') || $actor->isAdmin()) {
            return true;
        }

        return $actor->isAgent()
            && (int) $actor->id === (int) $profile->user_id;
    }

    public function approve(User $actor, RealtorProfile $profile): bool
    {
        return ($actor->can('realtor_profiles.approve') || $actor->isAdmin())
            && $profile->user
            && $profile->user->isAgent();
    }

    public function reject(User $actor, RealtorProfile $profile): bool
    {
        return ($actor->can('realtor_profiles.reject') || $actor->isAdmin())
            && $profile->user
            && $profile->user->isAgent();
    }
}
