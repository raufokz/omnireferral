<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RealtorProfile;
use App\Models\User;
use App\Support\AdminAudit;
use App\Support\AgentAvatar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StaffAgentProfileController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', RealtorProfile::class);

        $status = $request->string('status', 'all')->value();

        $query = RealtorProfile::query()
            ->with(['user:id,name,display_name,email,status', 'createdByUser:id,name'])
            ->latest();

        if ($status !== 'all') {
            $query->where('profile_status', $status);
        }

        return view('pages.admin.agent-profiles.index', [
            'profiles' => $query->paginate(25)->withQueryString(),
            'status' => $status,
            'counts' => [
                'all' => RealtorProfile::count(),
                'draft' => RealtorProfile::draft()->count(),
                'published' => RealtorProfile::published()->count(),
                'featured' => RealtorProfile::featured()->count(),
            ],
            'meta' => [
                'title' => 'Agent Profiles | Admin | OmniReferral',
                'description' => 'Staff workspace for creating and publishing agent directory profiles.',
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('viewAny', RealtorProfile::class);

        return view('pages.admin.agent-profiles.create', [
            'statusOptions' => RealtorProfile::statusOptions(),
            'meta' => ['title' => 'Add Agent Profile | OmniReferral'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', RealtorProfile::class);

        $validated = $this->validatedProfilePayload($request);

        $profile = DB::transaction(function () use ($request, $validated) {
            $slug = RealtorProfile::generateUniqueSlug($validated['name']);
            $email = $validated['email'] ?? ('agent+'.$slug.'@directory.omnireferral.local');

            $headshotPath = AgentAvatar::defaultStorageHeadshot();
            if ($request->hasFile('headshot')) {
                $stored = $request->file('headshot')->store('avatars', 'public');
                $headshotPath = 'storage/'.$stored;
            } elseif (! empty($validated['headshot_url'])) {
                $headshotPath = $validated['headshot_url'];
            }

            $user = User::create([
                'name' => $validated['name'],
                'display_name' => $validated['display_name'] ?? null,
                'email' => $email,
                'password' => Str::password(32),
                'phone' => $validated['phone'] ?? null,
                'city' => $validated['service_city'],
                'state' => strtoupper($validated['service_state']),
                'zip_code' => $validated['service_zip_code'] ?? null,
                'role' => 'agent',
                'status' => 'pending',
                'must_reset_password' => true,
            ]);

            return RealtorProfile::create([
                'user_id' => $user->id,
                'created_by_user_id' => $request->user()?->id,
                'slug' => $slug,
                'service_city' => $validated['service_city'],
                'service_state' => strtoupper($validated['service_state']),
                'service_zip_code' => $validated['service_zip_code'] ?? null,
                'brokerage_name' => $validated['brokerage_name'],
                'license_number' => $validated['license_number'] ?? null,
                'years_of_experience' => $validated['years_of_experience'] ?? null,
                'languages' => $validated['languages'] ?? null,
                'market_areas' => $validated['market_areas'] ?? null,
                'specialties' => RealtorProfile::normalizeSpecialties($validated['specialties'] ?? $validated['specialties_text'] ?? ''),
                'bio' => $validated['bio'],
                'headshot' => $headshotPath,
                'rating' => $validated['rating'] ?? 4.5,
                'review_count' => $validated['review_count'] ?? 0,
                'profile_status' => $validated['profile_status'],
                'source_url' => $validated['source_url'] ?? null,
            ]);
        });

        AdminAudit::log($request, 'realtor_profile.created', 'realtor_profile', $profile->id, [
            'slug' => $profile->slug,
            'profile_status' => $profile->profile_status,
        ]);

        return redirect()
            ->route('admin.agent-profiles.show', $profile)
            ->with('success', 'Agent profile created.');
    }

    public function show(RealtorProfile $agentProfile): View
    {
        $this->authorize('view', $agentProfile);
        $agentProfile->load(['user', 'createdByUser']);

        return view('pages.admin.agent-profiles.show', [
            'profile' => $agentProfile,
            'user' => $agentProfile->user,
            'statusOptions' => RealtorProfile::statusOptions(),
            'canEdit' => auth()->user()?->can('update', $agentProfile) ?? false,
            'meta' => [
                'title' => ($agentProfile->user?->publicDisplayName() ?: 'Agent').' | Profile',
            ],
        ]);
    }

    public function update(Request $request, RealtorProfile $agentProfile): RedirectResponse
    {
        $this->authorize('update', $agentProfile);
        $user = $agentProfile->user;
        abort_unless($user, 404);

        $validated = $this->validatedProfilePayload($request, $user->id);

        if ($request->hasFile('headshot')) {
            $stored = $request->file('headshot')->store('avatars', 'public');
            $agentProfile->headshot = 'storage/'.$stored;
        } elseif (! empty($validated['headshot_url'])) {
            $agentProfile->headshot = $validated['headshot_url'];
        }

        $user->update([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'] ?? null,
            'email' => $validated['email'] ?? $user->email,
            'phone' => $validated['phone'] ?? null,
        ]);

        $agentProfile->update([
            'brokerage_name' => $validated['brokerage_name'],
            'license_number' => $validated['license_number'] ?? null,
            'service_city' => $validated['service_city'],
            'service_state' => strtoupper($validated['service_state']),
            'service_zip_code' => $validated['service_zip_code'] ?? null,
            'years_of_experience' => $validated['years_of_experience'] ?? null,
            'languages' => $validated['languages'] ?? null,
            'market_areas' => $validated['market_areas'] ?? null,
            'specialties' => RealtorProfile::normalizeSpecialties($validated['specialties'] ?? $validated['specialties_text'] ?? ''),
            'bio' => $validated['bio'],
            'rating' => $validated['rating'] ?? $agentProfile->rating,
            'review_count' => $validated['review_count'] ?? $agentProfile->review_count,
            'profile_status' => $validated['profile_status'],
            'source_url' => $validated['source_url'] ?? null,
        ]);

        AdminAudit::log($request, 'realtor_profile.updated', 'realtor_profile', $agentProfile->id);

        return back()->with('success', 'Agent profile updated.');
    }

    public function feature(Request $request, RealtorProfile $agentProfile): RedirectResponse
    {
        $this->authorize('update', $agentProfile);

        $agentProfile->update(['profile_status' => RealtorProfile::STATUS_FEATURED]);

        AdminAudit::log($request, 'realtor_profile.featured', 'realtor_profile', $agentProfile->id);

        return back()->with('success', 'Profile marked as Featured.');
    }

    public function publish(Request $request, RealtorProfile $agentProfile): RedirectResponse
    {
        $this->authorize('update', $agentProfile);

        $agentProfile->update(['profile_status' => RealtorProfile::STATUS_PUBLISHED]);

        return back()->with('success', 'Profile published.');
    }

    private function validatedProfilePayload(Request $request, ?int $userId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'.($userId ? ','.$userId : '')],
            'phone' => ['nullable', 'string', 'max:20'],
            'brokerage_name' => ['required', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'service_city' => ['required', 'string', 'max:100'],
            'service_state' => ['required', 'string', 'size:2'],
            'service_zip_code' => ['nullable', 'string', 'max:10'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'languages' => ['nullable', 'string', 'max:255'],
            'market_areas' => ['nullable', 'string', 'max:1000'],
            'specialties' => ['nullable', 'array'],
            'specialties.*' => ['string', 'max:100'],
            'specialties_text' => ['nullable', 'string', 'max:500'],
            'bio' => ['required', 'string', 'min:40', 'max:2000'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'review_count' => ['nullable', 'integer', 'min:0'],
            'profile_status' => ['required', 'in:draft,published,featured'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'headshot' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'headshot_url' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
