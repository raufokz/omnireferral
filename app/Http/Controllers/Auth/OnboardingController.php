<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\OnboardingRequest;
use App\Models\RealtorProfile;
use App\Models\User;
use App\Notifications\AgentCredentialsNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function show(): View
    {
        return view('pages.auth.onboarding-form', [
            'meta' => [
                'title' => 'Complete Onboarding | OmniReferral',
                'description' => 'Complete your OmniReferral onboarding to access your dashboard.',
            ],
        ]);
    }

    public function store(OnboardingRequest $request): RedirectResponse
    {
        $headshotPath = null;
        if ($request->hasFile('upload_picture')) {
            $headshotPath = $request->file('upload_picture')->store('realtor-headshots', 'public');
        }

        $tempPassword = Str::password(12);
        $now = now();

        try {
            DB::beginTransaction();

            $user = User::firstOrNew(['email' => $request->email]);
            $isNew = ! $user->exists;

            $user->fill([
                'name' => $request->full_name,
                'display_name' => $request->full_name,
                'phone' => $request->phone,
                'address_line_1' => $request->office_address,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->postal_code,
                'role' => 'agent',
                'status' => $isNew ? 'pending' : $user->status,
                'avatar' => $headshotPath,
                'onboarding_completed_at' => $now,
                'must_reset_password' => true,
                'password_set_at' => null,
            ]);

            $user->password = $tempPassword;
            $user->save();

            $marketAreas = array_values(array_filter([
                $request->primary_area_of_service,
                $request->secondary_area,
                $request->radius_miles ? $request->radius_miles . ' mile radius' : null,
            ]));

            $bio = $request->full_name . ' is a dedicated real estate professional'
                . ($request->city ? ' serving the ' . $request->city . ' area' : '')
                . ' through ' . ($request->brokerage_name ?? 'OmniReferral') . '.';

            RealtorProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'slug' => RealtorProfile::generateUniqueSlug($request->full_name),
                    'service_city' => $request->city,
                    'service_state' => $request->state,
                    'service_zip_code' => $request->postal_code,
                    'brokerage_name' => $request->brokerage_name,
                    'license_number' => $request->license_number,
                    'years_of_experience' => 2,
                    'languages' => $request->languages,
                    'market_areas' => ! empty($marketAreas) ? implode(', ', $marketAreas) : null,
                    'specialties' => $request->lead_types,
                    'bio' => $bio,
                    'headshot' => $headshotPath,
                    'profile_status' => RealtorProfile::STATUS_DRAFT,
                    'is_active_agent' => true,
                    'submission_source' => 'onboarding_form',
                    'approved_at' => null,
                ]
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Onboarding failed', [
                'error' => $e->getMessage(),
                'email' => $request->email,
            ]);

            return back()->withInput()->withErrors([
                'email' => 'Something went wrong while saving your information. Please try again or contact support.',
            ]);
        }

        try {
            $user->notify(new AgentCredentialsNotification($tempPassword));
        } catch (\Throwable $e) {
            Log::error('Welcome email failed to send', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('login')
            ->with('success', 'Your onboarding is complete! Check your email for your temporary password and sign in to get started.');
    }
}
