<?php

namespace App\Jobs;

use App\Models\DataExport;
use App\Models\Enquiry;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadFilterService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateDataExport implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];
    public int $timeout = 120;

    public function __construct(public int $exportId)
    {
    }

    public function handle(LeadFilterService $leadFilters): void
    {
        $export = DataExport::find($this->exportId);
        if (! $export) {
            return;
        }

        if ($export->status === 'complete') {
            return;
        }

        $export->forceFill([
            'status' => 'running',
            'started_at' => now(),
            'error' => null,
        ])->save();

        try {
            Storage::disk('local')->makeDirectory('exports');

            [$path, $contentType, $size] = match ([$export->type, $export->format]) {
                ['users', 'csv'] => $this->exportUsersCsv(),
                ['users', 'xlsx'] => $this->exportUsersXlsx(),
                ['enquiries', 'csv'] => $this->exportEnquiriesCsv(),
                ['enquiries', 'xlsx'] => $this->exportEnquiriesXlsx(),
                ['leads', 'csv'] => $this->exportLeadsCsv($leadFilters, (array) ($export->filters ?? [])),
                default => throw new \InvalidArgumentException("Unsupported export type/format: {$export->type}/{$export->format}"),
            };

            $export->forceFill([
                'status' => 'complete',
                'file_path' => $path,
                'content_type' => $contentType,
                'file_size' => $size,
                'finished_at' => now(),
            ])->save();
        } catch (\Throwable $e) {
            Log::error('Data export failed.', [
                'export_id' => $this->exportId,
                'type' => $export->type,
                'format' => $export->format,
                'exception' => $e->getMessage(),
            ]);

            $export->forceFill([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'finished_at' => now(),
            ])->save();

            throw $e;
        }
    }

    private function exportUsersCsv(): array
    {
        $path = 'exports/users-' . now()->format('Ymd-His') . '-' . uniqid() . '.csv';
        $fullPath = Storage::disk('local')->path($path);

        $handle = fopen($fullPath, 'w');
        fputcsv($handle, [
            'id', 'name', 'display_name', 'email', 'phone', 'role', 'status', 'staff_team', 'created_at', 'last_synced_at',
        ]);

        User::query()->orderBy('id')->chunkById(1000, function ($rows) use ($handle) {
            foreach ($rows as $u) {
                fputcsv($handle, [
                    $u->id,
                    $u->name,
                    $u->display_name,
                    $u->email,
                    $u->phone,
                    $u->role,
                    $u->status,
                    $u->staff_team,
                    optional($u->created_at)?->toDateTimeString(),
                    optional($u->last_synced_at)?->toDateTimeString(),
                ]);
            }
        });

        fclose($handle);

        return [$path, 'text/csv; charset=UTF-8', filesize($fullPath) ?: null];
    }

    private function exportUsersXlsx(): array
    {
        $path = 'exports/users-' . now()->format('Ymd-His') . '-' . uniqid() . '.xlsx';
        $fullPath = Storage::disk('local')->path($path);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['ID', 'Name', 'Display name', 'Email', 'Phone', 'Role', 'Status', 'Staff team', 'Joined', 'Last synced'],
        ], null, 'A1');

        $row = 2;
        User::query()->orderBy('id')->chunkById(1000, function ($users) use ($sheet, &$row) {
            foreach ($users as $u) {
                $sheet->fromArray([[
                    $u->id,
                    $u->name,
                    $u->display_name,
                    $u->email,
                    $u->phone,
                    $u->role,
                    $u->status,
                    $u->staff_team,
                    optional($u->created_at)?->toDateTimeString(),
                    optional($u->last_synced_at)?->toDateTimeString(),
                ]], null, 'A' . $row);
                $row++;
            }
        });

        (new Xlsx($spreadsheet))->save($fullPath);

        return [$path, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', filesize($fullPath) ?: null];
    }

    private function exportEnquiriesCsv(): array
    {
        $path = 'exports/enquiries-' . now()->format('Ymd-His') . '-' . uniqid() . '.csv';
        $fullPath = Storage::disk('local')->path($path);

        $handle = fopen($fullPath, 'w');
        fputcsv($handle, [
            'id', 'created_at', 'status', 'sender_name', 'sender_email', 'sender_phone',
            'sender_user_id', 'receiver_user_id', 'receiver_name', 'receiver_email',
            'property_id', 'property_title', 'subject', 'message_excerpt', 'replies_count',
        ]);

        Enquiry::query()
            ->with(['property:id,title', 'receiver:id,name,email', 'sender:id,name,email'])
            ->withCount('replies')
            ->orderBy('id')
            ->chunkById(1000, function ($rows) use ($handle) {
                foreach ($rows as $e) {
                    fputcsv($handle, [
                        $e->id,
                        optional($e->created_at)?->toDateTimeString(),
                        $e->status,
                        $e->sender_name,
                        $e->sender_email,
                        $e->sender_phone,
                        $e->sender_user_id,
                        $e->receiver_user_id,
                        $e->receiver?->name,
                        $e->receiver?->email,
                        $e->property_id,
                        $e->property?->title,
                        $e->subject,
                        \Illuminate\Support\Str::limit((string) $e->message, 200),
                        $e->replies_count,
                    ]);
                }
            });

        fclose($handle);

        return [$path, 'text/csv; charset=UTF-8', filesize($fullPath) ?: null];
    }

    private function exportEnquiriesXlsx(): array
    {
        $path = 'exports/enquiries-' . now()->format('Ymd-His') . '-' . uniqid() . '.xlsx';
        $fullPath = Storage::disk('local')->path($path);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['ID', 'Created', 'Status', 'Sender', 'Email', 'Receiver', 'Property', 'Subject', 'Replies', 'Message (preview)'],
        ], null, 'A1');

        $row = 2;
        Enquiry::query()
            ->with(['property:id,title', 'receiver:id,name,email'])
            ->withCount('replies')
            ->orderBy('id')
            ->chunkById(1000, function ($rows) use ($sheet, &$row) {
                foreach ($rows as $e) {
                    $sheet->fromArray([[
                        $e->id,
                        optional($e->created_at)?->toDateTimeString(),
                        $e->status,
                        $e->sender_name,
                        $e->sender_email,
                        $e->receiver?->name . ' / ' . $e->receiver?->email,
                        $e->property?->title,
                        $e->subject,
                        $e->replies_count,
                        \Illuminate\Support\Str::limit((string) $e->message, 240),
                    ]], null, 'A' . $row);
                    $row++;
                }
            });

        (new Xlsx($spreadsheet))->save($fullPath);

        return [$path, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', filesize($fullPath) ?: null];
    }

    private function exportLeadsCsv(LeadFilterService $filters, array $rawFilters): array
    {
        $path = 'exports/leads-' . now()->format('Ymd-His') . '-' . uniqid() . '.csv';
        $fullPath = Storage::disk('local')->path($path);

        $handle = fopen($fullPath, 'w');
        fputcsv($handle, [
            'timestamp', 'lead_number', 'lead_name', 'intent', 'property_address_or_desired_area', 'beds_baths',
            'budget', 'asking_price', 'working_with_realtor', 'timeline', 'dnc_disclaimer', 'notes',
            'phone_number', 'email', 'rep_name', 'state', 'sent_to', 'status', 'assignment',
            'reason_in_house', 'realtor_response', 'assigned_agent', 'source', 'created_at',
        ]);

        $query = Lead::query()->with('assignedAgent:id,name');
        $filters->apply($query, $filters->normalizeFromArray($rawFilters));

        $query->chunkById(1000, function ($rows) use ($handle) {
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

        return [$path, 'text/csv; charset=UTF-8', filesize($fullPath) ?: null];
    }
}

