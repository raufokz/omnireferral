<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\SendPortalAccessSetupEmailJob;
use App\Jobs\SyncUserToGoHighLevel;
use App\Models\GhlSetting;
use App\Models\Lead;
use App\Models\Package;
use App\Models\RealtorProfile;
use App\Models\User;
use App\Notifications\NewAgentOnboardingNotification;
use App\Services\LeadCustomerNotifier;
use App\Services\OnboardingSyncService;
use App\Services\PasswordProvisioningService;
use App\Services\WebhookInboxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OnboardingWebhookPayload',
    type: 'object',
    required: ['email'],
    properties: [
        new OA\Property(property: 'contact_id', type: 'string', description: 'GoHighLevel contact ID'),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'first_name', type: 'string'),
        new OA\Property(property: 'last_name', type: 'string'),
        new OA\Property(property: 'phone', type: 'string'),
        new OA\Property(property: 'role', type: 'string', enum: ['agent', 'realtor', 'buyer', 'seller']),
        new OA\Property(property: 'city', type: 'string'),
        new OA\Property(property: 'state', type: 'string'),
        new OA\Property(property: 'zip_code', type: 'string'),
        new OA\Property(property: 'brokerage_name', type: 'string'),
        new OA\Property(property: 'license_number', type: 'string'),
        new OA\Property(property: 'specialties', type: 'string'),
        new OA\Property(property: 'bio', type: 'string'),
        new OA\Property(property: 'form_id', type: 'string'),
        new OA\Property(property: 'form_name', type: 'string'),
    ]
)]
#[OA\Schema(
    schema: 'PurchaseWebhookPayload',
    type: 'object',
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'phone', type: 'string'),
        new OA\Property(property: 'role', type: 'string'),
        new OA\Property(property: 'package_slug', type: 'string'),
        new OA\Property(property: 'package_id', type: 'integer'),
        new OA\Property(property: 'contact_id', type: 'string'),
        new OA\Property(property: 'brokerage_name', type: 'string'),
        new OA\Property(property: 'city', type: 'string'),
        new OA\Property(property: 'state', type: 'string'),
    ]
)]
class GoHighLevelWebhookController extends Controller
{
    public function __construct(
        private readonly OnboardingSyncService     $syncService,
        private readonly PasswordProvisioningService $passwordService,
    ) {}

    #[OA\Post(
        path: '/webhooks/gohighlevel/purchase',
        tags: ['Webhooks', 'GoHighLevel'],
        summary: 'Handle package purchase webhook from GoHighLevel',
        description: 'Receives a package purchase notification from GHL, creates/updates the user and realtor profile, dispatches sync and setup email.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/PurchaseWebhookPayload')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Purchase processed successfully'),
            new OA\Response(response: 401, description: 'Unauthorized - invalid webhook secret'),
            new OA\Response(response: 422, description: 'Missing email address'),
            new OA\Response(response: 500, description: 'Account provisioning failed'),
        ]
    )]
    public function packagePurchased(Request $request): JsonResponse
    {
        if (! $this->isAuthorized($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $record = $this->recordWebhook($request, 'package_purchased');

        if ($record->processed_at) {
            return response()->json(['message' => 'Duplicate webhook ignored.'], 200);
        }

        $email = $request->string('email')->value() ?: data_get($request->all(), 'contact.email');
        if (! $email) {
            Log::warning('GHL package_purchased webhook missing email.', ['payload' => $request->all()]);

            return response()->json(['message' => 'Missing email address.'], 422);
        }

        try {
            $result = DB::transaction(function () use ($request, $email) {
                $name        = $request->string('name')->value() ?: data_get($request->all(), 'contact.name', 'New OmniReferral Agent');
                $phone       = $request->string('phone')->value() ?: data_get($request->all(), 'contact.phone');
                $packageSlug = $request->string('package_slug')->value() ?: data_get($request->all(), 'package.slug');
                $packageId   = $request->integer('package_id') ?: data_get($request->all(), 'package.id');

                $user      = User::firstOrNew(['email' => $email]);
                $isNewUser = ! $user->exists;

                $user->fill([
                    'name'                => $name,
                    'phone'               => $phone,
                    'role'                => $this->syncService->normalizeRole($request->string('role')->value() ?: 'agent'),
                    'status'              => 'pending',
                    'ghl_contact_id'      => $request->string('contact_id')->value() ?: data_get($request->all(), 'contact.id'),
                    'onboarding_completed_at' => null,
                    'must_reset_password' => $isNewUser ? true : (bool) $user->must_reset_password,
                    'email_verified_at'   => $user->email_verified_at ?? now(),
                    'city'                => $request->string('city')->value() ?: $user->city,
                    'state'               => strtoupper($request->string('state')->value() ?: ($user->state ?? '')),
                    'zip_code'            => $request->string('zip_code')->value() ?: $user->zip_code,
                ]);

                $plainPassword = null;
                if ($isNewUser) {
                    $plainPassword = $this->passwordService->provision($user);
                }

                $package = $packageId
                    ? Package::find($packageId)
                    : ($packageSlug ? Package::where('slug', $packageSlug)->first() : null);

                if ($package) {
                    $user->current_plan_id = $package->id;
                }

                if (! $user->affiliate_code) {
                    $user->affiliate_code = strtoupper(Str::random(8));
                }

                $user->save();

                RealtorProfile::updateOrCreate(['user_id' => $user->id], [
                    'slug'            => RealtorProfile::where('user_id', $user->id)->value('slug') ?: Str::slug($user->name . '-' . Str::lower(Str::random(6))),
                    'brokerage_name'  => $request->string('brokerage_name')->value() ?: 'OmniReferral Partner',
                    'service_city'    => $request->string('city')->value() ?: ($user->city ?: 'Dallas'),
                    'service_state'   => strtoupper($request->string('state')->value() ?: ($user->state ?: 'TX')),
                    'service_zip_code' => $request->string('zip_code')->value() ?: ($user->zip_code ?: '75201'),
                    'specialties'     => $request->string('specialties')->value() ?: 'Buyer Representation, Seller Strategy, Lead Conversion',
                    'bio'             => $request->string('bio')->value() ?: 'Agent profile created after package purchase.',
                    'headshot'        => \App\Support\AgentAvatar::defaultStorageHeadshot(),
                ]);

                return [
                    'user'          => $user,
                    'isNewUser'     => $isNewUser,
                    'plainPassword' => $plainPassword,
                ];
            });

            $user = $result['user'];

            SyncUserToGoHighLevel::dispatch($user->id);

            if ($result['isNewUser']) {
                SendPortalAccessSetupEmailJob::dispatch(
                    userId: $user->id,
                    onboardingLogId: null,
                    via: 'package_purchase',
                );
            }

            if ($result['isNewUser'] && $user->role === 'agent') {
                $adminUsers = User::where('role', 'admin')->get();
                Notification::send($adminUsers, new NewAgentOnboardingNotification($user));
            }

            app(WebhookInboxService::class)->markProcessed($record);

            Log::info('GHL package_purchased processed.', ['user_id' => $user->id, 'email' => $email, 'new_user' => $result['isNewUser']]);

            return response()->json([
                'message'       => 'Purchase processed successfully.',
                'user_id'       => $user->id,
                'role'          => $user->role,
                'onboarding_url'=> route('login'),
                'dashboard_url' => $user->dashboardRoute(),
                'login_url'     => route('login'),
            ]);
        } catch (\Throwable $e) {
            Log::error('GHL package_purchased failed.', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => mb_substr($e->getTraceAsString(), 0, 2000),
            ]);

            return response()->json(['message' => 'Account provisioning failed. Our team has been notified.'], 500);
        }
    }

    #[OA\Post(
        path: '/webhooks/gohighlevel/onboarding',
        tags: ['Webhooks', 'GoHighLevel'],
        summary: 'Receive onboarding completion from GoHighLevel',
        description: 'Handles onboarding form submission webhook. Creates/updates user, realtor/buyer profile, generates password token, and queues setup email.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/OnboardingWebhookPayload')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Onboarding processed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'user_id', type: 'integer'),
                        new OA\Property(property: 'role', type: 'string'),
                        new OA\Property(property: 'dashboard', type: 'string'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized - invalid webhook secret'),
            new OA\Response(response: 422, description: 'Missing email'),
            new OA\Response(response: 500, description: 'Account provisioning failed'),
        ]
    )]
    public function onboardingCompleted(Request $request): JsonResponse
    {
        if (! $this->isAuthorized($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $record = $this->recordWebhook($request, 'onboarding_completed');

        if ($record->processed_at) {
            return response()->json(['message' => 'Duplicate webhook ignored.'], 200);
        }

        $email = $request->string('email')->value() ?: data_get($request->all(), 'contact.email');
        if (! $email) {
            Log::warning('GHL onboarding_completed webhook: email missing from payload.', ['payload' => $request->all()]);

            return response()->json(['message' => 'Email missing from GoHighLevel payload.'], 422);
        }

        try {
            $payload      = $request->except(['password', 'token', 'webhook_secret']);
            $explicitUserId = $request->integer('field_user_id') ?: $request->integer('user_id') ?: null;

            $result = DB::transaction(
                fn () => $this->syncService->sync($payload, $explicitUserId)
            );

            $user = $result['user'];

            SyncUserToGoHighLevel::dispatch($user->id);

            if ($result['shouldSendSetup']) {
                SendPortalAccessSetupEmailJob::dispatch(
                    userId: $user->id,
                    onboardingLogId: $result['onboardingLog']->id ?? null,
                    via: 'ghl_onboarding',
                );
            }

            app(WebhookInboxService::class)->markProcessed($record);

            Log::info('GHL onboarding_completed processed.', [
                'user_id'          => $user->id,
                'email'            => $email,
                'new_user'         => $result['isNewUser'],
                'first_onboarding' => $result['isFirstOnboarding'],
                'role'             => $user->role,
            ]);

            return response()->json([
                'message'   => 'Onboarding processed successfully.',
                'user_id'   => $user->id,
                'role'      => $user->role,
                'dashboard' => $user->dashboardRoute(),
            ]);
        } catch (\Throwable $e) {
            Log::error('GHL onboarding_completed failed.', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => mb_substr($e->getTraceAsString(), 0, 2000),
            ]);

            return response()->json(['message' => 'Account provisioning failed. Our team has been notified.'], 500);
        }
    }

    #[OA\Post(
        path: '/webhooks/gohighlevel/lead-status',
        tags: ['Webhooks', 'GoHighLevel'],
        summary: 'Receive lead status update from GoHighLevel',
        description: 'Updates lead status (contacted, closed, etc.) and notifies the customer if needed.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'lead_number', type: 'string'),
                    new OA\Property(property: 'ghl_contact_id', type: 'string'),
                    new OA\Property(property: 'status', type: 'string', enum: ['new', 'contacted', 'qualified', 'closed', 'lost']),
                    new OA\Property(property: 'notes', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Lead status synced'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Lead not found'),
        ]
    )]
    public function leadStatusUpdated(Request $request): JsonResponse
    {
        if (! $this->isAuthorized($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $payload    = $request->all();
        $rawPayload = $request->getContent();
        $headers    = collect($request->headers->all())
            ->map(fn ($values) => is_array($values) ? (count($values) === 1 ? $values[0] : $values) : $values)
            ->toArray();

        $inbox    = app(WebhookInboxService::class);
        $remoteId = (string) ($request->input('id')
            ?? data_get($payload, 'id')
            ?? data_get($payload, 'contact.id')
            ?? data_get($payload, 'contact_id')
            ?? $request->input('ghl_contact_id')
            ?? $request->input('lead_number')
            ?? '');

        $record = $inbox->recordInbound(
            provider: 'gohighlevel',
            event: 'lead_status_updated',
            remoteId: $remoteId !== '' ? $remoteId : null,
            rawPayload: $rawPayload,
            payload: is_array($payload) ? $payload : [],
            headers: $headers,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            related: null,
        );

        if ($record->processed_at) {
            return response()->json(['message' => 'Duplicate webhook ignored.'], 200);
        }

        $lead = Lead::query()
            ->when($request->filled('lead_number'), fn ($query) => $query->where('lead_number', $request->string('lead_number')->value()))
            ->when($request->filled('ghl_contact_id'), fn ($query) => $query->orWhere('ghl_contact_id', $request->string('ghl_contact_id')->value()))
            ->first();

        if (! $lead) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $previousStatus     = $lead->status;
        $status             = $request->string('status')->value();
        if ($status) {
            $lead->status = $status;
        }

        $lead->route_notes  = trim((string) $request->string('notes')->value()) ?: $lead->route_notes;
        $lead->contacted_at = $lead->status === 'contacted' ? now() : $lead->contacted_at;
        $lead->closed_at    = $lead->status === 'closed' ? now() : $lead->closed_at;
        $lead->save();

        app(LeadCustomerNotifier::class)->notifyStatusChangeIfNeeded($lead->fresh(), $previousStatus);

        $inbox->markProcessed($record);

        return response()->json(['message' => 'Lead status synced.']);
    }

    private function recordWebhook(Request $request, string $event): \App\Models\WebhookEvent
    {
        $payload    = $request->all();
        $rawPayload = $request->getContent();
        $headers    = collect($request->headers->all())
            ->map(fn ($values) => is_array($values) ? (count($values) === 1 ? $values[0] : $values) : $values)
            ->toArray();

        $remoteId = (string) ($request->input('id')
            ?? $request->input('contact_id')
            ?? data_get($payload, 'id')
            ?? data_get($payload, 'contact.id')
            ?? data_get($payload, 'contact_id')
            ?? '');

        return app(WebhookInboxService::class)->recordInbound(
            provider: 'gohighlevel',
            event: $event,
            remoteId: $remoteId !== '' ? $remoteId : null,
            rawPayload: $rawPayload,
            payload: is_array($payload) ? $payload : [],
            headers: $headers,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            related: null,
        );
    }

    /**
     * Validate the incoming webhook secret header.
     * Checks DB-stored GhlSetting first, falls back to .env config.
     */
    private function isAuthorized(Request $request): bool
    {
        $dbSetting = GhlSetting::instance();
        $secret = '';

        try {
            $secret = $dbSetting->webhook_secret ? trim((string) $dbSetting->webhook_secret) : '';
        } catch (\Throwable) {
        }

        if ($secret === '') {
            $secret = trim((string) config('services.gohighlevel.webhook_secret'));
        }

        $header = (string) $request->header('X-OmniReferral-Webhook', '');

        if ($secret === '') {
            return app()->environment(['local', 'testing']);
        }

        if (! hash_equals($secret, $header)) {
            return false;
        }

        if (! config('services.gohighlevel.webhook_require_nonce')) {
            return true;
        }

        $nonce = trim((string) $request->header('X-OmniReferral-Webhook-Nonce', ''));

        return $nonce !== '';
    }
}
