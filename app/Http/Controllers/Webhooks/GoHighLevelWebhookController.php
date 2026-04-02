<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\SyncUserToGoHighLevel;
use App\Models\Lead;
use App\Models\Package;
use App\Models\RealtorProfile;
use App\Models\User;
use App\Notifications\AgentCredentialsNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GoHighLevelWebhookController extends Controller
{
    public function onboardingCompleted(Request $request): JsonResponse
    {
        if (! $this->isAuthorized($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $email = $request->string('email')->value() ?: data_get($request->all(), 'contact.email');
        abort_unless($email, 422, 'Missing email address.');

        $name = $request->string('name')->value() ?: data_get($request->all(), 'contact.name', 'New OmniReferral Agent');
        $phone = $request->string('phone')->value() ?: data_get($request->all(), 'contact.phone');
        $role = $request->string('role')->value() ?: 'agent';
        $staffTeam = $request->string('staff_team')->value();
        $packageSlug = $request->string('package_slug')->value();
        $packageId = $request->integer('package_id') ?: null;
        $tempPassword = Str::password(14, true, true, false, false);

        $user = User::firstOrNew(['email' => $email]);
        $isNewUser = ! $user->exists;

        $user->fill([
            'name' => $name,
            'phone' => $phone,
            'role' => in_array($role, ['buyer', 'seller', 'agent', 'admin', 'staff'], true) ? $role : 'agent',
            'staff_team' => $staffTeam,
            'status' => 'active',
            'ghl_contact_id' => $request->string('contact_id')->value() ?: data_get($request->all(), 'contact.id'),
            'onboarding_completed_at' => now(),
        ]);

        if ($isNewUser) {
            $user->password = Hash::make($tempPassword);
        }

        $package = $packageId
            ? Package::find($packageId)
            : Package::where('slug', $packageSlug)->first();

        if ($package) {
            $user->current_plan_id = $package->id;
        }

        if (! $user->affiliate_code) {
            $user->affiliate_code = strtoupper(Str::random(8));
        }

        $user->save();

        if ($user->role === 'agent') {
            RealtorProfile::updateOrCreate(['user_id' => $user->id], [
                'slug' => RealtorProfile::where('user_id', $user->id)->value('slug') ?: Str::slug($user->name . '-' . Str::lower(Str::random(6))),
                'brokerage_name' => $request->string('brokerage_name')->value() ?: 'OmniReferral Partner',
                'city' => $request->string('city')->value() ?: 'Dallas',
                'state' => strtoupper($request->string('state')->value() ?: 'TX'),
                'zip_code' => $request->string('zip_code')->value() ?: '75201',
                'specialties' => $request->string('specialties')->value() ?: 'Buyer Representation, Seller Strategy, Lead Conversion',
                'bio' => $request->string('bio')->value() ?: 'Agent profile generated from GoHighLevel onboarding.',
                'headshot' => 'images/realtors/3.png',
            ]);
        }

        SyncUserToGoHighLevel::dispatch($user->id);

        if ($isNewUser) {
            $user->notify(new AgentCredentialsNotification($tempPassword));
        }

        return response()->json([
            'message' => 'Onboarding processed successfully.',
            'user_id' => $user->id,
            'role' => $user->role,
            'dashboard' => $user->dashboardRoute(),
        ]);
    }

    public function leadStatusUpdated(Request $request): JsonResponse
    {
        if (! $this->isAuthorized($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $lead = Lead::query()
            ->when($request->filled('lead_number'), fn ($query) => $query->where('lead_number', $request->string('lead_number')->value()))
            ->when($request->filled('ghl_contact_id'), fn ($query) => $query->orWhere('ghl_contact_id', $request->string('ghl_contact_id')->value()))
            ->first();

        if (! $lead) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $status = $request->string('status')->value();
        if ($status) {
            $lead->status = $status;
        }

        $lead->route_notes = trim((string) $request->string('notes')->value()) ?: $lead->route_notes;
        $lead->contacted_at = $lead->status === 'contacted' ? now() : $lead->contacted_at;
        $lead->closed_at = $lead->status === 'closed' ? now() : $lead->closed_at;
        $lead->save();

        return response()->json(['message' => 'Lead status synced.']);
    }

    private function isAuthorized(Request $request): bool
    {
        $secret = config('services.gohighlevel.webhook_secret');

        if (! $secret) {
            return true;
        }

        return hash_equals($secret, (string) $request->header('X-OmniReferral-Webhook'));
    }
}
