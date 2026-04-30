<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Services\EnquiryReplyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $user->hasAnyRole(['buyer', 'seller', 'agent']), 403);

        $filters = [
            'status' => (string) $request->query('status', ''),
            'search' => trim((string) $request->query('search', '')),
        ];

        $query = Enquiry::query()
            ->forParticipant($user)
            ->with(['property:id,title,slug', 'receiver:id,name', 'sender:id,name'])
            ->withCount('replies');

        if ($filters['status'] !== '' && in_array($filters['status'], [Enquiry::STATUS_PENDING, Enquiry::STATUS_REPLIED, Enquiry::STATUS_CLOSED], true)) {
            $query->where('status', $filters['status']);
        }

        if ($filters['search'] !== '') {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('sender_name', 'like', '%' . $s . '%')
                    ->orWhere('sender_email', 'like', '%' . $s . '%')
                    ->orWhereHas('property', fn ($pq) => $pq->where('title', 'like', '%' . $s . '%'));
            });
        }

        $enquiries = $query->latest()->paginate(20)->withQueryString();

        return view('pages.dashboard.enquiries.index', [
            'enquiries' => $enquiries,
            'filters' => $filters,
            'meta' => [
                'title' => 'My enquiries | OmniReferral',
                'description' => 'Listing conversations you started or received as the property owner.',
            ],
        ]);
    }

    public function show(Request $request, Enquiry $enquiry): View
    {
        $user = $request->user();
        $this->authorizeParticipant($user, $enquiry);

        $enquiry->load([
            'property',
            'receiver',
            'sender',
            'replies.sender',
        ]);

        $canReply = $this->canReply($user, $enquiry);
        $canClose = $this->canClose($user, $enquiry);

        return view('pages.dashboard.enquiries.show', [
            'enquiry' => $enquiry,
            'canReply' => $canReply,
            'canClose' => $canClose,
            'replyUrl' => route('dashboard.enquiries.replies.store', $enquiry),
            'statusUrl' => route('dashboard.enquiries.status', $enquiry),
            'meta' => [
                'title' => 'Enquiry · ' . ($enquiry->property?->title ?? 'Listing') . ' | OmniReferral',
                'description' => 'Message thread for your property enquiry.',
            ],
        ]);
    }

    public function storeReply(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeParticipant($user, $enquiry);
        abort_unless($this->canReply($user, $enquiry), 403);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:20000'],
        ]);

        EnquiryReplyService::store($enquiry, $user, $validated['message']);

        return back()->with('success', 'Reply sent.');
    }

    public function updateStatus(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeParticipant($user, $enquiry);

        $validated = $request->validate([
            'status' => ['required', Rule::in([Enquiry::STATUS_CLOSED])],
        ]);

        abort_unless($this->canClose($user, $enquiry), 403);

        $enquiry->update(['status' => $validated['status']]);
        $enquiry->syncLinkedContact();

        return back()->with('success', 'Conversation marked as closed.');
    }

    private function authorizeParticipant(?\App\Models\User $user, Enquiry $enquiry): void
    {
        abort_unless($user, 403);
        abort_unless(
            (int) $user->id === (int) $enquiry->receiver_user_id
                || ($enquiry->sender_user_id && (int) $user->id === (int) $enquiry->sender_user_id),
            403
        );
    }

    private function canReply(?\App\Models\User $user, Enquiry $enquiry): bool
    {
        if (! $user) {
            return false;
        }

        if ($enquiry->status === Enquiry::STATUS_CLOSED) {
            return false;
        }

        if ((int) $user->id === (int) $enquiry->receiver_user_id) {
            return true;
        }

        return $enquiry->sender_user_id && (int) $user->id === (int) $enquiry->sender_user_id;
    }

    private function canClose(?\App\Models\User $user, Enquiry $enquiry): bool
    {
        if (! $user || $enquiry->status === Enquiry::STATUS_CLOSED) {
            return false;
        }

        return (int) $user->id === (int) $enquiry->receiver_user_id
            || ($enquiry->sender_user_id && (int) $user->id === (int) $enquiry->sender_user_id);
    }
}
