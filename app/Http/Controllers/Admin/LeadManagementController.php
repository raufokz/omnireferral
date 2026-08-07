<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreManualLeadRequest;
use App\Http\Requests\SyncGoogleSheetRequest;
use App\Models\Lead;
use App\Models\LeadImportRun;
use App\Models\Property;
use App\Models\RealtorProfile;
use App\Models\User;
use App\Services\LeadFilterService;
use App\Services\LeadMultiFormatImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = app(LeadFilterService::class)->normalizeFromRequest($request);
        $baseQuery = Lead::query()->with('assignedAgent:id,name');
        app(LeadFilterService::class)->apply($baseQuery, $filters);

        $leads = (clone $baseQuery)
            ->orderByRaw('COALESCE(source_timestamp, created_at) DESC')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $summaryQuery = clone $baseQuery;
        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'qualified' => (clone $summaryQuery)->where('status', 'qualified')->count(),
            'rejected' => (clone $summaryQuery)->where('status', 'not_interested')->count(),
            'website' => (clone $summaryQuery)->where('source', 'website')->count(),
        ];

        $workspaceUser = auth()->user();
        $isStaffView = $workspaceUser?->role === 'staff';
        $agents = User::where('role', 'agent')->orderBy('name')->get(['id', 'name']);

        $realtorStats = null;
        if ($filters['agent_id']) {
            $realtorStats = $this->buildRealtorStats((int) $filters['agent_id']);
            $realtorStats['agent_name'] = $agents->firstWhere('id', $filters['agent_id'])?->name ?? 'Selected Realtor';
        }

        return view('pages.admin.leads.index', [
            'leads' => $leads,
            'filters' => $filters,
            'agents' => $agents,
            'realtorStats' => $realtorStats,
            'repNames' => Lead::query()
                ->whereNotNull('rep_name')
                ->where('rep_name', '!=', '')
                ->distinct()
                ->orderBy('rep_name')
                ->pluck('rep_name'),
            'sources' => Lead::query()
                ->whereNotNull('source')
                ->where('source', '!=', '')
                ->distinct()
                ->orderBy('source')
                ->pluck('source'),
            'intents' => ['buyer', 'seller', 'investor', 'other'],
            'statuses' => Lead::statusList(),
            'summary' => $summary,
            'workspaceUser' => $workspaceUser,
            'isStaffView' => $isStaffView,
            'stats' => [
                'leads' => Lead::count(),
                'realtors' => RealtorProfile::count(),
                'properties' => Property::count(),
                'pendingListings' => Property::pendingReview()->count(),
                'pending' => RealtorProfile::whereHas('user', function ($query) {
                    $query->where('status', 'pending');
                })->count(),
            ],
            'meta' => [
                'title' => 'Lead Management | OmniReferral',
                'description' => 'Filter, assign, import, sync, and export leads for admin and staff teams.',
            ],
        ]);
    }

    /**
     * Per-realtor lead counts + percentages across every status, for the Lead
     * Registry's "Assigned Realtor" filter panel.
     *
     * @return array{total: int, by_status: array<string, array{count: int, percent: float}>}
     */
    private function buildRealtorStats(int $agentId): array
    {
        $counts = Lead::query()
            ->where('assigned_agent_id', $agentId)
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $total = (int) $counts->sum();

        $byStatus = collect(Lead::statusList())->mapWithKeys(function (string $status) use ($counts, $total) {
            $count = (int) ($counts[$status] ?? 0);

            return [$status => [
                'count' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100, 1) : 0.0,
            ]];
        })->all();

        return [
            'total' => $total,
            'by_status' => $byStatus,
        ];
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        if ($request->boolean('async')) {
            $export = \App\Models\DataExport::create([
                'requested_by_user_id' => $request->user()?->id,
                'type' => 'leads',
                'format' => 'csv',
                'filters' => app(LeadFilterService::class)->normalizeFromRequest($request),
                'status' => 'pending',
            ]);
            \App\Jobs\GenerateDataExport::dispatch($export->id);

            return redirect()
                ->route('admin.exports.index')
                ->with('success', 'Lead export queued. You can download it once processing completes.');
        }

        $filename = 'leads-export-' . now()->format('Ymd-His') . '.csv';
        $query = Lead::query()->with('assignedAgent:id,name');
        app(LeadFilterService::class)->apply($query, app(LeadFilterService::class)->normalizeFromRequest($request));

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'timestamp',
                'lead_number',
                'lead_name',
                'intent',
                'property_address_or_desired_area',
                'beds_baths',
                'budget',
                'asking_price',
                'working_with_realtor',
                'timeline',
                'dnc_disclaimer',
                'notes',
                'phone_number',
                'email',
                'rep_name',
                'state',
                'sent_to',
                'status',
                'assignment',
                'reason_in_house',
                'realtor_response',
                'assigned_agent',
                'source',
                'created_at',
            ]);

            $query->chunkById(500, function ($rows) use ($handle) {
                foreach ($rows as $lead) {
                    fputcsv($handle, [
                        optional($lead->source_timestamp)->toDateTimeString(),
                        $lead->lead_number,
                        $lead->name,
                        $lead->intent,
                        $lead->property_address,
                        $lead->beds_baths,
                        $lead->budget,
                        $lead->asking_price,
                        $lead->working_with_realtor ? 'Yes' : ($lead->working_with_realtor === false ? 'No' : ''),
                        $lead->timeline,
                        $lead->dnc_disclaimer,
                        $lead->notes,
                        $lead->phone,
                        $lead->email,
                        $lead->rep_name,
                        $lead->state,
                        $lead->sent_to,
                        $lead->status,
                        $lead->assignment,
                        $lead->reason_in_house,
                        $lead->realtor_response,
                        $lead->assignedAgent?->name,
                        $lead->source,
                        optional($lead->created_at)->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function importCsv(Request $request, LeadMultiFormatImportService $importService): RedirectResponse
    {
        $request->validate([
            'lead_file' => ['nullable', 'file', 'mimes:csv,txt,xlsx,xls,pdf,doc,docx,json', 'max:10240'],
            'csv_file' => ['nullable', 'file', 'mimes:csv,txt,xlsx,xls,pdf,doc,docx,json', 'max:10240'],
            'mode' => ['nullable', 'in:import,preview'],
        ]);

        $file = $request->file('lead_file') ?: $request->file('csv_file');
        if (! $file) {
            return back()->with('error', 'Please choose a lead file before importing.');
        }

        $rows = $importService->previewFile($file);
        if ($rows === []) {
            return back()->with('error', 'We could not detect any importable lead rows in that file. Please verify the format and try again.');
        }

        $mode = (string) $request->input('mode', 'import');
        if ($mode !== 'preview') {
            $startedAt = now();
            $result = $importService->importPreparedRows($rows);

            LeadImportRun::create([
                'source' => 'file_import',
                'triggered_by_user_id' => $request->user()?->id,
                'status' => 'completed',
                'created_count' => $result['created'],
                'skipped_count' => $result['skipped'],
                'failed_count' => $result['failed'] ?? 0,
                'warnings' => ! empty($result['failed_rows']) ? $result['failed_rows'] : null,
                'file_name' => $file->getClientOriginalName(),
                'started_at' => $startedAt,
                'finished_at' => now(),
            ]);

            $message = "Import complete. Added {$result['created']} new leads, skipped {$result['skipped']} duplicate/invalid rows.";
            if (($result['failed'] ?? 0) > 0) {
                $message .= " {$result['failed']} rows failed. Check the logs for details.";
            }

            return redirect()
                ->route('admin.leads.index')
                ->with('success', $message);
        }

        $previewKey = 'lead_import_preview_' . Str::uuid();
        Cache::put($previewKey, [
            'rows' => $rows,
            'file_name' => $file->getClientOriginalName(),
        ], now()->addMinutes(30));

        return redirect()->route('admin.leads.import.preview', ['key' => $previewKey]);
    }

    public function previewImport(Request $request): View|RedirectResponse
    {
        $key = (string) $request->string('key')->value();
        $cached = Cache::get($key);

        // Support both old format (array of rows) and new format (array with 'rows' key)
        if (is_array($cached) && isset($cached['rows'])) {
            $rows = $cached['rows'];
            $fileName = $cached['file_name'] ?? null;
        } elseif (is_array($cached)) {
            $rows = $cached;
            $fileName = null;
        } else {
            return redirect()->route('admin.leads.index')->with('error', 'Import preview expired. Please upload again.');
        }

        $newCount = collect($rows)->where('_duplicate', false)->count();
        $duplicateCount = collect($rows)->where('_duplicate', true)->count();

        return view('pages.admin.leads.preview', [
            'key' => $key,
            'rows' => array_slice($rows, 0, 300),
            'totalRows' => count($rows),
            'newCount' => $newCount,
            'duplicateCount' => $duplicateCount,
            'meta' => [
                'title' => 'Lead Import Preview | OmniReferral',
                'description' => 'Review imported lead rows before inserting into database.',
            ],
        ]);
    }

    public function commitImport(Request $request, LeadMultiFormatImportService $importService): RedirectResponse
    {
        $request->validate([
            'preview_key' => ['required', 'string'],
        ]);

        $key = $request->string('preview_key')->value();
        $cached = Cache::get($key);

        // Support both old format (array of rows) and new format (array with 'rows' key)
        if (is_array($cached) && isset($cached['rows'])) {
            $rows = $cached['rows'];
            $fileName = $cached['file_name'] ?? null;
        } elseif (is_array($cached)) {
            $rows = $cached;
            $fileName = null;
        } else {
            return redirect()->route('admin.leads.index')->with('error', 'Import preview expired. Please upload again.');
        }

        $startedAt = now();
        $result = $importService->importPreparedRows($rows);
        Cache::forget($key);

        LeadImportRun::create([
            'source' => 'file_import',
            'triggered_by_user_id' => $request->user()?->id,
            'status' => 'completed',
            'created_count' => $result['created'],
            'skipped_count' => $result['skipped'],
            'failed_count' => $result['failed'] ?? 0,
            'warnings' => ! empty($result['failed_rows']) ? $result['failed_rows'] : null,
            'file_name' => $fileName,
            'started_at' => $startedAt,
            'finished_at' => now(),
        ]);

        $message = "Import complete. Added {$result['created']} new leads, skipped {$result['skipped']} duplicate/invalid rows.";
        if (($result['failed'] ?? 0) > 0) {
            $message .= " {$result['failed']} rows failed. Check the logs for details.";
        }

        return redirect()
            ->route('admin.leads.index')
            ->with('success', $message);
    }

    /**
     * Show import/sync run history.
     */
    public function importHistory(): View
    {
        $runs = LeadImportRun::query()
            ->with('triggeredBy:id,name')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('pages.admin.leads.import-history', [
            'runs' => $runs,
            'meta' => [
                'title' => 'Import & Sync History | OmniReferral',
                'description' => 'Review past lead imports and Google Sheets syncs.',
            ],
        ]);
    }

    public function store(StoreManualLeadRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        $lead = new Lead();
        $lead->fill([
            'lead_number' => Lead::generateLeadNumber(),
            'source' => $request->user()?->role === 'staff' ? 'staff_entry' : 'admin_entry',
            'source_timestamp' => now(),
            'name' => $validated['name'],
            'email' => ! empty($validated['email']) ? $validated['email'] : null,
            'phone' => $validated['phone'] ?? '',
            'city' => $validated['city'] ?? '',
            'intent' => $validated['intent'] ?? 'buyer',
            'status' => $validated['status'] ?? 'new',
            'zip_code' => '00000',
            'property_address' => $validated['property_address'] ?? '',
            'beds_baths' => $validated['beds_baths'] ?? '',
            'budget' => $validated['budget'] ?? null,
            'dop' => $validated['dop'] ?? null,
            'asking_price' => $validated['asking_price'] ?? null,
            'financing_status' => $validated['financing_status'] ?? '',
            'credit_score' => $validated['credit_score'] ?? null,
            'working_with_realtor' => isset($validated['working_with_realtor']) ? (bool) $validated['working_with_realtor'] : null,
            'timeline' => $validated['timeline'] ?? '',
            'dnc_disclaimer' => $validated['dnc_disclaimer'] ?? '',
            'notes' => $validated['notes'] ?? '',
            'rep_name' => $validated['rep_name'] ?? '',
            'state' => $validated['state'] ?? '',
            'sent_to' => $validated['sent_to'] ?? '',
            'assignment' => $validated['assignment'] ?? '',
            'reason_in_house' => $validated['reason_in_house'] ?? '',
            'realtor_response' => $validated['realtor_response'] ?? '',
            'assigned_agent_id' => $validated['assigned_agent_id'] ?? null,
        ]);

        if (! empty($validated['assigned_agent_id'])) {
            $lead->assigned_at = now();
        }

        $lead->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Lead {$lead->lead_number} created successfully.",
                'lead' => $lead,
            ]);
        }

        return redirect()
            ->route('admin.leads.index')
            ->with('success', "Lead {$lead->lead_number} created successfully.");
    }

    public function liveData(Request $request): JsonResponse
    {
        $filters = app(LeadFilterService::class)->normalizeFromRequest($request);
        $baseQuery = Lead::query()->with('assignedAgent:id,name');
        app(LeadFilterService::class)->apply($baseQuery, $filters);

        $leads = (clone $baseQuery)
            ->orderByRaw('COALESCE(source_timestamp, created_at) DESC')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $summaryQuery = clone $baseQuery;
        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'qualified' => (clone $summaryQuery)->where('status', 'qualified')->count(),
            'rejected' => (clone $summaryQuery)->where('status', 'not_interested')->count(),
            'website' => (clone $summaryQuery)->where('source', 'website')->count(),
        ];

        $agents = User::where('role', 'agent')->orderBy('name')->get(['id', 'name']);
        $statuses = Lead::statusList();

        $html = view('pages.admin.leads.partials.table_rows', [
            'leads' => $leads,
            'agents' => $agents,
            'statuses' => $statuses,
        ])->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'summary' => $summary,
            'total' => $summary['total'],
        ]);
    }

    public function syncGoogleSheet(SyncGoogleSheetRequest $request, LeadMultiFormatImportService $importService): RedirectResponse|JsonResponse
    {
        $sheetUrl = trim((string) (
            $request->input('sheet_url')
            ?: $request->input('sheet_csv_url')
            ?: config('services.google_sheets.leads_sheet_url')
            ?: config('services.google_sheets.leads_csv_url')
        ));

        if (! $sheetUrl) {
            $msg = 'Google Sheets URL is not configured. Provide a URL below or set the GOOGLE_SHEETS_LEADS_URL env variable.';
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $msg], 400)
                : back()->with('error', $msg);
        }

        $sheetCsvUrl = $this->resolveGoogleSheetCsvUrl($sheetUrl);
        if (! $sheetCsvUrl) {
            $msg = 'Please provide a valid Google Sheets link or CSV export URL.';
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $startedAt = now();

        $response = Http::timeout(25)
            ->accept('text/csv')
            ->get($sheetCsvUrl);
        if (! $response->successful()) {
            $this->logImportRun('google_sheets', $request->user()?->id, 'failed', 0, 0, 0, $startedAt, ['HTTP fetch failed with status ' . $response->status()]);
            $msg = 'Failed to fetch Google Sheets CSV.';
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $msg], 502)
                : back()->with('error', $msg);
        }

        $body = trim((string) $response->body());
        if ($body === '' || $this->looksLikeHtml($body)) {
            $this->logImportRun('google_sheets', $request->user()?->id, 'failed', 0, 0, 0, $startedAt, ['Response was HTML instead of CSV — sheet not shared?']);
            $msg = 'Google Sheets could not be read as CSV. Make sure the sheet is shared or published for CSV access.';
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $lines = preg_split('/\r\n|\r|\n/', $body);
        if (! $lines || count($lines) < 2) {
            $this->logImportRun('google_sheets', $request->user()?->id, 'completed', 0, 0, 0, $startedAt);
            $msg = 'Google Sheet has no lead rows to sync.';
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['success' => true, 'created' => 0, 'skipped' => 0, 'failed' => 0, 'message' => $msg])
                : back()->with('info', $msg);
        }

        $header = str_getcsv(array_shift($lines));
        $normalizedHeader = array_map(fn ($col) => Str::lower(trim((string) $col)), $header);
        $rawRows = [];

        foreach ($lines as $lineText) {
            if (! trim($lineText)) {
                continue;
            }

            $row = str_getcsv($lineText);
            $line = [];
            foreach ($normalizedHeader as $idx => $column) {
                $line[$column] = Arr::get($row, $idx);
            }
            $rawRows[] = $line;
        }

        $result = $importService->importRawRows($rawRows, 'google_sheets');

        $this->logImportRun(
            'google_sheets',
            $request->user()?->id,
            'completed',
            $result['created'],
            $result['skipped'],
            $result['failed'] ?? 0,
            $startedAt,
            ! empty($result['failed_rows']) ? $result['failed_rows'] : null,
        );

        $message = "Google Sheets sync complete. Added {$result['created']} new leads, skipped {$result['skipped']} duplicate/invalid rows.";
        if (($result['failed'] ?? 0) > 0) {
            $message .= " {$result['failed']} rows failed. Check the logs for details.";
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'created' => $result['created'],
                'skipped' => $result['skipped'],
                'failed' => $result['failed'] ?? 0,
                'message' => $message,
                'total_leads' => Lead::count(),
            ]);
        }

        return back()->with('success', $message);
    }

    private function logImportRun(string $source, ?int $userId, string $status, int $created, int $skipped, int $failed, $startedAt, ?array $warnings = null, ?string $fileName = null): void
    {
        LeadImportRun::create([
            'source' => $source,
            'triggered_by_user_id' => $userId,
            'status' => $status,
            'created_count' => $created,
            'skipped_count' => $skipped,
            'failed_count' => $failed,
            'warnings' => $warnings,
            'file_name' => $fileName,
            'started_at' => $startedAt,
            'finished_at' => now(),
        ]);
    }

    private function normalizeLeadFilters(Request $request): array
    {
        return app(LeadFilterService::class)->normalizeFromRequest($request);
    }

    private function applyLeadFilters($query, array $filters): void
    {
        app(LeadFilterService::class)->apply($query, $filters);
    }

    private function normalizeFilterDate(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveGoogleSheetCsvUrl(string $sheetUrl): ?string
    {
        $sheetUrl = trim($sheetUrl);
        if ($sheetUrl === '' || ! filter_var($sheetUrl, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($sheetUrl);
        if (! is_array($parts)) {
            return null;
        }

        $scheme = Str::lower((string) ($parts['scheme'] ?? ''));
        $host = Str::lower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '');
        $query = [];
        $fragment = [];
        parse_str((string) ($parts['query'] ?? ''), $query);
        parse_str((string) ($parts['fragment'] ?? ''), $fragment);

        // SSRF hardening: only allow HTTPS Google Sheets exports.
        if ($scheme !== 'https') {
            return null;
        }

        // Allow only docs.google.com (and its subdomains).
        if ($host !== 'docs.google.com' && ! str_ends_with($host, '.docs.google.com')) {
            return null;
        }

        // Accept direct export URLs too, but only on docs.google.com.
        if (str_contains($path, '/spreadsheets/d/') === false) {
            return null;
        }

        if (preg_match('#/spreadsheets/d/([a-zA-Z0-9-_]+)#', $path, $matches) !== 1) {
            return null;
        }

        $params = ['format' => 'csv'];
        $gid = $query['gid'] ?? $fragment['gid'] ?? null;
        if ($gid !== null && $gid !== '') {
            $params['gid'] = $gid;
        }

        return 'https://docs.google.com/spreadsheets/d/' . $matches[1] . '/export?' . http_build_query($params);
    }

    private function looksLikeHtml(string $payload): bool
    {
        $sample = Str::lower(Str::limit(trim($payload), 400, ''));

        return str_contains($sample, '<html')
            || str_contains($sample, '<!doctype html')
            || str_contains($sample, 'google accounts')
            || str_contains($sample, 'sign in');
    }
}
