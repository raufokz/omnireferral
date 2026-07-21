<?php

namespace App\Console\Commands;

use App\Services\LeadMultiFormatImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SyncGoogleSheetLeadsCommand extends Command
{
    protected $signature = 'leads:sync-sheets {--url= : Optional Google Sheets URL override}';

    protected $description = 'Auto-sync Google Sheets leads into the registry database without full page refreshes';

    public function handle(LeadMultiFormatImportService $importService): int
    {
        $sheetUrl = trim((string) (
            $this->option('url')
            ?: config('services.google_sheets.leads_sheet_url')
            ?: config('services.google_sheets.leads_csv_url')
        ));

        if (! $sheetUrl) {
            $this->error('No Google Sheets URL configured.');
            return self::FAILURE;
        }

        $sheetCsvUrl = $this->resolveGoogleSheetCsvUrl($sheetUrl);
        if (! $sheetCsvUrl) {
            $this->error('Invalid Google Sheets URL format.');
            return self::FAILURE;
        }

        $this->info("Fetching Google Sheet CSV from: {$sheetCsvUrl}");

        try {
            $response = Http::timeout(25)
                ->accept('text/csv')
                ->get($sheetCsvUrl);

            if (! $response->successful()) {
                $this->error("HTTP request failed with status: " . $response->status());
                return self::FAILURE;
            }

            $body = trim((string) $response->body());
            if ($body === '' || $this->looksLikeHtml($body)) {
                $this->error('Response was empty or HTML. Verify sheet is shared/published.');
                return self::FAILURE;
            }

            $lines = preg_split('/\r\n|\r|\n/', $body);
            if (! $lines || count($lines) < 2) {
                $this->info('Google Sheet has no lead rows to sync.');
                return self::SUCCESS;
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

            $this->info("Google Sheet sync completed successfully!");
            $this->line(" - Added: {$result['created']} new leads");
            $this->line(" - Skipped: {$result['skipped']} duplicates/invalid");
            if (($result['failed'] ?? 0) > 0) {
                $this->warn(" - Failed: {$result['failed']} rows");
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Google Sheet auto-sync failed: " . $e->getMessage());
            return self::FAILURE;
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

        if ($scheme !== 'https') {
            return null;
        }

        if ($host !== 'docs.google.com' && ! str_ends_with($host, '.docs.google.com')) {
            return null;
        }

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
