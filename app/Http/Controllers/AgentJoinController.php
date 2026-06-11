<?php

namespace App\Http\Controllers;

use App\Models\RealtorProfile;
use App\Models\User;
use App\Notifications\AgentProfilePendingReviewNotification;
use App\Support\AgentAvatar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AgentJoinController extends Controller
{
    public function create(): View
    {
        return view('pages.join-as-agent', [
            'specialtyOptions' => $this->specialtyOptions(),
            'meta' => [
                'title' => 'Join as Agent | OmniReferral',
                'description' => 'Apply to join the OmniReferral agent network. Submit your profile for admin review and start receiving qualified referrals.',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('company_website')) {
            return redirect()
                ->route('join-as-agent.success')
                ->with('success', 'Thank you. Your application has been received.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'size:2'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'service_city' => ['required', 'string', 'max:100'],
            'service_state' => ['required', 'string', 'size:2'],
            'service_zip_code' => ['nullable', 'string', 'max:10'],
            'brokerage_name' => ['required', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'languages' => ['nullable', 'string', 'max:255'],
            'market_areas' => ['nullable', 'string', 'max:1000'],
            'specialties' => ['required', 'array', 'min:1'],
            'specialties.*' => ['string', 'max:100'],
            'bio' => ['required', 'string', 'min:80', 'max:1000'],
            'headshot' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'social_facebook' => ['nullable', 'url', 'max:255'],
            'social_linkedin' => ['nullable', 'url', 'max:255'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
            'social_website' => ['nullable', 'url', 'max:255'],
            'terms_accepted' => ['accepted'],
        ], [
            'email.unique' => 'That email is already connected to an OmniReferral account.',
            'bio.min' => 'Tell us a bit more about your experience (at least 80 characters).',
            'specialties.required' => 'Select at least one specialty.',
            'terms_accepted.accepted' => 'Please accept the terms before submitting.',
        ]);

        [$user, $profile] = DB::transaction(function () use ($request, $validated) {
            $headshotPath = AgentAvatar::defaultStorageHeadshot();
            $avatarPath = null;

            if ($request->hasFile('headshot')) {
                $stored = $request->file('headshot')->store('avatars', 'public');
                $avatarPath = $stored;
                $headshotPath = 'storage/'.$stored;
            }

            $user = User::create([
                'name' => $validated['name'],
                'display_name' => $validated['display_name'] ?? null,
                'email' => $validated['email'],
                'password' => $validated['password'],
                'phone' => $validated['phone'] ?? null,
                'city' => $validated['city'],
                'state' => strtoupper($validated['state']),
                'zip_code' => $validated['zip_code'] ?? null,
                'role' => 'agent',
                'status' => 'pending',
                'avatar' => $avatarPath,
                'affiliate_code' => strtoupper(Str::random(8)),
                'social_facebook_url' => $validated['social_facebook'] ?? null,
                'social_linkedin_url' => $validated['social_linkedin'] ?? null,
            ]);

            $profile = RealtorProfile::updateOrCreate(['user_id' => $user->id], [
                'slug' => RealtorProfile::generateUniqueSlug($user->publicDisplayName()),
                'service_city' => $validated['service_city'],
                'service_state' => strtoupper($validated['service_state']),
                'service_zip_code' => $validated['service_zip_code'] ?? null,
                'brokerage_name' => $validated['brokerage_name'],
                'license_number' => $validated['license_number'] ?? null,
                'years_of_experience' => $validated['years_of_experience'] ?? null,
                'languages' => $validated['languages'] ?? null,
                'market_areas' => $validated['market_areas'] ?? null,
                'social_links' => array_filter([
                    'facebook' => $validated['social_facebook'] ?? null,
                    'linkedin' => $validated['social_linkedin'] ?? null,
                    'instagram' => $validated['social_instagram'] ?? null,
                    'website' => $validated['social_website'] ?? null,
                ]),
                'specialties' => RealtorProfile::normalizeSpecialties($validated['specialties']),
                'bio' => $validated['bio'],
                'headshot' => $headshotPath,
                'rating' => 4.5,
                'review_count' => 0,
                'leads_closed' => 0,
                'approved_at' => null,
                'rejected_at' => null,
                'approval_notes' => 'Pending admin review',
            ]);

            return [$user, $profile];
        });

        $this->notifyAdmins($user, $profile);

        return redirect()
            ->route('join-as-agent.success')
            ->with('success', 'Your agent profile has been submitted. An administrator will review it shortly.');
    }

    public function success(): View
    {
        return view('pages.join-as-agent-success', [
            'meta' => [
                'title' => 'Application Received | OmniReferral',
                'description' => 'Your agent profile application has been submitted for admin review.',
            ],
        ]);
    }

    private function notifyAdmins(User $agentUser, RealtorProfile $profile): void
    {
        $admins = User::query()
            ->where('role', 'admin')
            ->where('status', 'active')
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new AgentProfilePendingReviewNotification($agentUser, $profile));
    }

    private function specialtyOptions(): array
    {
        return [
            'Buyer Representation',
            'Seller Strategy',
            'Luxury Homes',
            'First-Time Buyers',
            'Relocation',
            'Investment Properties',
            'New Construction',
            'Commercial',
        ];
    }
}
