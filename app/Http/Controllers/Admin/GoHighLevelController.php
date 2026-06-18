<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GhlFieldMapping;
use App\Models\GhlSetting;
use App\Models\GoHighLevelWebhookLog;
use App\Models\OnboardingLog;
use App\Models\User;
use App\Services\GoHighLevelService;
use App\Services\WebhookInboxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class GoHighLevelController extends Controller
{
    // ─── Overview ─────────────────────────────────────────────────────────────

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
        ];

        return view('pages.admin.gohighlevel.index', [
            'settings'         => $settings,
            'ghl'              => $ghl,
            'recentWebhooks'   => $recentWebhooks,
            'recentOnboarding' => $recentOnboarding,
            'stats'            => $stats,
            'meta'             => ['title' => 'GoHighLevel — Admin | OmniReferral'],
        ]);
    }

    // ─── Settings ─────────────────────────────────────────────────────────────

    public function settings(): View
    {
        $settings = GhlSetting::instance();

        return view('pages.admin.gohighlevel.settings', [
            'settings' => $settings,
            'meta'     => ['title' => 'GHL Settings — Admin | OmniReferral'],
        ]);
    }

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

        $onboardingLogs = OnboardingLog::with('user:id,name,email,role')
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
}
