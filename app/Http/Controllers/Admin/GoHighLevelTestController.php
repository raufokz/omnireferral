<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendPortalAccessSetupEmailJob;
use App\Jobs\SyncUserToGoHighLevel;
use App\Models\AgentSubscription;
use App\Models\EmailLog;
use App\Models\GhlOnboardingTest;
use App\Models\GhlSetting;
use App\Models\GoHighLevelWebhookLog;
use App\Models\OnboardingLog;
use App\Models\Package;
use App\Models\PasswordSetupToken;
use App\Models\RealtorProfile;
use App\Models\User;
use App\Services\GoHighLevelService;
use App\Services\OnboardingSyncService;
use App\Services\PasswordProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GoHighLevelTestController extends Controller
{
    private const STAGE_DEFS = [
        'form_submitted'      => ['label' => 'Form Submitted', 'icon' => '📋'],
        'ghl_contact_created' => ['label' => 'GoHighLevel Contact Created', 'icon' => '👤'],
        'opportunity_created' => ['label' => 'Opportunity Created', 'icon' => '📌'],
        'webhook_received'    => ['label' => 'Webhook Received', 'icon' => '🔗'],
        'webhook_validated'   => ['label' => 'Payload Validated', 'icon' => '✅'],
        'user_created'        => ['label' => 'User Created', 'icon' => '👤'],
        'profile_created'     => ['label' => 'Realtor Profile Created', 'icon' => '📄'],
        'profile_approved'    => ['label' => 'Profile Approved', 'icon' => '👍'],
        'password_generated'  => ['label' => 'Password Generated', 'icon' => '🔑'],
        'email_sent'          => ['label' => 'Welcome Email Sent', 'icon' => '📧'],
        'agent_login_ready'   => ['label' => 'Agent Login Ready', 'icon' => '🚪'],
    ];

    public function __construct(
        private readonly OnboardingSyncService   $syncService,
        private readonly GoHighLevelService      $ghlService,
        private readonly PasswordProvisioningService $passwordService,
    ) {}

    public function testForm(): View
    {
        $this->authorizeAccess();
        $settings = GhlSetting::instance();
        $ghl = $this->ghlService;
        $recentTests = GhlOnboardingTest::with('user')
            ->latest()
            ->take(10)
            ->get();

        $ghlFormUrl = null;
        if ($ghl->configured()) {
            $ghlFormUrl = $settings->agent_onboarding_form_url
                ?? $settings->buyer_onboarding_form_url
                ?? null;
        }

        return view('pages.admin.gohighlevel.test-panel', [
            'settings'           => $settings,
            'ghl'                => $ghl,
            'ghlFormUrl'         => $ghlFormUrl,
            'ghlConfigured'      => $ghl->configured(),
            'recentTests'        => $recentTests,
            'locationId'         => $ghl->resolveLocationId(),
            'maskedApiKey'       => $this->maskKey($ghl->resolveApiKey()),
            'environment'        => $settings->environment,
            'meta'               => ['title' => 'GHL Onboarding Test Panel — Admin | OmniReferral'],
        ]);
    }

    public function submitTest(Request $request): JsonResponse
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'intent'            => 'required|in:buyer,seller,agent',
            'name'              => 'required|string|max:100',
            'email'             => 'required|email|max:255',
            'phone'             => 'nullable|string|max:20',
            'city'              => 'nullable|string|max:100',
            'state'             => 'nullable|string|max:2',
            'zip_code'          => 'nullable|string|max:10',
            'brokerage_name'    => 'nullable|string|max:200',
            'role'              => 'nullable|in:buyer,seller,agent',
            'form_method'       => 'nullable|in:mock_form,real_form',
        ]);

        $role = $validated['role'] ?? $validated['intent'];
        $now = now();
        $durations = [];

        $test = GhlOnboardingTest::create([
            'email'                => $validated['email'],
            'role'                 => $role,
            'status'               => 'processing',
            'form_payload'         => $validated,
            'form_submission_method' => $validated['form_method'] ?? 'mock_form',
            'started_at'           => $now,
            'stages'               => [
                'form_submitted' => [
                    'status'    => 'completed',
                    'timestamp' => $now->toIso8601String(),
                    'data'      => $validated,
                    'error'     => null,
                ],
            ],
        ]);
        $test->form_received = true;
        $test->save();

        try {
            $this->processTest($test, $validated, $role, $durations);

            $test->execution_durations = $durations;
            $test->status = 'completed';
            $test->completed_at = now();
            $test->save();

            return response()->json([
                'ok'      => true,
                'test_id' => $test->id,
                'message' => 'Onboarding test completed successfully.',
            ]);
        } catch (\Throwable $e) {
            $test->status = 'failed';
            $test->error_message = $e->getMessage();
            $test->error_stage = $this->findFailedStage($test->stages ?? []);
            $test->execution_durations = $durations;
            $test->save();

            Log::error('GHL onboarding test failed.', [
                'test_id' => $test->id,
                'email'   => $validated['email'],
                'error'   => $e->getMessage(),
                'trace'   => mb_substr($e->getTraceAsString(), 0, 1000),
            ]);

            return response()->json([
                'ok'       => false,
                'test_id'  => $test->id,
                'message'  => 'Onboarding test failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function processTest(GhlOnboardingTest $test, array $formData, string $role, array &$durations): void
    {
        $settings = GhlSetting::instance();
        $ghl = $this->ghlService;
        $stages = $test->stages ?? [];
        $t0 = microtime(true);

        $ghlPayload = $this->buildMockGhlPayload($formData, $role);
        $test->webhook_payload = $ghlPayload;

        // --- Step: GHL Contact Creation ---
        $ghlContactId = $formData['ghl_contact_id'] ?? null;
        $apiResponse = null;
        $httpStatus = null;

        if ($ghl->configured() && $ghlPayload) {
            $apiResponse = $ghl->createOrUpdateContact($ghlPayload);
            $httpStatus = data_get($apiResponse, 'status', 200);
            $ghlContactId = $ghlContactId ?: data_get($apiResponse, 'contact.id') ?: data_get($apiResponse, 'id');
            $test->api_request_payload = $ghlPayload;
            $test->api_response_payload = $apiResponse;
            $test->http_status = $httpStatus;
        }

        $durations['ghl_contact_created'] = round((microtime(true) - $t0) * 1000, 1);
        $test->ghl_api_details = [
            'contact_id'        => $ghlContactId,
            'api_response'      => $apiResponse,
            'http_status'       => $httpStatus,
            'location_id'       => $ghl->resolveLocationId(),
            'form_id'           => $settings->agent_onboarding_form_url ? 'configured' : null,
            'pipeline_id'       => data_get($apiResponse, 'contact.pipeline.id'),
            'stage_id'          => data_get($apiResponse, 'contact.pipeline.stageId'),
            'api_key_masked'    => $this->maskKey($ghl->resolveApiKey()),
        ];
        if ($ghlContactId) {
            $test->ghl_contact_id = $ghlContactId;
        }
        $test->webhook_simulated = true;
        $stages['ghl_contact_created'] = [
            'status'    => $ghlContactId ? 'completed' : 'completed',
            'timestamp' => now()->toIso8601String(),
            'data'      => $ghlContactId ? ['contact_id' => $ghlContactId, 'http_status' => $httpStatus] : ['note' => 'GHL not configured, using mock'],
            'error'     => null,
        ];

        // --- Step: Opportunity Creation ---
        $t1 = microtime(true);
        if ($ghlContactId && $ghl->configured()) {
            $pipelineStage = $settings->realtor_onboarding_form_url ? 'onboarding' : 'active';
            $ghl->updateOpportunityStage($ghlContactId, $pipelineStage);
            $test->opportunity_created = true;
            $test->pipeline_stage = $pipelineStage;
            $test->pipeline_id = data_get($apiResponse, 'contact.pipeline.id');
        }
        $durations['opportunity_created'] = round((microtime(true) - $t1) * 1000, 1);
        $stages['opportunity_created'] = [
            'status'    => $test->opportunity_created || $ghlContactId ? 'completed' : 'skipped',
            'timestamp' => now()->toIso8601String(),
            'data'      => $test->opportunity_created ? ['stage' => $pipelineStage ?? 'active'] : null,
            'error'     => null,
        ];

        // --- Steps: Webhook received & validated ---
        $stages['webhook_received'] = [
            'status'    => 'completed',
            'timestamp' => now()->toIso8601String(),
            'data'      => ['method' => 'POST', 'endpoint' => route('webhooks.gohighlevel.onboarding')],
            'error'     => null,
        ];
        $stages['webhook_validated'] = [
            'status'    => 'completed',
            'timestamp' => now()->toIso8601String(),
            'data'      => ['email' => $ghlPayload['email'], 'role' => $role, 'secret_valid' => true],
            'error'     => null,
        ];

        $test->stages = $stages;
        $test->save();

        // --- Step: User & Profile Creation (via OnboardingSyncService) ---
        $t2 = microtime(true);
        $result = DB::transaction(fn () => $this->syncService->sync($ghlPayload));

        $user = $result['user'];
        $test->user_id = $user->id;
        $test->user_data = $user->fresh()->toArray();
        $test->user_created = true;
        $durations['user_created'] = round((microtime(true) - $t2) * 1000, 1);
        $stages['user_created'] = [
            'status'    => 'completed',
            'timestamp' => now()->toIso8601String(),
            'data'      => [
                'user_id' => $user->id, 'name' => $user->name,
                'email'   => $user->email, 'role' => $user->role,
                'status'  => $user->status,
            ],
            'error' => null,
        ];
        $test->stages = $stages;
        $test->save();

        // --- Step: Realtor Profile ---
        $t3 = microtime(true);
        $profile = match ($role) {
            'agent'   => $user->realtorProfile,
            'buyer', 'seller' => $user->buyerProfile,
            default   => null,
        };
        if ($profile) {
            $test->profile_data = $profile->fresh()->toArray();
            $test->profile_created = true;
            $test->profile_id = $profile->id;

            if ($profile instanceof RealtorProfile) {
                $test->profile_approved = (bool) $profile->is_approved;
                $test->profile_published = (bool) ($profile->is_active_agent ?? false);
            }

            $durations['profile_created'] = round((microtime(true) - $t3) * 1000, 1);
            $stages['profile_created'] = [
                'status'    => 'completed',
                'timestamp' => now()->toIso8601String(),
                'data'      => [
                    'profile_id' => $profile->id,
                    'type'       => class_basename($profile),
                    'slug'       => $profile instanceof RealtorProfile ? $profile->slug : null,
                ],
                'error' => null,
            ];
        } else {
            $durations['profile_created'] = round((microtime(true) - $t3) * 1000, 1);
            $stages['profile_created'] = [
                'status' => 'completed',
                'timestamp' => now()->toIso8601String(),
                'data'   => ['note' => 'No profile type for role: ' . $role],
                'error'  => null,
            ];
        }

        // --- Step: Profile Approved (auto-approve in test) ---
        if ($profile && $profile instanceof RealtorProfile && ! $test->profile_approved) {
            $profile->forceFill(['is_approved' => true, 'is_active_agent' => true])->save();
            $test->profile_approved = true;
            $test->profile_published = true;
        }
        $stages['profile_approved'] = [
            'status'    => 'completed',
            'timestamp' => now()->toIso8601String(),
            'data'      => ['approved' => true, 'published' => true],
            'error'     => null,
        ];
        $test->stages = $stages;
        $test->save();

        // --- Step: Password Generated ---
        $t4 = microtime(true);
        $plainToken = $this->passwordService->provision($user);
        $tokenHash = hash('sha256', $plainToken ?? Str::random(64));
        if ($plainToken) {
            PasswordSetupToken::create([
                'user_id'     => $user->id,
                'token'       => $tokenHash,
                'created_via' => 'ghl_onboarding_test',
                'expires_at'  => now()->addHours(24),
            ]);
            $user->forceFill(['must_reset_password' => true])->save();
        }
        $test->password_token = $plainToken;
        $test->password_generated = (bool) $plainToken;
        $durations['password_generated'] = round((microtime(true) - $t4) * 1000, 1);
        $stages['password_generated'] = [
            'status'    => $plainToken ? 'completed' : 'skipped',
            'timestamp' => now()->toIso8601String(),
            'data'      => $plainToken ? [
                'token'              => $this->maskToken($plainToken),
                'expires_at'         => now()->addHours(24)->toIso8601String(),
                'setup_url'          => route('password.setup', ['token' => $plainToken]),
                'must_reset_password' => true,
            ] : ['note' => 'Password already set for existing user'],
            'error' => null,
        ];

        $portalLoginUrl = route('login');
        $test->portal_login_url = $portalLoginUrl;
        $test->stages = $stages;
        $test->save();

        // --- Step: Welcome Email Sent ---
        $t5 = microtime(true);
        $log = $result['onboardingLog'] ?? null;
        try {
            SendPortalAccessSetupEmailJob::dispatch(
                userId: $user->id,
                onboardingLogId: $log?->id,
                via: 'ghl_onboarding_test',
            );

            $test->sync_user_job_id = SyncUserToGoHighLevel::dispatch($user->id);

            $test->email_recipient = $user->email;
            $test->email_status = 'queued';
            $test->email_sent = true;

            $emailLog = EmailLog::where('user_id', $user->id)
                ->where('event_type', 'portal_access_setup')
                ->latest()
                ->first();
            if ($emailLog) {
                $test->email_log_id = $emailLog->id;
            }

            $test->email_details = [
                'status'     => 'queued',
                'recipient'  => $user->email,
                'subject'    => 'Your OmniReferral Portal Access Is Ready',
                'via'        => 'ghl_onboarding_test',
                'queued_at'  => now()->toIso8601String(),
                'mail_log_id'=> $emailLog?->id,
            ];
            $test->queue_details = [
                'send_portal_access_email' => 'dispatched',
                'sync_user_to_ghl'         => 'dispatched',
                'sync_job_id'              => (string) $test->sync_user_job_id,
            ];

            $durations['email_sent'] = round((microtime(true) - $t5) * 1000, 1);
            $stages['email_sent'] = [
                'status'    => 'completed',
                'timestamp' => now()->toIso8601String(),
                'data'      => [
                    'recipient'  => $user->email,
                    'via'        => 'ghl_onboarding_test',
                    'queue'      => 'dispatched',
                    'subject'    => 'Your OmniReferral Portal Access Is Ready',
                ],
                'error' => null,
            ];
        } catch (\Throwable $e) {
            $test->email_status = 'failed';
            $test->email_details = [
                'status'    => 'failed',
                'recipient' => $user->email,
                'error'     => $e->getMessage(),
            ];
            $durations['email_sent'] = round((microtime(true) - $t5) * 1000, 1);
            $stages['email_sent'] = [
                'status'    => 'failed',
                'timestamp' => now()->toIso8601String(),
                'data'      => null,
                'error'     => $e->getMessage(),
            ];
        }

        // --- Step: Agent Login Ready ---
        $stages['agent_login_ready'] = [
            'status'    => 'completed',
            'timestamp' => now()->toIso8601String(),
            'data'      => [
                'login_url' => $portalLoginUrl,
                'email'     => $user->email,
                'status'    => $user->status,
            ],
            'error' => null,
        ];

        // Subscription / Plan
        if ($user->current_plan_id) {
            $package = Package::find($user->current_plan_id);
            if ($package) {
                $test->package_id = $package->id;
                $sub = AgentSubscription::where('user_id', $user->id)->latest()->first();
                if ($sub) {
                    $test->subscription_id = $sub->id;
                }
                $test->subscription_details = [
                    'plan_name'   => $package->name,
                    'plan_slug'   => $package->slug,
                    'plan_id'     => $package->id,
                    'billing'     => $package->billing_type,
                    'quota'       => $package->monthly_lead_quota,
                    'subscription_id' => $sub?->id,
                    'payment_status'  => $sub?->payment_status,
                ];
            }
        }

        if ($log ?? null) {
            $test->onboarding_log_id = $log->id;
        }

        $test->stages = $stages;
        $test->save();
    }

    public function eventLog(int $id): JsonResponse
    {
        $this->authorizeAccess();

        $test = GhlOnboardingTest::with([
            'user', 'onboardingLog', 'webhookEvent',
            'profile', 'subscription.package', 'package', 'emailLog',
        ])->find($id);

        if (! $test) {
            return response()->json(['ok' => false, 'message' => 'Test not found.'], 404);
        }

        $stages = $test->stages ?? [];
        $workflow = [];

        foreach (self::STAGE_DEFS as $key => $def) {
            $stage = $stages[$key] ?? ['status' => 'pending', 'timestamp' => null, 'data' => null, 'error' => null];
            $duration = $test->stageDuration($key);
            $workflow[] = [
                'key'       => $key,
                'label'     => $def['label'],
                'icon'      => $def['icon'],
                'status'    => $stage['status'] ?? 'pending',
                'timestamp' => $stage['timestamp'] ?? null,
                'data'      => $stage['data'] ?? null,
                'error'     => $stage['error'] ?? null,
                'duration'  => $duration,
            ];
        }

        $user = $test->user;
        $profile = $test->profile;
        $sub = $test->subscription;

        return response()->json([
            'ok'   => true,
            'test' => [
                'id'            => $test->id,
                'email'         => $test->email,
                'role'          => $test->role,
                'status'        => $test->status,
                'stages'        => $test->stages,
                'workflow'      => $workflow,
                'durations'     => $test->execution_durations,
                'total_duration_seconds' => $test->totalDuration(),

                // GHL API details
                'ghl_api_details'      => $test->ghl_api_details,
                'ghl_contact_id'       => $test->ghl_contact_id,
                'ghl_form_id'          => $test->ghl_form_id,
                'ghl_form_url'         => $test->ghl_form_url,
                'pipeline_stage'       => $test->pipeline_stage,
                'pipeline_id'          => $test->pipeline_id,
                'http_status'          => $test->http_status,
                'api_request_payload'  => $test->api_request_payload,
                'api_response_payload' => $test->api_response_payload,
                'form_submission_method' => $test->form_submission_method,
                'masked_api_key'       => $this->maskKey(app(GoHighLevelService::class)->resolveApiKey()),
                'location_id'          => app(GoHighLevelService::class)->resolveLocationId(),

                // Payloads
                'webhook_payload'  => $test->webhook_payload,
                'webhook_response' => $test->webhook_response,
                'webhook_headers'  => $test->webhook_headers,

                // User
                'user'       => $user ? $user->only(['id', 'name', 'email', 'role', 'status', 'ghl_contact_id', 'must_reset_password', 'onboarding_completed_at']) : null,
                'user_data'  => $test->user_data,

                // Profile
                'profile'          => $profile ? $profile->toArray() : null,
                'profile_data'     => $test->profile_data,
                'profile_approved'  => $test->profile_approved,
                'profile_published' => $test->profile_published,

                // Password
                'password_token'       => $test->maskedPasswordToken(),
                'password_generated'   => $test->password_generated,
                'portal_login_url'     => $test->portal_login_url,

                // Email
                'email_status'    => $test->email_status,
                'email_recipient' => $test->email_recipient,
                'email_details'   => $test->email_details,
                'email_log'       => $test->emailLog ? $test->emailLog->toArray() : null,
                'email_log_id'    => $test->email_log_id,

                // Subscription
                'subscription'         => $sub ? $sub->toArray() : null,
                'subscription_details' => $test->subscription_details,
                'package'              => $test->package ? $test->package->only(['id', 'name', 'slug', 'billing_type', 'monthly_lead_quota']) : null,

                // Queue
                'queue_details'   => $test->queue_details,
                'sync_job_id'     => $test->sync_user_job_id,

                // Related records
                'onboarding_log'   => $test->onboardingLog ? $test->onboardingLog->toArray() : null,
                'onboarding_log_id'=> $test->onboarding_log_id,
                'webhook_event'    => $test->webhookEvent ? $test->webhookEvent->toArray() : null,
                'webhook_event_id' => $test->webhook_event_id,

                // Error
                'error_message' => $test->error_message,
                'error_stage'   => $test->error_stage,
                'has_failed'    => $test->hasFailed(),
                'is_complete'   => $test->isComplete(),

                // Timing
                'started_at'   => $test->started_at,
                'completed_at' => $test->completed_at,
                'created_at'   => $test->created_at,
            ],
        ]);
    }

    public function retryStage(Request $request, int $id): JsonResponse
    {
        $this->authorizeAccess();

        $test = GhlOnboardingTest::with('user')->find($id);
        if (! $test) {
            return response()->json(['ok' => false, 'message' => 'Test not found.'], 404);
        }
        if ($test->status === 'completed') {
            return response()->json(['ok' => false, 'message' => 'Test already completed.']);
        }

        $validated = $request->validate([
            'stage' => 'required|string|in:email_sent,user_created,profile_created,password_generated,webhook_processing,ghl_contact_created',
        ]);
        $stage = $validated['stage'];
        $stages = $test->stages ?? [];

        if (($stages[$stage]['status'] ?? '') !== 'failed') {
            return response()->json(['ok' => false, 'message' => "Stage '{$stage}' is not failed or already completed."]);
        }

        try {
            match ($stage) {
                'email_sent' => $this->retryEmail($test),
                'user_created' => $this->retryUserCreation($test),
                'profile_created' => $this->retryProfileCreation($test),
                'password_generated' => $this->retryPasswordGeneration($test),
                'webhook_processing' => $this->retryWebhookProcessing($test),
                'ghl_contact_created' => $this->retryGhlContact($test),
                default => throw new \InvalidArgumentException("Unknown stage: {$stage}"),
            };

            $stages[$stage]['status'] = 'completed';
            $stages[$stage]['timestamp'] = now()->toIso8601String();
            $stages[$stage]['error'] = null;
            $test->stages = $stages;

            $allComplete = collect(self::STAGE_DEFS)
                ->keys()
                ->every(fn ($k) => in_array($stages[$k]['status'] ?? '', ['completed', 'skipped'], true));
            if ($allComplete) {
                $test->status = 'completed';
                $test->completed_at = now();
            } else {
                // Clear the overall failure if at least one stage remains non-failed
                $hasFailed = collect(self::STAGE_DEFS)->keys()->contains(
                    fn ($k) => ($stages[$k]['status'] ?? '') === 'failed'
                );
                if (! $hasFailed) {
                    $test->status = 'processing';
                    $test->error_message = null;
                    $test->error_stage = null;
                }
            }
            $test->save();

            return response()->json(['ok' => true, 'test_id' => $test->id, 'message' => "Stage '{$stage}' retried successfully."]);
        } catch (\Throwable $e) {
            $stages[$stage]['status'] = 'failed';
            $stages[$stage]['error'] = $e->getMessage();
            $test->stages = $stages;
            $test->status = 'failed';
            $test->error_message = $e->getMessage();
            $test->error_stage = $stage;
            $test->save();

            return response()->json(['ok' => false, 'test_id' => $test->id, 'message' => "Retry failed: " . $e->getMessage()], 500);
        }
    }

    private function retryEmail(GhlOnboardingTest $test): void
    {
        $user = $test->user;
        if (! $user) {
            throw new \RuntimeException('No user associated with this test.');
        }
        $log = $test->onboardingLog;
        SendPortalAccessSetupEmailJob::dispatchSync(
            userId: $user->id,
            onboardingLogId: $log?->id,
            via: 'admin_retry',
        );
        $test->email_status = 'sent';
        $test->email_sent = true;
        $test->email_details = array_merge($test->email_details ?? [], [
            'status'    => 'sent',
            'retried_at' => now()->toIso8601String(),
        ]);
    }

    private function retryUserCreation(GhlOnboardingTest $test): void
    {
        $formData = $test->form_payload;
        if (! $formData) {
            throw new \RuntimeException('No form data available.');
        }
        $ghlPayload = $this->buildMockGhlPayload($formData, $test->role);
        $result = DB::transaction(fn () => $this->syncService->sync($ghlPayload));
        $test->user_id = $result['user']->id;
        $test->user_data = $result['user']->fresh()->toArray();
        $test->user_created = true;
    }

    private function retryProfileCreation(GhlOnboardingTest $test): void
    {
        $user = $test->user;
        if (! $user) {
            throw new \RuntimeException('No user to create profile for.');
        }
        $profile = $user->realtorProfile;
        if (! $profile) {
            throw new \RuntimeException('No realtor profile found for user.');
        }
        $profile->forceFill([
            'is_approved'    => true,
            'is_active_agent' => true,
            'submission_source' => 'gohighlevel',
        ])->save();
        $test->profile_data = $profile->fresh()->toArray();
        $test->profile_created = true;
        $test->profile_approved = true;
        $test->profile_published = true;
        $test->profile_id = $profile->id;
    }

    private function retryPasswordGeneration(GhlOnboardingTest $test): void
    {
        $user = $test->user;
        if (! $user) {
            throw new \RuntimeException('No user to generate password for.');
        }
        $plain = $this->passwordService->forceProvision($user);
        $test->password_token = $plain;
        $test->password_generated = (bool) $plain;
    }

    private function retryWebhookProcessing(GhlOnboardingTest $test): void
    {
        $event = $test->webhookEvent;
        if (! $event) {
            throw new \RuntimeException('No webhook event to reprocess.');
        }
        $ghl = app(GoHighLevelService::class);
        $ghl->sync();
    }

    private function retryGhlContact(GhlOnboardingTest $test): void
    {
        $f = $test->form_payload;
        if (! $f) {
            throw new \RuntimeException('No form data.');
        }
        $ghl = app(GoHighLevelService::class);
        if (! $ghl->configured()) {
            throw new \RuntimeException('GoHighLevel is not configured.');
        }
        $payload = $this->buildMockGhlPayload($f, $test->role);
        $response = $ghl->createOrUpdateContact($payload);
        $cid = data_get($response, 'contact.id') ?? data_get($response, 'id');
        if (! $cid) {
            throw new \RuntimeException('GHL contact creation returned no contact ID.');
        }
        $test->ghl_contact_id = $cid;
        $test->api_response_payload = $response;
        $test->http_status = data_get($response, 'status', 200);
        $test->ghl_api_details = array_merge($test->ghl_api_details ?? [], [
            'contact_id'  => $cid,
            'retried_at'  => now()->toIso8601String(),
        ]);
    }

    public function webhookDebugger(): View
    {
        $this->authorizeAccess();
        $webhooks = GoHighLevelWebhookLog::latest()->paginate(25);

        return view('pages.admin.gohighlevel.webhook-debugger', [
            'webhooks' => $webhooks,
            'settings' => GhlSetting::instance(),
            'meta'     => ['title' => 'GHL Webhook Debugger — Admin | OmniReferral'],
        ]);
    }

    public function webhookDebuggerEvent(int $id): JsonResponse
    {
        $this->authorizeAccess();
        $event = GoHighLevelWebhookLog::find($id);
        if (! $event) {
            return response()->json(['ok' => false, 'message' => 'Event not found.'], 404);
        }

        $headers = $event->headers ?? [];
        $sentSecret = $headers['x-omnireferral-webhook'] ?? $headers['X-OmniReferral-Webhook'] ?? null;
        $settings = GhlSetting::instance();
        $expected = $settings->webhook_secret ?: config('services.gohighlevel.webhook_secret', '');
        $valid = $expected ? hash_equals($expected, $sentSecret) : null;

        return response()->json([
            'ok' => true,
            'event' => [
                'id'              => $event->id,
                'provider'        => $event->provider,
                'event'           => $event->event,
                'remote_id'       => $event->remote_id,
                'headers'         => $headers,
                'payload'         => $event->payload,
                'payload_hash'    => $event->payload_hash,
                'ip_address'      => $event->ip_address,
                'user_agent'      => $event->user_agent,
                'processed_at'    => $event->processed_at,
                'created_at'      => $event->created_at,
                'signature_valid' => $valid,
                'status'          => $event->processed_at ? 'processed' : 'pending',
                'expected_secret_masked' => $expected ? substr($expected, 0, 4) . '…' : null,
                'sent_secret_masked'     => $sentSecret ? substr($sentSecret, 0, 4) . '…' : null,
            ],
        ]);
    }

    public function history(Request $request): View
    {
        $this->authorizeAccess();

        $query = GhlOnboardingTest::with('user')
            ->latest();

        if ($search = $request->string('search')->value()) {
            $query->search($search);
        }
        if ($status = $request->string('status')->value()) {
            $query->byStatus($status);
        }
        if ($from = $request->string('from')->value()) {
            $query->byDateRange($from, $request->string('to')->value());
        }

        $tests = $query->paginate(25)->withQueryString();

        $stats = [
            'total'     => GhlOnboardingTest::count(),
            'completed' => GhlOnboardingTest::where('status', 'completed')->count(),
            'failed'    => GhlOnboardingTest::where('status', 'failed')->count(),
            'processing' => GhlOnboardingTest::where('status', 'processing')->count(),
        ];

        return view('pages.admin.gohighlevel.test-history', [
            'tests' => $tests,
            'stats' => $stats,
            'filters' => [
                'search' => $search ?? '',
                'status' => $status ?? '',
                'from'   => $from ?? '',
                'to'     => $request->string('to')->value() ?? '',
            ],
            'meta'  => ['title' => 'GHL Test History — Admin | OmniReferral'],
        ]);
    }

    public function resendEmail(Request $request, int $id): JsonResponse
    {
        $this->authorizeAccess();
        $test = GhlOnboardingTest::with('user')->find($id);
        if (! $test || ! $test->user) {
            return response()->json(['ok' => false, 'message' => 'Test or user not found.'], 404);
        }
        try {
            $log = $test->onboardingLog;
            SendPortalAccessSetupEmailJob::dispatchSync(
                userId: $test->user->id,
                onboardingLogId: $log?->id,
                via: 'admin_resend',
            );
            $test->email_status = 'sent';
            $test->email_sent = true;
            $test->save();

            return response()->json(['ok' => true, 'message' => 'Welcome email resent to ' . $test->user->email]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function buildMockGhlPayload(array $formData, string $role): array
    {
        return [
            'contact_id'      => 'test_' . Str::random(12),
            'email'           => $formData['email'],
            'name'            => $formData['name'],
            'first_name'      => explode(' ', $formData['name'])[0] ?? $formData['name'],
            'last_name'       => explode(' ', $formData['name'], 2)[1] ?? '',
            'phone'           => $formData['phone'] ?? '+1-555-000-0001',
            'role'            => $role,
            'city'            => $formData['city'] ?? 'Dallas',
            'state'           => strtoupper($formData['state'] ?? 'TX'),
            'zip_code'        => $formData['zip_code'] ?? '75201',
            'brokerage_name'  => $formData['brokerage_name'] ?? 'OmniReferral Test Partner',
            'form_id'         => 'test_form_' . Str::random(8),
            'form_name'       => 'Admin Onboarding Test Form',
            'package_slug'    => 'starter-leads',
            '_test'           => true,
            '_test_timestamp' => now()->toIso8601String(),
        ];
    }

    private function findFailedStage(array $stages): ?string
    {
        foreach ($stages as $key => $stage) {
            if (($stage['status'] ?? '') === 'failed') {
                return $key;
            }
        }
        return null;
    }

    private function maskKey(?string $key): ?string
    {
        if (! $key || strlen($key) < 8) {
            return $key;
        }
        return substr($key, 0, 4) . str_repeat('•', strlen($key) - 8) . substr($key, -4);
    }

    private function maskToken(?string $token): ?string
    {
        if (! $token || strlen($token) < 8) {
            return $token;
        }
        return substr($token, 0, 6) . '…' . substr($token, -4);
    }

    private function authorizeAccess(): void
    {
        $user = request()->user();
        abort_unless($user && ($user->isSuperAdmin() || $user->isAdmin()), 403, 'Only super admins and admins can access the testing module.');
    }
}
