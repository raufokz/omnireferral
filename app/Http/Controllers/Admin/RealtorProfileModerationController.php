<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RealtorProfile;
use App\Models\User;
use App\Support\AdminAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RealtorProfileModerationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', RealtorProfile::class);

        $status = $request->string('status', 'pending')->value();

        $query = RealtorProfile::query()
            ->with(['user:id,name,display_name,email,phone,status,role,created_at'])
            ->whereHas('user', fn ($user) => $user->agents())
            ->latest();

        $profiles = match ($status) {
            'approved' => $query->clone()->publicEligible()->paginate(20)->withQueryString(),
            'rejected' => $query->clone()->whereNotNull('rejected_at')->paginate(20)->withQueryString(),
            default => $query->clone()->pendingReview()->paginate(20)->withQueryString(),
        };

        return view('pages.admin.agent-profiles.index', [
            'profiles' => $profiles,
            'status' => $status,
            'counts' => [
                'pending' => RealtorProfile::pendingReview()->count(),
                'approved' => RealtorProfile::publicEligible()->count(),
                'rejected' => RealtorProfile::query()->whereNotNull('rejected_at')->count(),
            ],
            'meta' => [
                'title' => 'Agent Profiles | Admin | OmniReferral',
                'description' => 'Review, approve, and manage agent profile submissions.',
            ],
        ]);
    }

    public function show(RealtorProfile $agentProfile): View
    {
        $this->authorize('view', $agentProfile);
        $agentProfile->load(['user', 'approvedByUser', 'rejectedByUser']);

        return view('pages.admin.agent-profiles.show', [
            'profile' => $agentProfile,
            'user' => $agentProfile->user,
            'canApprove' => auth()->user()?->can('approve', $agentProfile) ?? false,
            'canReject' => auth()->user()?->can('reject', $agentProfile) ?? false,
            'canEdit' => auth()->user()?->can('update', $agentProfile) ?? false,
            'meta' => [
                'title' => ($agentProfile->user?->publicDisplayName() ?: 'Agent').' | Profile Review',
                'description' => 'Review agent profile submission and approval status.',
            ],
        ]);
    }

    public function update(Request $request, RealtorProfile $agentProfile): RedirectResponse
    {
        $this->authorize('update', $agentProfile);
        $user = $agentProfile->user;
        abort_unless($user, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'brokerage_name' => ['required', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'service_city' => ['required', 'string', 'max:100'],
            'service_state' => ['required', 'string', 'size:2'],
            'service_zip_code' => ['nullable', 'string', 'max:10'],
            'specialties' => ['required', 'string', 'max:500'],
            'bio' => ['required', 'string', 'min:80', 'max:1000'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'languages' => ['nullable', 'string', 'max:255'],
            'market_areas' => ['nullable', 'string', 'max:1000'],
            'approval_notes' => ['nullable', 'string', 'max:1000'],
            'rating' => ['nullable', 'numeric', 'min:3', 'max:5'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        $agentProfile->update([
            'brokerage_name' => $validated['brokerage_name'],
            'license_number' => $validated['license_number'] ?? null,
            'service_city' => $validated['service_city'],
            'service_state' => strtoupper($validated['service_state']),
            'service_zip_code' => $validated['service_zip_code'] ?? null,
            'specialties' => $validated['specialties'],
            'bio' => $validated['bio'],
            'years_of_experience' => $validated['years_of_experience'] ?? null,
            'languages' => $validated['languages'] ?? null,
            'market_areas' => $validated['market_areas'] ?? null,
            'approval_notes' => $validated['approval_notes'] ?? $agentProfile->approval_notes,
            'rating' => $validated['rating'] ?? max(3.0, (float) ($agentProfile->rating ?? 3.0)),
        ]);

        AdminAudit::log($request, 'realtor_profile.updated', 'realtor_profile', $agentProfile->id, [
            'user_id' => $user->id,
            'slug' => $agentProfile->slug,
        ]);

        return back()->with('success', 'Agent profile updated.');
    }

    public function approve(Request $request, RealtorProfile $agentProfile): RedirectResponse
    {
        $this->authorize('approve', $agentProfile);
        $actor = $request->user();
        $user = $agentProfile->user;
        abort_unless($user && $actor, 403);

        $validated = $request->validate([
            'approval_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $user->update(['status' => 'active']);

        $agentProfile->update([
            'approved_at' => now(),
            'approved_by_user_id' => $actor->id,
            'rejected_at' => null,
            'rejected_by_user_id' => null,
            'approval_notes' => $validated['approval_notes'] ?? 'Approved by admin',
            'rating' => max(3.0, (float) ($agentProfile->rating ?? 3.0)),
        ]);

        AdminAudit::log($request, 'realtor_profile.approved', 'realtor_profile', $agentProfile->id, [
            'user_id' => $user->id,
            'slug' => $agentProfile->slug,
        ]);

        return back()->with('success', "{$user->publicDisplayName()}'s profile is approved and publicly visible.");
    }

    public function reject(Request $request, RealtorProfile $agentProfile): RedirectResponse
    {
        $this->authorize('reject', $agentProfile);
        $actor = $request->user();
        $user = $agentProfile->user;
        abort_unless($user && $actor, 403);

        $validated = $request->validate([
            'approval_notes' => ['required', 'string', 'max:1000'],
            'suspend_user' => ['nullable', 'boolean'],
        ]);

        $user->update([
            'status' => $request->boolean('suspend_user') ? 'suspended' : 'pending',
        ]);

        $agentProfile->update([
            'rejected_at' => now(),
            'rejected_by_user_id' => $actor->id,
            'approved_at' => null,
            'approved_by_user_id' => null,
            'approval_notes' => $validated['approval_notes'],
        ]);

        AdminAudit::log($request, 'realtor_profile.rejected', 'realtor_profile', $agentProfile->id, [
            'user_id' => $user->id,
            'slug' => $agentProfile->slug,
        ]);

        return back()->with('success', "{$user->publicDisplayName()}'s profile has been rejected.");
    }

    public function activateUser(Request $request, RealtorProfile $agentProfile): RedirectResponse
    {
        $this->authorize('approve', $agentProfile);
        $user = $agentProfile->user;
        abort_unless($user, 404);

        $user->update(['status' => 'active']);

        AdminAudit::log($request, 'realtor_profile.user_activated', 'user', $user->id);

        return back()->with('success', "{$user->publicDisplayName()}'s account is now active.");
    }

    public function suspendUser(Request $request, RealtorProfile $agentProfile): RedirectResponse
    {
        $this->authorize('reject', $agentProfile);
        $user = $agentProfile->user;
        abort_unless($user, 404);

        $user->update(['status' => 'suspended']);

        AdminAudit::log($request, 'realtor_profile.user_suspended', 'user', $user->id);

        return back()->with('success', "{$user->publicDisplayName()}'s account has been suspended.");
    }
}
