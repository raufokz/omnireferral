<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Services\EnquiryReplyService;
use App\Support\AdminAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnquiryController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isStaff(), 403);

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'property_id' => (string) $request->query('property_id', ''),
            'user_id' => (string) $request->query('user_id', ''),
            'status' => (string) $request->query('status', ''),
            'date_from' => (string) $request->query('date_from', ''),
            'date_to' => (string) $request->query('date_to', ''),
        ];

        $query = Enquiry::query()
            ->with([
                'property:id,title,slug,location,zip_code',
                'receiver:id,name,email',
                'sender:id,name,email',
                'contact:id,source',
            ])
            ->withCount('replies');

        if ($filters['search'] !== '') {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('sender_name', 'like', '%' . $s . '%')
                    ->orWhere('sender_email', 'like', '%' . $s . '%')
                    ->orWhere('subject', 'like', '%' . $s . '%')
                    ->orWhere('message', 'like', '%' . $s . '%')
                    ->orWhereHas('property', function ($pq) use ($s) {
                        $pq->where('title', 'like', '%' . $s . '%');
                    });
            });
        }

        if ($filters['property_id'] !== '' && is_numeric($filters['property_id'])) {
            $query->where('property_id', (int) $filters['property_id']);
        }

        if ($filters['user_id'] !== '' && is_numeric($filters['user_id'])) {
            $uid = (int) $filters['user_id'];
            $query->where(function ($q) use ($uid) {
                $q->where('sender_user_id', $uid)
                    ->orWhere('receiver_user_id', $uid);
            });
        }

        if ($filters['status'] !== '' && in_array($filters['status'], [Enquiry::STATUS_PENDING, Enquiry::STATUS_REPLIED, Enquiry::STATUS_CLOSED], true)) {
            $query->where('status', $filters['status']);
        }

        if ($filters['date_from'] !== '') {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] !== '') {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $enquiries = $query->latest()->paginate(25)->withQueryString();

        return view('pages.admin.enquiries.index', [
            'enquiries' => $enquiries,
            'filters' => $filters,
            'canExport' => $request->user()->isAdmin(),
            'isStaffView' => $request->user()->role === 'staff',
            'meta' => [
                'title' => 'Enquiries & conversations | OmniReferral',
                'description' => 'Property listing enquiries with threaded replies between senders, owners, and operations.',
            ],
        ]);
    }

    public function show(Request $request, Enquiry $enquiry): View
    {
        abort_unless($request->user()?->isStaff(), 403);

        $enquiry->load([
            'property',
            'receiver',
            'sender',
            'contact',
            'replies.sender',
        ]);

        return view('pages.admin.enquiries.show', [
            'enquiry' => $enquiry,
            'isStaffView' => $request->user()->role === 'staff',
            'canReply' => true,
            'replyUrl' => route('admin.enquiries.replies.store', $enquiry),
            'statusUrl' => route('admin.enquiries.status', $enquiry),
            'meta' => [
                'title' => 'Enquiry #' . $enquiry->id . ' | OmniReferral',
                'description' => 'Conversation thread and routing for this listing enquiry.',
            ],
        ]);
    }

    public function storeReply(Request $request, Enquiry $enquiry): RedirectResponse
    {
        abort_unless($request->user()?->isStaff(), 403);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:20000'],
        ]);

        EnquiryReplyService::store($enquiry, $request->user(), $validated['message'], $request);

        return back()->with('success', 'Reply sent.');
    }

    public function updateStatus(Request $request, Enquiry $enquiry): RedirectResponse
    {
        abort_unless($request->user()?->isStaff(), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in([Enquiry::STATUS_PENDING, Enquiry::STATUS_REPLIED, Enquiry::STATUS_CLOSED])],
        ]);

        $enquiry->update(['status' => $validated['status']]);
        $enquiry->syncLinkedContact();

        AdminAudit::log(
            $request,
            'enquiries.status',
            Enquiry::class,
            (int) $enquiry->id,
            ['status' => $validated['status']]
        );

        return back()->with('success', 'Status updated.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        AdminAudit::log($request, 'enquiries.export.csv', null, null, []);

        $filename = 'enquiries-export-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'id', 'created_at', 'status', 'sender_name', 'sender_email', 'sender_phone',
                'sender_user_id', 'receiver_user_id', 'receiver_name', 'receiver_email',
                'property_id', 'property_title', 'subject', 'message_excerpt', 'replies_count',
            ]);

            Enquiry::query()
                ->with(['property:id,title', 'receiver:id,name,email', 'sender:id,name,email'])
                ->withCount('replies')
                ->orderBy('id')
                ->chunkById(500, function ($rows) use ($handle) {
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
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportXlsx(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        AdminAudit::log($request, 'enquiries.export.xlsx', null, null, []);

        $filename = 'enquiries-export-' . now()->format('Ymd-His') . '.xlsx';

        return response()->streamDownload(function () {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray([
                ['ID', 'Created', 'Status', 'Sender', 'Email', 'Receiver', 'Property', 'Subject', 'Replies', 'Message (preview)'],
            ], null, 'A1');

            $row = 2;
            Enquiry::query()
                ->with(['property:id,title', 'receiver:id,name,email'])
                ->withCount('replies')
                ->orderBy('id')
                ->chunkById(500, function ($rows) use ($sheet, &$row) {
                    foreach ($rows as $e) {
                        $sheet->fromArray([
                            [
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
                            ],
                        ], null, 'A' . $row);
                        $row++;
                    }
                });

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
