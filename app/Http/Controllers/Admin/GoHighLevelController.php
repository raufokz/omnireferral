<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendPortalAccessSetupEmailJob;
use App\Models\GhlFieldMapping;
use App\Models\GhlSetting;
use App\Models\GoHighLevelWebhookLog;
use App\Models\Lead;
use App\Models\OnboardingLog;
use App\Models\User;
use App\Services\GoHighLevelService;
use App\Services\WebhookInboxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'GhlSettings',
    type: 'object',
    properties: [
        new OA\Property(property: 'api_key', type: 'string', description: 'GHL API key (encrypted)'),
        new OA\Property(property: 'location_id', type: 'string'),
        new OA\Property(property: 'agency_id', type: 'string'),
        new OA\Property(property: 'webhook_secret', type: 'string', description: 'Webhook secret (encrypted)'),
        new OA\Property(property: 'environment', type: 'string', enum: ['production', 'sandbox']),
        new OA\Property(property: 'pre_payment_survey_url', type: 'string', format: 'url'),
        new OA\Property(property: 'post_payment_onboarding_url', type: 'string', format: 'url'),
        new OA\Property(property: 'buyer_onboarding_form_url', type: 'string', format: 'url'),
        new OA\Property(property: 'agent_onboarding_form_url', type: 'string', format: 'url'),
        new OA\Property(property: 'realtor_onboarding_form_url', type: 'string', format: 'url'),
        new OA\Property(property: 'hidden_fields', type: 'array', items: new OA\Items(type: 'string')),
    ]
)]
class GoHighLevelController extends Controller
{
    // ─── Overview ─────────────────────────────────────────────────────────────

    #[OA\Get(
        path: '/admin/gohighlevel',
        tags: ['Admin', 'GoHighLevel'],
        summary: 'GHL integration dashboard',
        description: 'Shows connection status, KPI stats, recent webhooks/onboarding activity, and configured form URLs.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'GHL overview page'),
            new OA\Response(response: 403, description: 'Forbidden - admin access required'),
        ]
    )]
    public function index(): View
    {
        $settings = GhlSetting::instance();
        $ghl = app(GoHighLevelService::class);

        $recentWebhooks = GoHighLevelWebhookLog::latest()->take(5)->get();
        $recentOnboarding = OnboardingLog::with('user:id,name,email,role')
            ->where('source', 'ghl')
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            'webhooks_total'     => GoHighLevelWebhookLog::count(),
            'webhooks_processed' => GoHighLevelWebhookLog::whereNotNull('processed_at')->count(),
            'webhooks_pending'   => GoHighLevelWebhookLog::whereNull('processed_at')->count(),
            'onboarding_total'   => OnboardingLog::where('source', 'ghl')->count(),
            'users_ghl_synced'   => User::whereNotNull('ghl_contact_id')->count(),
            'leads_ghl_synced'   => Lead::whereNotNull('ghl_contact_id')->count(),
            'last_webhook_at'    => GoHighLevelWebhookLog::latest()->value('created_at'),
            'configured'         => $ghl->configured(),
        ];

        return view('pages.admin.gohighlevel.index', [
            'settings'         => $settings,
            'ghl'              => $ghl,
            'recentWebhooks'   => $recentWebhooks,
            'recentOnboarding' => $recentOnboarding,
            'stats'            => $stats,
            'testConnectionUrl'=> route('admin.ghl.test.connection'),
            'meta'             => ['title' => 'GoHighLevel — Admin | OmniReferral'],
        ]);
    }

    // ─── Settings ─────────────────────────────────────────────────────────────

    #[OA\Get(
        path: '/admin/gohighlevel/settings',
        tags: ['Admin', 'GoHighLevel'],
        summary: 'View GHL integration settings',
        description: 'Shows API credentials, form URLs, and hidden field configuration.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'GHL settings page'),
            new OA\Response(response: 403, description: 'Forbidden - admin access required'),
        ]
    )]
    public function settings(): View
    {
        $settings = GhlSetting::instance();

        return view('pages.admin.gohighlevel.settings', [
            'settings' => $settings,
            'meta'     => ['title' => 'GHL Settings — Admin | OmniReferral'],
        ]);
    }

    #[OA\Put(
        path: '/admin/gohighlevel/settings',
        tags: ['Admin', 'GoHighLevel'],
        summary: 'Update GHL integration settings',
        description: 'Updates API credentials, form URLs, and hidden field mapping. Super admin only.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/GhlSettings')
        ),
        responses: [
            new OA\Response(response: 302, description: 'Redirect with success message'),
            new OA\Response(response: 403, description: 'Forbidden - super admin only'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function updateSettings(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'Only super admins can edit GoHighLevel credentials.');

        $validated = $request->validate([
            'api_key'                       => 'nullable|string|max:500',
            'agency_id'                     => 'nullable|string|max:100',
            'location_id'                   => 'nullable|string|max:100',
            'webhook_secret'                => 'nullable|string|max:500',
            'environment'                   => 'required|in:production,sandbox',
            'pre_payment_survey_url'        => 'nullable|url|max:500',
            'post_payment_onboarding_url'   => 'nullable|url|max:500',
            'buyer_onboarding_form_url'     => 'nullable|url|max:500',
            'agent_onboarding_form_url'     => 'nullable|url|max:500',
            'realtor_onboarding_form_url'   => 'nullable|url|max:500',
            'redirect_url_after_submission' => 'nullable|url|max:500',
            'hidden_fields'                 => 'nullable|array',
            'hidden_fields.*'               => 'string|max:50',
            'notes'                         => 'nullable|string|max:1000',
        ]);

        $settings = GhlSetting::instance();

        // Preserve existing encrypted values if the field was left blank.
        if (blank($validated['api_key'] ?? null)) {
            unset($validated['api_key']);
        }
        if (blank($validated['webhook_secret'] ?? null)) {
            unset($validated['webhook_secret']);
        }

        $settings->fill($validated);
        $settings->last_tested_by_user_id = null;
        $settings->connection_status = 'unknown';
        $settings->save();

        Log::info('GHL settings updated.', ['by_user_id' => $request->user()->id]);

        return redirect()
            ->route('admin.ghl.settings')
            ->with('success', 'GoHighLevel settings saved. Click "Test Connection" to verify.');
    }

    // ─── Field Mappings ────────────────────────────────────────────────────────

    public function mappings(): View
    {
        $mappings = GhlFieldMapping::ordered()->get();

        return view('pages.admin.gohighlevel.mappings', [
            'mappings'        => $mappings,
            'supportedTables' => GhlFieldMapping::supportedTables(),
            'meta'            => ['title' => 'GHL Field Mappings — Admin | OmniReferral'],
        ]);
    }

    public function storeMappings(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $validated = $request->validate([
            'mappings'              => 'required|array',
            'mappings.*.ghl_field'  => 'required|string|max:100',
            'mappings.*.db_table'   => 'required|in:users,realtor_profiles,buyer_profiles',
            'mappings.*.db_column'  => 'required|string|max:100',
            'mappings.*.label'      => 'nullable|string|max:150',
            'mappings.*.is_active'  => 'boolean',
            'mappings.*.sort_order' => 'integer|min:0|max:9999',
        ]);

        foreach ($validated['mappings'] as $i => $row) {
            GhlFieldMapping::updateOrCreate(
                [
                    'ghl_field' => $row['ghl_field'],
                    'db_table'  => $row['db_table'],
                    'db_column' => $row['db_column'],
                ],
                [
                    'label'      => $row['label'] ?? null,
                    'is_active'  => (bool) ($row['is_active'] ?? true),
                    'sort_order' => (int) ($row['sort_order'] ?? $i),
                ]
            );
        }

        return redirect()->route('admin.ghl.mappings')->with('success', 'Field mappings saved.');
    }

    public function addMapping(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $validated = $request->validate([
            'ghl_field'  => 'required|string|max:100',
            'db_table'   => 'required|in:users,realtor_profiles,buyer_profiles',
            'db_column'  => 'required|string|max:100',
            'label'      => 'nullable|string|max:150',
            'sort_order' => 'integer|min:0|max:9999',
        ]);

        GhlFieldMapping::updateOrCreate(
            [
                'ghl_field' => $validated['ghl_field'],
                'db_table'  => $validated['db_table'],
                'db_column' => $validated['db_column'],
            ],
            [
                'label'      => $validated['label'] ?? null,
                'is_active'  => true,
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
            ]
        );

        return redirect()->route('admin.ghl.mappings')->with('success', 'Mapping added.');
    }

    public function deleteMapping(Request $request, GhlFieldMapping $mapping): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $mapping->delete();

        return redirect()->route('admin.ghl.mappings')->with('success', 'Mapping removed.');
    }

    public function toggleMapping(Request $request, GhlFieldMapping $mapping): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $mapping->update(['is_active' => ! $mapping->is_active]);

        return redirect()->route('admin.ghl.mappings')->with('success', 'Mapping updated.');
    }

    // ─── Logs ─────────────────────────────────────────────────────────────────

    #[OA\Get(
        path: '/admin/gohighlevel/logs',
        tags: ['Admin', 'GoHighLevel'],
        summary: 'View GHL webhook and onboarding logs',
        description: 'Filterable logs showing all GoHighLevel webhook deliveries and onboarding submissions. Includes retry and resend actions.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'event_type', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['processed', 'pending'])),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Logs page'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function logs(Request $request): View
    {
        $eventType = $request->query('event_type');
        $status    = $request->query('status');
        $search    = $request->query('search');

        $webhooks = GoHighLevelWebhookLog::query()
            ->when($eventType, fn ($q) => $q->where('event', $eventType))
            ->when($status === 'processed', fn ($q) => $q->whereNotNull('processed_at'))
            ->when($status === 'pending', fn ($q) => $q->whereNull('processed_at'))
            ->when($search, fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('remote_id', 'like', "%{$search}%")
                      ->orWhereRaw("JSON_SEARCH(payload, 'one', ?) IS NOT NULL", ["%{$search}%"]);
            }))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $onboardingLogs = OnboardingLog::with('user:id,name,email,role,status,onboarding_completed_at,must_reset_password')
            ->where('source', 'ghl')
            ->when($search, fn ($q) => $q->where('triggered_by', 'like', "%{$search}%"))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $eventTypes = GoHighLevelWebhookLog::distinct()->pluck('event')->sort()->values();

        return view('pages.admin.gohighlevel.logs', [
            'webhooks'       => $webhooks,
            'onboardingLogs' => $onboardingLogs,
            'eventTypes'     => $eventTypes,
            'filters'        => compact('eventType', 'status', 'search'),
            'meta'           => ['title' => 'GHL Logs — Admin | OmniReferral'],
        ]);
    }

    // ─── Testing ──────────────────────────────────────────────────────────────

    #[OA\Get(
        path: '/admin/gohighlevel/testing',
        tags: ['Admin', 'GoHighLevel'],
        summary: 'GHL testing dashboard',
        description: 'Shows a dashboard for testing GHL webhooks, connection, and sync functionality.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Testing page'),
            new OA\Response(response: 403, description: 'Forbidden - admin access required'),
        ]
    )]
    public function testing(): View
    {
        $settings       = GhlSetting::instance();
        $webhookUrl     = route('webhooks.gohighlevel.onboarding');
        $purchaseWebhookUrl = route('webhooks.gohighlevel.purchase');

        return view('pages.admin.gohighlevel.testing', [
            'settings'           => $settings,
            'webhookUrl'         => $webhookUrl,
            'purchaseWebhookUrl' => $purchaseWebhookUrl,
            'meta'               => ['title' => 'GHL Testing — Admin | OmniReferral'],
        ]);
    }

    #[OA\Post(
        path: '/admin/gohighlevel/test/connection',
        tags: ['Admin', 'GoHighLevel'],
        summary: 'Test GHL API connection',
        description: 'Tests the configured GoHighLevel API key and location ID by pinging the GHL API. Updates last tested timestamp.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(type: 'object')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Connection test result'),
            new OA\Response(response: 403, description: 'Forbidden - admin access required'),
        ]
    )]
    public function testConnection(Request $request): JsonResponse
    {
        $ghl    = app(GoHighLevelService::class);
        $result = $ghl->testConnection();

        $settings = GhlSetting::instance();
        $settings->last_tested_at = now();
        $settings->last_tested_by_user_id = $request->user()?->id;
        $settings->save();

        return response()->json($result);
    }

    #[OA\Post(
        path: '/admin/gohighlevel/test/webhook',
        tags: ['Admin', 'GoHighLevel'],
        summary: 'Test GHL webhook delivery',
        description: 'Sends a simulated webhook payload to the local onboarding or purchase webhook endpoint for testing.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['event_type', 'email'],
                properties: [
                    new OA\Property(property: 'event_type', type: 'string', enum: ['onboarding_completed', 'package_purchased']),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'role', type: 'string', enum: ['buyer', 'seller', 'agent']),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Webhook test result'),
            new OA\Response(response: 403, description: 'Forbidden - admin access required'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function testWebhook(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_type' => 'required|in:onboarding_completed,package_purchased',
            'email'      => 'required|email',
            'name'       => 'nullable|string|max:100',
            'role'       => 'nullable|in:buyer,seller,agent',
        ]);

        $routeName = $validated['event_type'] === 'package_purchased'
            ? 'webhooks.gohighlevel.purchase'
            : 'webhooks.gohighlevel.onboarding';

        $targetUrl = route($routeName);
        $secret    = GhlSetting::instance()->webhook_secret
                  ?? config('services.gohighlevel.webhook_secret', '');

        $payload = [
            'email'      => $validated['email'],
            'name'       => $validated['name'] ?? 'Test User',
            'role'       => $validated['role'] ?? 'agent',
            'phone'      => '+1-555-000-0001',
            'city'       => 'Dallas',
            'state'      => 'TX',
            'zip_code'   => '75201',
            '_test'      => true,
        ];

        try {
            $headers = ['Accept' => 'application/json'];
            if (filled($secret)) {
                $headers['X-OmniReferral-Webhook'] = $secret;
            }

            $response = Http::withHeaders($headers)
                ->timeout(15)
                ->post($targetUrl, $payload);

            return response()->json([
                'ok'       => $response->successful(),
                'status'   => $response->status(),
                'response' => $response->json() ?? $response->body(),
                'url'      => $targetUrl,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok'      => false,
                'message' => $e->getMessage(),
                'url'     => $targetUrl,
            ]);
        }
    }

    #[OA\Post(
        path: '/admin/gohighlevel/test/sync',
        tags: ['Admin', 'GoHighLevel'],
        summary: 'Test user sync to GHL',
        description: 'Attempts to sync a specific user to GoHighLevel as a contact and returns the result.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_id'],
                properties: [
                    new OA\Property(property: 'user_id', type: 'integer', description: 'User ID to sync'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Sync result'),
            new OA\Response(response: 403, description: 'Forbidden - admin access required'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function testSync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $ghl  = app(GoHighLevelService::class);

        if (! $ghl->configured()) {
            return response()->json(['ok' => false, 'message' => 'GoHighLevel is not configured.']);
        }

        try {
            $result = $ghl->syncUser($user);

            return response()->json([
                'ok'      => $result !== null,
                'message' => $result !== null ? 'Contact synced to GoHighLevel.' : 'Sync returned no response — check API key and location ID.',
                'result'  => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    #[OA\Post(
        path: '/admin/gohighlevel/logs/{webhookEventId}/retry',
        tags: ['Admin', 'GoHighLevel'],
        summary: 'Retry a webhook event',
        description: 'Re-fires an unprocessed webhook event payload against the local webhook endpoint for reprocessing.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'webhookEventId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                description: 'Webhook log event ID'
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Retry result'),
            new OA\Response(response: 403, description: 'Forbidden - admin access required'),
            new OA\Response(response: 404, description: 'Event not found'),
        ]
    )]
    public function retrySync(Request $request, int $webhookEventId): JsonResponse
    {
        $event = GoHighLevelWebhookLog::findOrFail($webhookEventId);

        if ($event->processed_at) {
            return response()->json(['ok' => false, 'message' => 'This event is already processed.']);
        }

        // Re-fire the event payload against our own webhook endpoint.
        $routeName = match ($event->event) {
            'package_purchased'    => 'webhooks.gohighlevel.purchase',
            'onboarding_completed' => 'webhooks.gohighlevel.onboarding',
            default                => null,
        };

        if (! $routeName) {
            return response()->json(['ok' => false, 'message' => 'No retry handler for event: '.$event->event]);
        }

        $secret = GhlSetting::instance()->webhook_secret
               ?? config('services.gohighlevel.webhook_secret', '');

        $headers = ['Accept' => 'application/json'];
        if (filled($secret)) {
            $headers['X-OmniReferral-Webhook'] = $secret;
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(15)
                ->post(route($routeName), $event->payload ?? []);

            return response()->json([
                'ok'       => $response->successful(),
                'status'   => $response->status(),
                'response' => $response->json(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    #[OA\Post(
        path: '/admin/gohighlevel/logs/{onboardingLogId}/resend-email',
        tags: ['Admin', 'GoHighLevel'],
        summary: 'Resend portal access email',
        description: 'Re-sends the portal access setup email to the user associated with a given onboarding log entry.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'onboardingLogId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                description: 'Onboarding log ID'
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Email resend result'),
            new OA\Response(response: 403, description: 'Forbidden - admin access required'),
            new OA\Response(response: 404, description: 'Onboarding log not found'),
        ]
    )]
    public function resendPortalAccessEmail(Request $request, int $onboardingLogId): JsonResponse
    {
        $log = OnboardingLog::with('user')->findOrFail($onboardingLogId);

        if (! $log->user) {
            return response()->json(['ok' => false, 'message' => 'User not found for this onboarding log.']);
        }

        $user = $log->user;

        // Eligibility (spec wording). Resend only when the user can actually use the portal.
        $reasons = [];

        if (blank($user->email)) {
            $reasons[] = 'Missing email';
        }

        if (! $user->onboarding_completed_at) {
            $reasons[] = 'Onboarding not completed';
        }

        if (! in_array($user->status, ['active', 'approved'], true)) {
            $reasons[] = 'User still pending';
        }

        if ($reasons) {
            return response()->json([
                'ok' => false,
                'message' => 'User not eligible for portal access email.',
                'reasons' => $reasons,
            ]);
        }

        try {
            // Run synchronously so a mail/SMTP failure surfaces immediately to the admin.
            SendPortalAccessSetupEmailJob::dispatchSync(
                userId: $user->id,
                onboardingLogId: $log->id,
                via: 'admin_resend',
            );

            $log->refresh();

            if ($log->email_status === 'failed') {
                return response()->json([
                    'ok' => false,
                    'message' => 'Mail config failed: '.($log->error_message ?: 'unknown mailer error'),
                ]);
            }

            Log::info('Portal access setup email resent via admin', [
                'user_id'           => $user->id,
                'email'             => $user->email,
                'onboarding_log_id' => $onboardingLogId,
            ]);

            return response()->json([
                'ok' => true,
                'message' => 'Portal access email sent successfully to '.$user->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to resend portal access email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Mail config failed: '.$e->getMessage(),
            ]);
        }
    }

    // ─── Debugging Center ───────────────────────────────────────────────────────

    /**
     * Run a battery of read-only health checks across connection, webhooks, and
     * database layers and surface each as problem / cause / fix / severity / status.
     * Nothing here mutates data — it is purely diagnostic.
     */
    public function debug(): View
    {
        $settings = GhlSetting::instance();
        $ghl      = app(GoHighLevelService::class);

        $checks = [];

        // ── Connection ───────────────────────────────────────────────────────
        $hasKey = filled($ghl->resolveApiKey());
        $checks[] = $this->check(
            'Connection', 'Private integration key configured',
            $hasKey ? 'working' : 'broken',
            $hasKey ? 'low' : 'high',
            $hasKey ? 'API key is present (DB or env).' : 'No API key found in ghl_settings or GOHIGHLEVEL_API_KEY env.',
            $hasKey ? null : 'Add the key under GoHighLevel → Settings, or set GOHIGHLEVEL_API_KEY in .env.',
            'app/Services/GoHighLevelService.php · resolveApiKey()'
        );

        $hasLocation = filled($ghl->resolveLocationId());
        $checks[] = $this->check(
            'Connection', 'Location / Business ID configured',
            $hasLocation ? 'working' : 'broken',
            $hasLocation ? 'low' : 'high',
            $hasLocation ? 'Location ID is present.' : 'No Location ID in ghl_settings or GOHIGHLEVEL_LOCATION_ID env.',
            $hasLocation ? null : 'Add the Location ID under GoHighLevel → Settings.',
            'app/Services/GoHighLevelService.php · resolveLocationId()'
        );

        $statusMap = [
            'connected' => ['working', 'low', 'Last test succeeded.'],
            'invalid'   => ['broken', 'high', 'Last test returned 401 — the key is invalid or lacks scope.'],
            'error'     => ['warning', 'medium', 'Last test failed with a network/API error.'],
            'unknown'   => ['warning', 'medium', 'Connection has not been tested since the last settings change.'],
        ];
        [$cStatus, $cSev, $cCause] = $statusMap[$settings->connection_status] ?? $statusMap['unknown'];
        $checks[] = $this->check(
            'Connection', 'Last verified API connection',
            $cStatus, $cSev, $cCause,
            $settings->connection_status === 'connected' ? null : 'Open the overview and click "Test Connection" to re-verify credentials and scope.',
            'GoHighLevelController@testConnection',
            $settings->last_tested_at?->diffForHumans()
        );

        // ── Webhooks ─────────────────────────────────────────────────────────
        $routesOk = Route::has('webhooks.gohighlevel.onboarding')
            && Route::has('webhooks.gohighlevel.purchase')
            && Route::has('webhooks.gohighlevel.lead-status');
        $checks[] = $this->check(
            'Webhooks', 'Inbound webhook routes registered',
            $routesOk ? 'working' : 'broken',
            $routesOk ? 'low' : 'high',
            $routesOk ? 'onboarding, purchase, and lead-status endpoints are routed.' : 'One or more GHL webhook routes are missing.',
            $routesOk ? null : 'Verify the webhooks.gohighlevel.* routes in routes/web.php.',
            'routes/web.php'
        );

        $secret = filled($settings->webhook_secret) || filled(config('services.gohighlevel.webhook_secret'));
        $isProd = app()->environment('production');
        $checks[] = $this->check(
            'Webhooks', 'Webhook secret / signature validation',
            $secret ? 'working' : ($isProd ? 'broken' : 'warning'),
            $secret ? 'low' : ($isProd ? 'high' : 'medium'),
            $secret
                ? 'A shared secret is configured; inbound webhooks must send the X-OmniReferral-Webhook header.'
                : 'No webhook secret configured. In local/testing the endpoint accepts unsigned calls; in production it would reject everything.',
            $secret ? null : 'Set a webhook secret under Settings and configure the same value in GoHighLevel.',
            'GoHighLevelWebhookController@isAuthorized'
        );

        $recentCount = GoHighLevelWebhookLog::where('created_at', '>=', now()->subDays(7))->count();
        $lastWebhookAt = GoHighLevelWebhookLog::latest()->value('created_at');
        $checks[] = $this->check(
            'Webhooks', 'Events received in the last 7 days',
            $recentCount > 0 ? 'working' : 'warning',
            $recentCount > 0 ? 'low' : 'medium',
            $recentCount > 0 ? "{$recentCount} event(s) received recently." : 'No GoHighLevel webhook events received in the last 7 days.',
            $recentCount > 0 ? null : 'Confirm the webhook URL in GoHighLevel matches this site and that the workflow/automation is active.',
            'webhook_events table (provider=gohighlevel)',
            $lastWebhookAt?->diffForHumans()
        );

        $pending = GoHighLevelWebhookLog::whereNull('processed_at')->count();
        $checks[] = $this->check(
            'Webhooks', 'Unprocessed webhook backlog',
            $pending === 0 ? 'working' : 'warning',
            $pending === 0 ? 'low' : 'medium',
            $pending === 0 ? 'All received events have been processed.' : "{$pending} event(s) recorded but not marked processed.",
            $pending === 0 ? null : 'Open Logs, inspect the payload, and use Retry. Persistent failures are logged to storage/logs/laravel.log.',
            'GoHighLevelController@retrySync'
        );

        // ── Database ─────────────────────────────────────────────────────────
        foreach (['webhook_events', 'ghl_settings', 'ghl_field_mappings', 'onboarding_logs'] as $table) {
            $exists = Schema::hasTable($table);
            $checks[] = $this->check(
                'Database', "Table `{$table}` exists",
                $exists ? 'working' : 'broken',
                $exists ? 'low' : 'high',
                $exists ? 'Table is present.' : 'Required table is missing.',
                $exists ? null : 'Run `php artisan migrate` — a GHL migration has not been applied.',
                'database/migrations'
            );
        }

        $ghlColumn = Schema::hasColumn('users', 'ghl_contact_id');
        $checks[] = $this->check(
            'Database', 'users.ghl_contact_id column present',
            $ghlColumn ? 'working' : 'broken',
            $ghlColumn ? 'low' : 'high',
            $ghlColumn ? 'Contacts can be linked back to GoHighLevel.' : 'The ghl_contact_id column is missing from users.',
            $ghlColumn ? null : 'Run pending migrations.',
            'users table'
        );

        $activeMappings = GhlFieldMapping::active()->count();
        $checks[] = $this->check(
            'Database', 'Active field mappings configured',
            $activeMappings > 0 ? 'working' : 'warning',
            $activeMappings > 0 ? 'low' : 'medium',
            $activeMappings > 0 ? "{$activeMappings} active mapping(s)." : 'No active field mappings — onboarding webhooks fall back to built-in defaults in OnboardingSyncService.',
            $activeMappings > 0 ? null : 'Add mappings under Field Mappings to control how GHL fields land in your tables.',
            'app/Models/GhlFieldMapping.php'
        );

        // ── Summary ──────────────────────────────────────────────────────────
        $summary = [
            'broken'  => collect($checks)->where('status', 'broken')->count(),
            'warning' => collect($checks)->where('status', 'warning')->count(),
            'working' => collect($checks)->where('status', 'working')->count(),
        ];

        $endpointDocs = [
            'onboarding' => route('webhooks.gohighlevel.onboarding'),
            'purchase'   => route('webhooks.gohighlevel.purchase'),
            'leadStatus' => route('webhooks.gohighlevel.lead-status'),
            'events'     => route('webhooks.gohighlevel.events'),
        ];

        return view('pages.admin.gohighlevel.debug', [
            'checks'        => $checks,
            'summary'       => $summary,
            'settings'      => $settings,
            'endpointDocs'  => $endpointDocs,
            'secretEnabled' => $secret,
            'meta'          => ['title' => 'GHL Debugging Center — Admin | OmniReferral'],
        ]);
    }

    /**
     * Build a single diagnostic row.
     */
    private function check(
        string $area,
        string $label,
        string $status,
        string $severity,
        string $cause,
        ?string $fix = null,
        ?string $file = null,
        ?string $meta = null,
    ): array {
        return [
            'area'     => $area,
            'label'    => $label,
            'status'   => $status,   // working | warning | broken
            'severity' => $severity, // low | medium | high
            'cause'    => $cause,
            'fix'      => $fix,
            'file'     => $file,
            'meta'     => $meta,
        ];
    }
}
