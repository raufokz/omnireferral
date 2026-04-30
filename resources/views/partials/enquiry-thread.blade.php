<div class="enquiry-thread-wrap">
    <div class="enquiry-thread" id="enquiry-thread" role="log" aria-live="polite" aria-relevant="additions">
        <div class="enquiry-thread__msg enquiry-thread__msg--visitor">
            <div class="enquiry-thread__bubble">
                <div class="enquiry-thread__meta">
                    <strong>{{ $enquiry->sender_name }}</strong>
                    <span>{{ $enquiry->sender_email }}</span>
                    @if($enquiry->sender_phone)
                        <span>· {{ $enquiry->sender_phone }}</span>
                    @endif
                    <time datetime="{{ $enquiry->created_at?->toIso8601String() }}">{{ $enquiry->created_at?->format('M j, Y g:i A') }}</time>
                </div>
                @if($enquiry->subject)
                    <p class="enquiry-thread__subject">{{ $enquiry->subject }}</p>
                @endif
                <div class="enquiry-thread__body">{!! nl2br(e($enquiry->message)) !!}</div>
            </div>
        </div>

        @foreach($enquiry->replies as $reply)
            @php
                $modifier = 'enquiry-thread__msg--other';
                if ($reply->sender_user_id) {
                    $sender = $reply->sender;
                    if ($sender && $sender->isStaff()) {
                        $modifier = 'enquiry-thread__msg--staff';
                    } elseif ((int) $reply->sender_user_id === (int) $enquiry->receiver_user_id) {
                        $modifier = 'enquiry-thread__msg--owner';
                    } elseif ((int) $reply->sender_user_id === (int) $enquiry->sender_user_id) {
                        $modifier = 'enquiry-thread__msg--visitor';
                    }
                }
            @endphp
            <div class="enquiry-thread__msg {{ $modifier }}">
                <div class="enquiry-thread__bubble">
                    <div class="enquiry-thread__meta">
                        <strong>{{ $reply->sender_display ?: 'Participant' }}</strong>
                        <time datetime="{{ $reply->created_at?->toIso8601String() }}">{{ $reply->created_at?->format('M j, Y g:i A') }}</time>
                    </div>
                    <div class="enquiry-thread__body">{!! nl2br(e($reply->message)) !!}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="enquiry-thread__actions">
        @if(!empty($statusUrl) && ($statusMode ?? null) === 'admin')
            <form method="POST" action="{{ $statusUrl }}" class="enquiry-thread__status-form">
                @csrf
                @method('PATCH')
                <label class="workspace-field" style="margin:0;">
                    <span>Enquiry status</span>
                    <div class="enquiry-thread__status-row">
                        <select name="status">
                            <option value="pending" {{ $enquiry->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="replied" {{ $enquiry->status === 'replied' ? 'selected' : '' }}>Replied</option>
                            <option value="closed" {{ $enquiry->status === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                        <button type="submit" class="button">Update status</button>
                    </div>
                </label>
            </form>
        @endif

        @if(!empty($statusUrl) && ($statusMode ?? null) === 'close' && $enquiry->status !== Enquiry::STATUS_CLOSED)
            <form method="POST" action="{{ $statusUrl }}" class="enquiry-thread__close-form" onsubmit="return confirm('Close this conversation? You can still read history later.');">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="closed">
                <button type="submit" class="button button--ghost-blue">Close conversation</button>
            </form>
        @endif

        @if(!empty($replyUrl) && ($canReply ?? false))
            <form method="POST" action="{{ $replyUrl }}" class="enquiry-thread__reply-form">
                @csrf
                <label class="workspace-field workspace-field--full" style="margin:0;">
                    <span>Your reply</span>
                    <textarea name="message" rows="4" required placeholder="Write a message…"></textarea>
                </label>
                <div class="workspace-actions" style="margin-top:0.65rem;">
                    <button type="submit" class="button">Send reply</button>
                </div>
            </form>
        @elseif($enquiry->status === 'closed')
            <p class="enquiry-thread__closed-note">This conversation is closed. Replies are disabled.</p>
        @endif
    </div>
</div>
