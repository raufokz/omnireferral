<?php

namespace App\Observers;

use App\Models\RealtorProfile;
use App\Models\User;
use App\Support\AgentAvatar;
use Illuminate\Support\Str;

class UserObserver
{
    public function saved(User $user): void
    {
        // Enforce the invariant:
        // - role=agent => exactly one realtor_profile
        // - non-agent => no realtor_profile

        if ($user->role === 'agent') {
            if (! $user->realtorProfile) {
                RealtorProfile::create([
                    'user_id' => $user->id,
                    'slug' => $this->makeSlug($user),
                    'brokerage_name' => 'OmniReferral Partner',
                    'license_number' => null,
                    'service_city' => $user->city,
                    'service_state' => $user->state,
                    'service_zip_code' => $user->zip_code,
                    'specialties' => 'Buyer Representation, Seller Strategy, Lead Conversion',
                    'bio' => 'Agent profile created automatically from the OmniReferral platform.',
                    'headshot' => $user->avatar ? ('storage/' . ltrim($user->avatar, '/')) : AgentAvatar::defaultStorageHeadshot(),
                    'profile_status' => RealtorProfile::STATUS_PUBLISHED,
                ]);
            }

            return;
        }

        if ($user->realtorProfile) {
            // If an admin demotes an agent, remove the agent-only extension record.
            // Downstream FKs should be set to null by existing nullOnDelete constraints.
            $user->realtorProfile()->delete();
        }
    }

    private function makeSlug(User $user): string
    {
        $base = Str::slug($user->publicDisplayName() ?: $user->name ?: 'agent');

        for ($i = 0; $i < 8; $i++) {
            $slug = $base . '-' . Str::lower(Str::random(6));
            if (! RealtorProfile::query()->where('slug', $slug)->exists()) {
                return $slug;
            }
        }

        return $base . '-' . Str::lower(Str::random(10));
    }
}

