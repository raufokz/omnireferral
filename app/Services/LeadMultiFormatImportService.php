<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use Smalot\PdfParser\Parser as PdfParser;

class LeadMultiFormatImportService
{
    public function previewFile(UploadedFile $file): array
    {
        $extension = Str::lower((string) $file->getClientOriginalExtension());

        $rows = match ($extension) {
            'csv', 'txt' => $this->rowsFromDelimitedFile($file->getRealPath()),
            'xlsx', 'xls' => $this->rowsFromSpreadsheet($file->getRealPath()),
            'json' => $this->rowsFromJson($file->getRealPath()),
            'pdf' => $this->rowsFromFlatText($this->extractPdfText($file->getRealPath())),
            'docx' => $this->rowsFromFlatText($this->extractWordText($file->getRealPath(), 'Word2007')),
            'doc' => $this->rowsFromFlatText($this->extractWordText($file->getRealPath(), 'MsDoc')),
            default => [],
        };

        return $this->prepareRows($rows, 'file_import');
    }

    public function importPreparedRows(array $rows): array
    {
        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if (! is_array($row) || ($row['_duplicate'] ?? false) || ($row['_invalid'] ?? false)) {
                $skipped++;
                continue;
            }

            $lead = new Lead();
            $lead->fill([
                'lead_number' => 'OMNI-' . now()->format('Ymd') . '-' . str_pad((string) (Lead::withTrashed()->count() + 1), 4, '0', STR_PAD_LEFT),
                'source' => (string) ($row['_source'] ?? 'file_import'),
                'source_timestamp' => $row['source_timestamp'] ?? null,
                'package_type' => 'quick',
                'name' => (string) ($row['name'] ?? ''),
                'email' => (string) ($row['email'] ?? ''),
                'phone' => (string) ($row['phone'] ?? ''),
                'intent' => (string) ($row['intent'] ?? 'buyer'),
                'status' => (string) ($row['status'] ?? 'new'),
                'zip_code' => (string) ($row['zip_code'] ?? '00000'),
                'property_address' => (string) ($row['property_address'] ?? ''),
                'beds_baths' => (string) ($row['beds_baths'] ?? ''),
                'working_with_realtor' => $row['working_with_realtor'] ?? null,
                'dnc_disclaimer' => (string) ($row['dnc_disclaimer'] ?? ''),
                'property_type' => (string) ($row['property_type'] ?? ''),
                'budget' => $row['budget'] ?? null,
                'asking_price' => $row['asking_price'] ?? null,
                'timeline' => (string) ($row['timeline'] ?? ''),
                'preferences' => (string) ($row['preferences'] ?? ''),
                'notes' => (string) ($row['notes'] ?? ''),
                'rep_name' => (string) ($row['rep_name'] ?? ''),
                'state' => (string) ($row['state'] ?? ''),
                'sent_to' => (string) ($row['sent_to'] ?? ''),
                'assignment' => (string) ($row['assignment'] ?? ''),
                'reason_in_house' => (string) ($row['reason_in_house'] ?? ''),
                'realtor_response' => (string) ($row['realtor_response'] ?? ''),
                'form_data' => $row['form_data'] ?? [],
            ]);
            $lead->save();
            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    public function importRawRows(array $rawRows, string $source = 'google_sheets'): array
    {
        return $this->importPreparedRows($this->prepareRows($rawRows, $source));
    }

    private function prepareRows(array $rawRows, string $source): array
    {
        $prepared = [];

        foreach ($rawRows as $rawRow) {
            if (! is_array($rawRow)) {
                continue;
            }

            $line = $this->mapIncomingRow($rawRow);
            $name = trim((string) ($line['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $rawEmail = trim((string) ($line['email'] ?? ''));
            $rawPhone = trim((string) ($line['phone'] ?? ''));
            $phone = $rawPhone !== '' ? $rawPhone : $this->extractPhone((string) ($line['lead_name'] ?? $name));
            $email = $rawEmail !== '' ? $rawEmail : $this->fallbackEmail($name, $phone);
            $propertyAddress = trim((string) ($line['property_address'] ?? ''));
            $zipCode = $this->extractZip($propertyAddress ?: (string) ($line['zip_code'] ?? ''));
            $status = $this->normalizeStatus((string) ($line['status'] ?? ''))
                ?? $this->mapColorToStatus((string) ($line['status_color'] ?? $line['color'] ?? ''))
                ?? 'new';
            $resolvedSource = $this->normalizeSource($line['source'] ?? null, $source);

            if ($status === 'assigned') {
                $status = 'new';
            }

            [$isDuplicate, $duplicateReason] = $this->detectDuplicate($rawEmail, $phone, $name, $zipCode, $propertyAddress);

            $prepared[] = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone ?: 'N/A',
                'intent' => $this->normalizeIntent((string) ($line['intent'] ?? 'buyer')),
                'status' => $status,
                'source_timestamp' => $this->parseTimestamp($line['source_timestamp'] ?? null),
                'zip_code' => $zipCode,
                'property_address' => $propertyAddress,
                'beds_baths' => trim((string) ($line['beds_baths'] ?? '')),
                'working_with_realtor' => $this->parseYesNo($line['working_with_realtor'] ?? null),
                'dnc_disclaimer' => trim((string) ($line['dnc_disclaimer'] ?? '')),
                'property_type' => trim((string) ($line['property_type'] ?? '')),
                'budget' => $this->parseAmount($line['budget'] ?? null),
                'asking_price' => $this->parseAmount($line['asking_price'] ?? null),
                'timeline' => trim((string) ($line['timeline'] ?? '')),
                'preferences' => trim((string) ($line['notes'] ?? '')),
                'notes' => trim((string) ($line['notes'] ?? '')),
                'rep_name' => trim((string) ($line['rep_name'] ?? '')),
                'state' => trim((string) ($line['state'] ?? '')),
                'sent_to' => trim((string) ($line['sent_to'] ?? '')),
                'assignment' => trim((string) ($line['assignment'] ?? '')),
                'reason_in_house' => trim((string) ($line['reason_in_house'] ?? '')),
                'realtor_response' => trim((string) ($line['realtor_response'] ?? '')),
                'form_data' => [
                    'imported_status' => $line['status'] ?? null,
                    'imported_assignment' => $line['assignment'] ?? null,
                    'status_color' => $line['status_color'] ?? $line['color'] ?? null,
                    'source_timestamp_raw' => $line['source_timestamp'] ?? null,
                    'lead_name_raw' => $line['lead_name'] ?? null,
                    'import_channel' => $source,
                    'imported_source' => $line['source'] ?? null,
                ],
                '_duplicate' => $isDuplicate,
                '_duplicate_reason' => $duplicateReason,
                '_invalid' => false,
                '_source' => $resolvedSource,
            ];
        }

        return $prepared;
    }

    private function rowsFromDelimitedFile(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            return [];
        }

        $headerLine = (string) fgets($handle);
        $delimiter = $this->detectDelimiter($headerLine);

        if ($delimiter === null) {
            fclose($handle);
            return $this->rowsFromFlatText((string) file_get_contents($path));
        }

        rewind($handle);
        $headers = array_map(fn ($col) => $this->canonicalKey((string) $col), fgetcsv($handle, 0, $delimiter) ?: []);
        $rows = [];

        while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = $values[$index] ?? null;
            }
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function rowsFromSpreadsheet(string $path): array
    {
        $sheet = SpreadsheetIOFactory::load($path)->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();
        $headerCells = $sheet->rangeToArray("A1:{$highestCol}1", null, true, true, true)[1] ?? [];
        $headers = [];

        foreach ($headerCells as $column => $value) {
            $headers[$column] = $this->canonicalKey((string) $value);
        }

        $statusColumn = array_search('status', $headers, true);
        $rows = [];

        for ($rowNumber = 2; $rowNumber <= $highestRow; $rowNumber++) {
            $range = $sheet->rangeToArray("A{$rowNumber}:{$highestCol}{$rowNumber}", null, true, true, true)[$rowNumber] ?? [];
            $line = [];

            foreach ($headers as $column => $headerName) {
                $line[$headerName] = $range[$column] ?? null;
            }

            if ($statusColumn && empty($line['status'])) {
                $rgb = (string) $sheet->getStyle("{$statusColumn}{$rowNumber}")->getFill()->getStartColor()->getRGB();
                if ($rgb !== '') {
                    $line['status_color'] = $rgb;
                }
            }

            $rows[] = $line;
        }

        return $rows;
    }

    private function rowsFromJson(string $path): array
    {
        $payload = json_decode((string) file_get_contents($path), true);
        if (! is_array($payload)) {
            return [];
        }

        if (array_is_list($payload)) {
            return array_map(fn ($row) => is_array($row) ? $this->normalizeKeys($row) : [], $payload);
        }

        $data = data_get($payload, 'data');

        if (is_array($data) && array_is_list($data)) {
            return array_map(fn ($row) => is_array($row) ? $this->normalizeKeys($row) : [], $data);
        }

        return [];
    }

    private function rowsFromFlatText(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $headerIndex = $this->detectTableHeaderIndex($lines);

        if ($headerIndex !== null) {
            $headerLine = $lines[$headerIndex];
            $delimiter = $this->detectDelimiter($headerLine);
            if ($delimiter !== null) {
                $headers = array_map(fn ($col) => $this->canonicalKey((string) $col), str_getcsv($headerLine, $delimiter));
                $rows = [];

                foreach (array_slice($lines, $headerIndex + 1) as $line) {
                    if (trim($line) === '') {
                        continue;
                    }

                    $values = str_getcsv($line, $delimiter);
                    if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                        continue;
                    }

                    $row = [];
                    foreach ($headers as $index => $header) {
                        $row[$header] = $values[$index] ?? null;
                    }
                    $rows[] = $row;
                }

                if ($rows !== []) {
                    return $rows;
                }
            }
        }

        $blocks = preg_split("/\n\s*\n/", $text) ?: [];
        $rows = [];

        foreach ($blocks as $block) {
            $blockLines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', trim($block)) ?: [])));
            if ($blockLines === []) {
                continue;
            }

            $row = [];
            foreach ($blockLines as $line) {
                if (preg_match('/^\s*([A-Za-z][A-Za-z0-9\/&() ._\-]{1,120})\s*[:=-]\s*(.+)$/', $line, $matches)) {
                    $row[$this->canonicalKey($matches[1])] = trim($matches[2]);
                }
            }

            if (count($row) >= 2) {
                $rows[] = $row;
                continue;
            }

            if (count($blockLines) === 1) {
                $delimiter = $this->detectDelimiter($blockLines[0]);
                if ($delimiter !== null) {
                    $parts = array_map('trim', str_getcsv($blockLines[0], $delimiter));
                    if (count($parts) >= 2) {
                        $rows[] = [
                            'lead_name' => $parts[0] ?? null,
                            'email' => $parts[1] ?? null,
                            'phone' => $parts[2] ?? null,
                            'buyer_seller_investor_other' => $parts[3] ?? null,
                            'status' => $parts[4] ?? null,
                            'property_address_or_desired_area' => $parts[5] ?? null,
                            'color' => $parts[6] ?? null,
                        ];
                    }
                }
            }
        }

        return $rows;
    }

    private function extractPdfText(string $path): string
    {
        try {
            return (new PdfParser())->parseFile($path)->getText();
        } catch (\Throwable) {
            return '';
        }
    }

    private function extractWordText(string $path, string $reader): string
    {
        try {
            $document = WordIOFactory::load($path, $reader);
        } catch (\Throwable) {
            return '';
        }

        $chunks = [];

        if (method_exists($document, 'getSections')) {
            foreach ($document->getSections() as $section) {
                $chunks = array_merge($chunks, $this->extractElementText($section));
            }
        }

        return trim(implode(PHP_EOL, array_filter($chunks)));
    }

    private function extractElementText(mixed $element): array
    {
        $chunks = [];

        if (! is_object($element)) {
            return $chunks;
        }

        if (method_exists($element, 'getText')) {
            $text = trim((string) $element->getText());
            if ($text !== '') {
                $chunks[] = $text;
            }
        }

        if (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $child) {
                $chunks = array_merge($chunks, $this->extractElementText($child));
            }
        }

        if (method_exists($element, 'getRows')) {
            foreach ($element->getRows() as $row) {
                $chunks = array_merge($chunks, $this->extractElementText($row));
            }
        }

        if (method_exists($element, 'getCells')) {
            foreach ($element->getCells() as $cell) {
                $chunks = array_merge($chunks, $this->extractElementText($cell));
            }
        }

        if (method_exists($element, 'getSections')) {
            foreach ($element->getSections() as $section) {
                $chunks = array_merge($chunks, $this->extractElementText($section));
            }
        }

        return $chunks;
    }

    private function mapIncomingRow(array $line): array
    {
        $line = $this->normalizeKeys($line);
        $budgetOrAsking = $this->valueFromAliases($line, [
            'budget_or_asking_price',
            'budget_asking_price',
            'budget_or_asking',
        ]);

        return [
            'source_timestamp' => $this->valueFromAliases($line, [
                'timestamp',
                'created_at',
                'submitted_at',
                'date',
                'date_time',
            ]),
            'name' => $this->valueFromAliases($line, [
                'lead_name',
                'name',
                'full_name',
                'customer_name',
            ]),
            'lead_name' => $this->valueFromAliases($line, ['lead_name', 'name']),
            'intent' => $this->valueFromAliases($line, [
                'buyer_seller_investor_other',
                'buyer_seller_investor_other_',
                'intent',
                'lead_type',
                'type',
            ]),
            'property_address' => $this->valueFromAliases($line, [
                'property_address_or_desired_area',
                'property_address',
                'desired_area',
                'desired_area_to_buy_or_selling_property_address',
                'address',
                'desired_area_to_buy',
            ]),
            'zip_code' => $this->valueFromAliases($line, ['zip_code', 'zip', 'postal_code']),
            'beds_baths' => $this->valueFromAliases($line, [
                'beds_baths',
                'beds_and_baths',
                'how_many_beds_n_baths',
                'bed_bath',
            ]),
            'budget' => $this->valueFromAliases($line, ['budget', 'buyer_budget']) ?? $budgetOrAsking,
            'asking_price' => $this->valueFromAliases($line, ['asking_price', 'listing_price', 'seller_price']) ?? $budgetOrAsking,
            'working_with_realtor' => $this->valueFromAliases($line, [
                'working_with_realtor',
                'working_with_realtor_yes_no',
                'working_with_a_realtor_already_yes_no',
                'working_with_a_realtor_already',
                'has_realtor',
                'realtor_yes_no',
            ]),
            'timeline' => $this->valueFromAliases($line, [
                'timeline',
                'how_soon_will_the_lead_act',
                'how_soon',
                'move_timeline',
            ]),
            'dnc_disclaimer' => $this->valueFromAliases($line, [
                'dnc_disclaimer',
                'dnc_disclaimer_yes_no',
                'dnc_disclaimer_clear_yes',
                'dnc',
            ]),
            'notes' => $this->valueFromAliases($line, [
                'notes',
                'additional_notes',
                'comment',
                'comments',
            ]),
            'phone' => $this->valueFromAliases($line, [
                'phone_number',
                'phone',
                'number',
                'mobile',
                'contact_number',
            ]),
            'email' => $this->valueFromAliases($line, ['email', 'email_address']),
            'rep_name' => $this->valueFromAliases($line, ['rep_name', 'representative', 'sales_rep']),
            'state' => $this->valueFromAliases($line, ['state', 'state_of_buying_selling']),
            'sent_to' => $this->valueFromAliases($line, ['sent_to', 'whom_to_send', 'forwarded_to']),
            'status' => $this->valueFromAliases($line, ['status', 'lead_status']),
            'assignment' => $this->valueFromAliases($line, ['assignment', 'assigned_to', 'agent_assignment']),
            'reason_in_house' => $this->valueFromAliases($line, ['reason_in_house', 'reason', 'reason_in_house_']),
            'realtor_response' => $this->valueFromAliases($line, ['realtor_response', 'response_from_realtor', 'agent_response']),
            'property_type' => $this->valueFromAliases($line, ['property_type', 'home_type']),
            'source' => $this->valueFromAliases($line, [
                'source',
                'lead_source',
                'origin',
                'channel',
                'submitted_from',
            ]),
            'color' => $this->valueFromAliases($line, ['color']),
            'status_color' => $this->valueFromAliases($line, ['status_color']),
        ];
    }

    private function normalizeKeys(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $rawKey = Str::lower(trim((string) $key));
            $normalized[$rawKey] = $value;
            $normalized[$this->canonicalKey($rawKey)] = $value;
        }

        return $normalized;
    }

    private function canonicalKey(string $key): string
    {
        return (string) Str::of(Str::lower(trim($key)))
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_');
    }

    private function valueFromAliases(array $line, array $aliases): mixed
    {
        foreach ($aliases as $alias) {
            $canonical = $this->canonicalKey($alias);
            $value = $line[$canonical] ?? $line[$alias] ?? null;

            if ($value !== null && trim((string) $value) !== '') {
                return $value;
            }
        }

        return null;
    }

    private function normalizeIntent(string $intent): string
    {
        $intent = Str::lower(trim($intent));

        return match (true) {
            str_contains($intent, 'sell') => 'seller',
            str_contains($intent, 'invest') => 'investor',
            str_contains($intent, 'other') => 'other',
            default => 'buyer',
        };
    }

    private function normalizeStatus(string $status): ?string
    {
        $status = Str::of($status)->lower()->replace([' ', '-'], '_')->toString();
        $status = match ($status) {
            'rejected', 'reject', 'not_interested_lead', 'not_interested', 'disqualified' => 'not_interested',
            'working', 'open', 'pending' => 'in_progress',
            default => $status,
        };

        return in_array($status, ['new', 'contacted', 'in_progress', 'qualified', 'assigned', 'closed', 'not_interested'], true)
            ? $status
            : null;
    }

    private function normalizeSource(mixed $source, string $fallback = 'file_import'): string
    {
        $normalized = Str::of((string) $source)->lower()->trim()->toString();

        if ($normalized === '') {
            return $fallback;
        }

        return match (true) {
            str_contains($normalized, 'website'),
            str_contains($normalized, 'web form'),
            str_contains($normalized, 'webform'),
            $normalized === 'web',
            str_contains($normalized, 'landing') => 'website',
            str_contains($normalized, 'google sheet'),
            str_contains($normalized, 'spreadsheet'),
            str_contains($normalized, 'sheet') => 'google_sheets',
            str_contains($normalized, 'csv'),
            str_contains($normalized, 'xlsx'),
            str_contains($normalized, 'xls'),
            str_contains($normalized, 'excel'),
            str_contains($normalized, 'pdf'),
            str_contains($normalized, 'doc'),
            str_contains($normalized, 'import') => 'file_import',
            default => Str::slug($normalized, '_')->toString() ?: $fallback,
        };
    }

    private function mapColorToStatus(string $color): ?string
    {
        $raw = Str::lower(trim($color));
        $raw = ltrim($raw, '#');

        return match (true) {
            in_array($raw, ['green', '00ff00', '008000', '92d050'], true) => 'qualified',
            in_array($raw, ['red', 'ff0000', 'c00000'], true) => 'not_interested',
            in_array($raw, ['yellow', 'ffff00', 'ffc000'], true) => 'in_progress',
            in_array($raw, ['orange', 'ed7d31'], true) => 'contacted',
            in_array($raw, ['blue', '4472c4'], true) => 'qualified',
            default => null,
        };
    }

    private function parseTimestamp(mixed $value): ?Carbon
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseAmount(mixed $raw): ?int
    {
        if ($raw === null) {
            return null;
        }

        $value = Str::lower(trim((string) $raw));

        if ($value === '') {
            return null;
        }

        if (preg_match('/(\d+(?:\.\d+)?)\s*k\b/', $value, $matches)) {
            return (int) round(((float) $matches[1]) * 1000);
        }

        if (preg_match('/\d[\d,]*/', $value, $matches)) {
            return (int) str_replace(',', '', $matches[0]);
        }

        return null;
    }

    private function parseYesNo(mixed $value): ?bool
    {
        $normalized = Str::lower(trim((string) $value));

        return match ($normalized) {
            'yes', 'y', 'true', '1' => true,
            'no', 'n', 'false', '0' => false,
            default => null,
        };
    }

    private function extractPhone(string $text): string
    {
        preg_match('/\d{10,15}/', preg_replace('/\D+/', '', $text), $matches);

        return $matches[0] ?? '';
    }

    private function fallbackEmail(string $name, string $phone): string
    {
        $base = Str::slug($name ?: 'lead');
        $suffix = $phone !== '' ? $phone : Str::lower(Str::random(6));

        return "{$base}.{$suffix}@import.local";
    }

    private function extractZip(string $text): string
    {
        if (preg_match('/\b\d{5}(?:-\d{4})?\b/', $text, $matches)) {
            return substr($matches[0], 0, 5);
        }

        return '00000';
    }

    private function detectDuplicate(string $email, string $phone, string $name, string $zipCode, string $propertyAddress): array
    {
        $duplicate = Lead::duplicateQuery($email, $phone)->first();

        if ($duplicate) {
            $reason = Lead::normalizeEmail($email) && Str::lower($duplicate->email) === Lead::normalizeEmail($email)
                ? 'Email already exists'
                : 'Phone number already exists';

            return [true, $reason];
        }

        $fallbackDuplicate = Lead::query()
            ->withTrashed()
            ->where('name', $name)
            ->where(function ($query) use ($zipCode, $propertyAddress) {
                $query->where('zip_code', $zipCode);

                if ($propertyAddress !== '') {
                    $query->orWhere('property_address', $propertyAddress);
                }
            })
            ->exists();

        return [$fallbackDuplicate, $fallbackDuplicate ? 'Matching lead name and market already exists' : null];
    }

    private function detectDelimiter(string $line): ?string
    {
        foreach ([",", "\t", "|", ";"] as $delimiter) {
            if (substr_count($line, $delimiter) >= 1) {
                return $delimiter;
            }
        }

        return null;
    }

    private function detectTableHeaderIndex(array $lines): ?int
    {
        foreach ($lines as $index => $line) {
            $lower = Str::lower($line);

            if (
                (str_contains($lower, 'lead') || str_contains($lower, 'name'))
                && (str_contains($lower, 'phone') || str_contains($lower, 'email'))
                && $this->detectDelimiter($line) !== null
            ) {
                return $index;
            }
        }

        return null;
    }
}
