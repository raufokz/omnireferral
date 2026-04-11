<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Property;
use App\Models\RealtorProfile;
use App\Models\User;
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
        $filters = $this->normalizeLeadFilters($request);
        $baseQuery = Lead::query()->with('assignedAgent:id,name');
        $this->applyLeadFilters($baseQuery, $filters);

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
        $filename = 'leads-export-' . now()->format('Ymd-His') . '.csv';
        $query = Lead::query()->with('assignedAgent:id,name');
        $this->applyLeadFilters($query, $this->normalizeLeadFilters($request));

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

            return redirect()
                ->route('admin.leads.index')
                ->with('success', "Import complete. Added {$result['created']} new leads, skipped {$result['skipped']} duplicate rows.");
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

        return redirect()
            ->route('admin.leads.index')
            ->with('success', "Import complete. Added {$result['created']} new leads, skipped {$result['skipped']} duplicate rows.");
    }

    public function syncGoogleSheet(Request $request, LeadMultiFormatImportService $importService): RedirectResponse
    {
        $request->validate([
            'sheet_url' => ['nullable', 'string', 'max:2000'],
            'sheet_csv_url' => ['nullable', 'string', 'max:2000'],
        ]);

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

        $response = Http::timeout(20)->get($sheetCsvUrl);
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

        return back()->with('success', "Google Sheets sync complete. Added {$result['created']} new leads, skipped {$result['skipped']} duplicate/invalid rows.");
    }

    private function normalizeLeadFilters(Request $request): array
    {
        return [
            'search' => trim((string) $request->string('search')->value()),
            'intent' => trim((string) $request->string('intent')->value()),
            'status' => trim((string) $request->string('status')->value()),
            'agent_id' => $request->integer('agent_id') ?: null,
            'rep_name' => trim((string) $request->string('rep_name')->value()),
            'source' => trim((string) $request->string('source')->value()),
            'date_from' => $this->normalizeFilterDate((string) $request->string('date_from')->value()),
            'date_to' => $this->normalizeFilterDate((string) $request->string('date_to')->value()),
        ];
    }

    private function applyLeadFilters($query, array $filters): void
    {
        $query
            ->when($filters['search'], function ($builder, string $search) {
                $builder->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('lead_number', 'like', "%{$search}%")
                        ->orWhere('zip_code', 'like', "%{$search}%")
                        ->orWhere('property_address', 'like', "%{$search}%")
                        ->orWhere('rep_name', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%")
                        ->orWhere('sent_to', 'like', "%{$search}%")
                        ->orWhere('source', 'like', "%{$search}%");
                });
            })
            ->when($filters['intent'], fn ($builder, string $intent) => $builder->where('intent', $intent))
            ->when($filters['status'], fn ($builder, string $status) => $builder->where('status', $status))
            ->when($filters['agent_id'], fn ($builder, int $agentId) => $builder->where('assigned_agent_id', $agentId))
            ->when($filters['rep_name'], fn ($builder, string $repName) => $builder->where('rep_name', $repName))
            ->when($filters['source'], fn ($builder, string $source) => $builder->where('source', $source))
            ->when($filters['date_from'], fn ($builder, string $dateFrom) => $builder->whereRaw('DATE(COALESCE(source_timestamp, created_at)) >= ?', [$dateFrom]))
            ->when($filters['date_to'], fn ($builder, string $dateTo) => $builder->whereRaw('DATE(COALESCE(source_timestamp, created_at)) <= ?', [$dateTo]));
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

        $host = Str::lower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '');
        $query = [];
        $fragment = [];
        parse_str((string) ($parts['query'] ?? ''), $query);
        parse_str((string) ($parts['fragment'] ?? ''), $fragment);

        if (! str_contains($host, 'docs.google.com') || ! str_contains($path, '/spreadsheets/d/')) {
            return $sheetUrl;
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
