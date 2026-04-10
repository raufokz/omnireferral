<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Property;
use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function overview(): View
    {
        [$user, $profile, $portal] = $this->portalContext();

        return view('pages.dashboards.agent-overview', array_merge($portal, [
            'agentUser' => $user,
            'agentProfile' => $profile,
            'activeAgentPage' => 'overview',
            'recentLeads' => (clone $portal['leadsQuery'])->latest()->take(5)->get(),
            'recentProperties' => $profile->properties()->latest()->take(4)->get(),
            'recentMessages' => (clone $portal['messagesQuery'])->latest()->take(4)->get(),
            'meta' => [
                'title' => 'Agent Overview | OmniReferral',
                'description' => 'Review leads, listing capacity, messages, and account health in your OmniReferral agent workspace.',
            ],
        ]));
    }

    public function profile(): View
    {
        [$user, $profile, $portal] = $this->portalContext();

        return view('pages.dashboards.agent-profile', array_merge($portal, [
            'agentUser' => $user,
            'agentProfile' => $profile,
            'activeAgentPage' => 'profile',
            'meta' => [
                'title' => 'Agent Profile | OmniReferral',
                'description' => 'Manage your OmniReferral agent profile, service area, and public-facing details.',
            ],
        ]));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        [$user, $profile] = $this->portalContext();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'size:2'],
            'zip_code' => ['required', 'string', 'max:10'],
            'brokerage_name' => ['required', 'string', 'max:255'],
            'license_number' => ['required', 'string', 'max:100'],
            'specialties' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'profile_image' => ['nullable', 'image', 'max:4096'],
        ]);

        $avatarPath = $user->avatar;
        $headshotPath = $profile->headshot;

        if ($request->hasFile('profile_image')) {
            $stored = $request->file('profile_image')->store('avatars', 'public');
            $avatarPath = $stored;
            $headshotPath = 'storage/' . $stored;
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address_line_1' => $validated['address_line_1'],
            'address_line_2' => $validated['address_line_2'] ?? null,
            'city' => $validated['city'],
            'state' => strtoupper($validated['state']),
            'zip_code' => $validated['zip_code'],
            'avatar' => $avatarPath,
        ]);

        $profile->update([
            'brokerage_name' => $validated['brokerage_name'],
            'license_number' => $validated['license_number'],
            'address_line_1' => $validated['address_line_1'],
            'address_line_2' => $validated['address_line_2'] ?? null,
            'city' => $validated['city'],
            'state' => strtoupper($validated['state']),
            'zip_code' => $validated['zip_code'],
            'specialties' => $validated['specialties'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'headshot' => $headshotPath,
        ]);

        return redirect()
            ->route('agent.profile')
            ->with('success', 'Your profile has been updated successfully.');
    }

    public function leads(): View
    {
        [$user, $profile, $portal] = $this->portalContext();

        return view('pages.dashboards.agent-leads', array_merge($portal, [
            'agentUser' => $user,
            'agentProfile' => $profile,
            'activeAgentPage' => 'leads',
            'leads' => (clone $portal['leadsQuery'])->latest()->paginate(12),
            'meta' => [
                'title' => 'Agent Leads | OmniReferral',
                'description' => 'Manage all leads assigned to your OmniReferral agent workspace.',
            ],
        ]));
    }

    public function listings(): View
    {
        [$user, $profile, $portal] = $this->portalContext();

        return view('pages.dashboards.agent-listings', array_merge($portal, [
            'agentUser' => $user,
            'agentProfile' => $profile,
            'activeAgentPage' => 'listings',
            'properties' => $profile->properties()->latest()->paginate(9),
            'meta' => [
                'title' => 'Agent Listings | OmniReferral',
                'description' => 'Publish and manage property listings based on your current OmniReferral package access.',
            ],
        ]));
    }

    public function messages(): View
    {
        [$user, $profile, $portal] = $this->portalContext();

        return view('pages.dashboards.agent-messages', array_merge($portal, [
            'agentUser' => $user,
            'agentProfile' => $profile,
            'activeAgentPage' => 'messages',
            'messages' => (clone $portal['messagesQuery'])->latest()->paginate(12),
            'meta' => [
                'title' => 'Agent Messages | OmniReferral',
                'description' => 'Review and manage listing and profile inquiries sent directly to you.',
            ],
        ]));
    }

    public function updateMessageStatus(Request $request, Contact $contact): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user && (int) $contact->recipient_user_id === (int) $user->id, 403);

        $validated = $request->validate([
            'message_status' => ['required', Rule::in(['new', 'read', 'replied', 'archived'])],
        ]);

        $contact->update([
            'message_status' => $validated['message_status'],
        ]);

        return back()->with('success', 'Message status updated.');
    }

    private function portalContext(): array
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isAgent(), 403);

        $profile = $this->ensureAgentProfile($user);
        $activePlan = $user->currentPlan && $user->currentPlan->category === 'lead'
            ? $user->currentPlan
            : null;

        $leadsQuery = Lead::query()->where('assigned_agent_id', $user->id);
        $messagesQuery = Contact::query()
            ->with(['property', 'realtorProfile.user'])
            ->where('recipient_user_id', $user->id);
        $propertiesQuery = Property::query()->where('realtor_profile_id', $profile->id);

        $listingLimit = $activePlan?->listingLimit() ?? 0;
        $activeListingCount = (clone $propertiesQuery)
            ->marketplaceVisible()
            ->count();
        $slotUsageCount = (clone $propertiesQuery)
            ->where('approval_status', '!=', Property::APPROVAL_REJECTED)
            ->whereNotIn('status', ['Sold', 'Off-Market'])
            ->count();
        $pendingReviewCount = (clone $propertiesQuery)
            ->where('approval_status', Property::APPROVAL_PENDING)
            ->count();
        $rejectedListingCount = (clone $propertiesQuery)
            ->where('approval_status', Property::APPROVAL_REJECTED)
            ->count();
        $remainingListingSlots = max($listingLimit - $slotUsageCount, 0);
        $totalLeads = (clone $leadsQuery)->count();
        $contactedLeads = (clone $leadsQuery)->whereIn('status', ['contacted', 'qualified', 'closed'])->count();
        $totalMessages = (clone $messagesQuery)->count();

        $pipeline = [
            ['label' => 'New', 'count' => (clone $leadsQuery)->where('status', 'new')->count()],
            ['label' => 'Contacted', 'count' => (clone $leadsQuery)->where('status', 'contacted')->count()],
            ['label' => 'Qualified', 'count' => (clone $leadsQuery)->where('status', 'qualified')->count()],
            ['label' => 'Closed', 'count' => (clone $leadsQuery)->where('status', 'closed')->count()],
        ];

        return [
            $user,
            $profile,
            [
                'activePlan' => $activePlan,
                'leadsQuery' => $leadsQuery,
                'messagesQuery' => $messagesQuery,
                'listingLimit' => $listingLimit,
                'listingLimitLabel' => $activePlan?->listingLimitLabel() ?? 'No listing access',
                'activeListingCount' => $activeListingCount,
                'slotUsageCount' => $slotUsageCount,
                'pendingReviewCount' => $pendingReviewCount,
                'rejectedListingCount' => $rejectedListingCount,
                'remainingListingSlots' => $remainingListingSlots,
                'canCreateListings' => $listingLimit > 0 && $remainingListingSlots > 0,
                'totalMessagesCount' => $totalMessages,
                'unreadMessagesCount' => (clone $messagesQuery)->where('message_status', 'new')->count(),
                'pipeline' => $pipeline,
                'agentStats' => [
                    'score' => number_format((float) ($profile->rating ?? 4.9), 1),
                    'leads_received' => $totalLeads,
                    'response_rate' => $totalLeads > 0 ? round(($contactedLeads / $totalLeads) * 100) . '%' : '0%',
                    'closed_leads' => (clone $leadsQuery)->where('status', 'closed')->count(),
                    'messages_received' => $totalMessages,
                ],
            ],
        ];
    }

    private function ensureAgentProfile(User $user): RealtorProfile
    {
        return RealtorProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'slug' => Str::slug($user->name . '-' . Str::lower(Str::random(6))),
                'brokerage_name' => 'OmniReferral Partner',
                'license_number' => 'Pending',
                'address_line_1' => $user->address_line_1,
                'address_line_2' => $user->address_line_2,
                'city' => $user->city ?: 'Dallas',
                'state' => $user->state ?: 'TX',
                'zip_code' => $user->zip_code ?: '75201',
                'specialties' => 'Buyer Representation, Seller Strategy, Lead Conversion',
                'bio' => 'Agent profile created in the OmniReferral workspace.',
                'headshot' => $user->avatar ? 'storage/' . $user->avatar : 'images/realtors/3.png',
            ]
        );
    }
}
