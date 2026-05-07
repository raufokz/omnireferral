<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function view(?User $user, Property $property): bool
    {
        if ($property->isApproved() && $property->status === 'Active') {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->isStaff()) {
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
        // Mirrors current behavior: seller/agent can submit listings; admin can create directly via admin UI.
        return $user->isSeller() || $user->isAgent() || $user->isAdmin();
    }

    public function update(User $user, Property $property): bool
    {
        if ($user->isStaff()) {
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
        // Public deletes should be restricted. Admin can delete from registry; seller/agent can soft-delete their own.
        if ($user->isAdmin()) {
            return true;
        }

        return $this->update($user, $property);
    }

    public function review(User $user, Property $property): bool
    {
        return $user->isStaff();
    }
}

