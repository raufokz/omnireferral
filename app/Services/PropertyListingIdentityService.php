<?php

namespace App\Services;

use App\Models\Property;
use App\Models\RealtorProfile;

/**
 * Keeps listing presentation keys aligned: when a property is tied to a realtor profile,
 * the listing agent user ("listed by") must be that profile's user.
 *
 * owner_user_id: economic/listing owner (often seller user on seller-submitted listings).
 * realtor_profile_id: public agent profile / brokerage context.
 * listed_by_id: must match realtor_profiles.user_id when realtor_profile_id is set.
 */
final class PropertyListingIdentityService
{
    public static function syncListedByFromRealtorProfile(Property $property): void
    {
        if (! $property->realtor_profile_id) {
            return;
        }

        $profile = $property->relationLoaded('realtorProfile')
            ? $property->realtorProfile
            : RealtorProfile::query()->find($property->realtor_profile_id);

        if (! $profile) {
            return;
        }

        $agentUserId = (int) $profile->user_id;
        if ($agentUserId < 1) {
            return;
        }

        if ((int) $property->listed_by_id === $agentUserId) {
            return;
        }

        $property->forceFill(['listed_by_id' => $agentUserId])->saveQuietly();
    }
}
