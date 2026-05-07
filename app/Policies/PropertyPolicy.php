<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use App\Support\AuthorizesWithPermissions;

class PropertyPolicy
{
    use AuthorizesWithPermissions;

    public function view(?User $user, Property $property): bool
    {
        if ($property->isApproved() && $property->status === 'Active') {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($this->canStaff($user, 'properties.view')) {
            return true;
        }

        if ($user->isSeller() && (int) $property->owner_user_id === (int) $user->id) {
            return true;
        }

        if ($user->isAgent() && $user->realtorProfile) {
            return (int) $property->realtor_profile_id === (int) $user->realtorProfile->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('properties.create')
            || $user->isSeller()
            || $user->isAgent()
            || $user->isAdmin();
    }

    public function update(User $user, Property $property): bool
    {
        if ($this->canStaff($user, 'properties.update')) {
            return true;
        }

        if ($user->isSeller()) {
            return (int) $property->owner_user_id === (int) $user->id;
        }

        if ($user->isAgent() && $user->realtorProfile) {
            return (int) $property->realtor_profile_id === (int) $user->realtorProfile->id;
        }

        return false;
    }

    public function delete(User $user, Property $property): bool
    {
        if ($this->canAdmin($user, 'properties.delete')) {
            return true;
        }

        return $this->update($user, $property);
    }

    public function review(User $user, Property $property): bool
    {
        return $user->can('properties.review') || $user->isStaff();
    }
}

