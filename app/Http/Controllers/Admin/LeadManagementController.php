<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncGoogleSheetRequest;
use App\Models\Lead;
use App\Models\Property;
use App\Models\RealtorProfile;
use App\Models\User;
use App\Services\LeadFilterService;
use App\Services\LeadMultiFormatImportService;
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

        return view('pages.admin.leads.index', [
            'leads' => $leads,
            'filters' => $filters,
            'agents' => User::where('role', 'agent')->orderBy('name')->get(['id', 'name']),
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
            'statuses' => ['new', 'contacted', 'in_progress', 'qualified', 'assigned', 'closed', 'not_interested'],
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
            $result = $importService->importPreparedRows($rows);

            $message = "Import complete. Added {$result['created']} new leads, skipped {$result['skipped']} duplicate/invalid rows.";
            if (($result['failed'] ?? 0) > 0) {
                $message .= " {$result['failed']} rows failed. Check the logs for details.";
            }

            return redirect()
                ->route('admin.leads.index')
                ->with('success', $message);
        }

        $previewKey = 'lead_import_preview_' . Str::uuid();
        Cache::put($previewKey, $rows, now()->addMinutes(30));

        return redirect()->route('admin.leads.import.preview', ['key' => $previewKey]);
    }

    public function previewImport(Request $request): View|RedirectResponse
    {
        $key = (string) $request->string('key')->value();
        $rows = Cache::get($key);
        if (! is_array($rows)) {
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
        $rows = Cache::get($key);
        if (! is_array($rows)) {
            return redirect()->route('admin.leads.index')->with('error', 'Import preview expired. Please upload again.');
        }

        $result = $importService->importPreparedRows($rows);
        Cache::forget($key);

        $message = "Import complete. Added {$result['created']} new leads, skipped {$result['skipped']} duplicate/invalid rows.";
        if (($result['failed'] ?? 0) > 0) {
            $message .= " {$result['failed']} rows failed. Check the logs for details.";
        }

        return redirect()
            ->route('admin.leads.index')
            ->with('success', $message);
    }

    public function syncGoogleSheet(SyncGoogleSheetRequest $request, LeadMultiFormatImportService $importService): RedirectResponse
    {
        $sheetUrl = trim((string) (
            $request->input('sheet_url')
            ?: $request->input('sheet_csv_url')
            ?: config('services.google_sheets.leads_sheet_url')
            ?: config('services.google_sheets.leads_csv_url')
        ));

        if (! $sheetUrl) {
            return back()->with('error', 'Google Sheets URL is not configured.');
        }

        $sheetCsvUrl = $this->resolveGoogleSheetCsvUrl($sheetUrl);
        if (! $sheetCsvUrl) {
            return back()->with('error', 'Please provide a valid Google Sheets link or CSV export URL.');
        }

        $response = Http::timeout(20)
            ->withoutRedirecting()
            ->accept('text/csv')
            ->get($sheetCsvUrl);
        if (! $response->successful()) {
            return back()->with('error', 'Failed to fetch Google Sheets CSV.');
        }

        $body = trim((string) $response->body());
        if ($body === '' || $this->looksLikeHtml($body)) {
            return back()->with('error', 'Google Sheets could not be read as CSV. Make sure the sheet is shared or published for CSV access.');
        }

        $lines = preg_split('/\r\n|\r|\n/', $body);
        if (! $lines || count($lines) < 2) {
            return back()->with('info', 'Google Sheet has no lead rows to sync.');
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

        $message = "Google Sheets sync complete. Added {$result['created']} new leads, skipped {$result['skipped']} duplicate/invalid rows.";
        if (($result['failed'] ?? 0) > 0) {
            $message .= " {$result['failed']} rows failed. Check the logs for details.";
        }

        return back()->with('success', $message);
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
