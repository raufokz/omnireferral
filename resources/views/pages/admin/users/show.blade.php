@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', $record->publicDisplayName())
@section('dashboard_description', 'Complete account record, subscription context, and recent marketplace activity.')

@section('dashboard_actions')
    <a href="{{ route('admin.users.index') }}" class="button button--ghost-blue">All users</a>
    @if($canEdit)
        <a href="{{ route('admin.users.edit', $record) }}" class="button">Edit user</a>
    @endif
@endsection

@section('content')
<div class="workspace-stack admin-user-show">
    @if(session('success'))
        <div class="workspace-card admin-user-show__flash admin-user-show__flash--ok">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="workspace-card admin-user-show__flash admin-user-show__flash--err">{{ session('error') }}</div>
    @endif

    <section class="workspace-card admin-user-show__hero">
        <div class="admin-user-show__hero-grid">
            <div class="admin-user-show__avatar">
                @if($record->profilePhotoPublicUrl())
                    <img src="{{ $record->profilePhotoPublicUrl() }}" alt="" width="120" height="120" loading="lazy">
                @else
                    <span class="listed-by-placeholder listed-by-placeholder--profile-hero">{{ $record->profileInitials() }}</span>
                @endif
            </div>
            <div class="admin-user-show__hero-main">
                <span class="eyebrow">User #{{ $record->id }}</span>
                <h2>{{ $record->publicDisplayName() }}</h2>
                <p class="admin-user-show__muted">{{ $record->email }}</p>
                <div class="admin-user-show__pills">
                    <span class="status-pill status-pill--{{ \Illuminate\Support\Str::slug($record->status, '_') }}">{{ ucfirst($record->status) }}</span>
                    <span class="admin-user-show__pill">{{ ucfirst($record->role) }}</span>
                    @if($record->staff_team)
                        <span class="admin-user-show__pill">{{ strtoupper($record->staff_team) }} team</span>
                    @endif
                </div>
            </div>
            <div class="admin-user-show__hero-actions">
                @if($canEdit)
                    <a href="{{ route('admin.users.edit', $record) }}" class="button">Edit profile</a>
                @endif
                @if($canSuspend && $record->status !== 'suspended')
                    <form method="POST" action="{{ route('admin.users.destroy', $record) }}" onsubmit="return confirm('Deactivate this user? They will not be able to sign in.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="button button--ghost-blue">Deactivate</button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <div class="workspace-grid workspace-grid--2 admin-user-show__grid">
        <section class="workspace-card admin-user-show__card">
            <span class="eyebrow">Identity</span>
            <h3>Legal &amp; display</h3>
            <dl class="admin-user-show__dl">
                <div><dt>Full name</dt><dd>{{ $record->name }}</dd></div>
                <div><dt>Display name</dt><dd>{{ $record->display_name ?: '—' }}</dd></div>
                <div><dt>Affiliate code</dt><dd>{{ $record->affiliate_code ?: '—' }}</dd></div>
                <div><dt>Joined</dt><dd>{{ $record->created_at?->format('M j, Y g:i a') }}</dd></div>
                <div><dt>Updated</dt><dd>{{ $record->updated_at?->format('M j, Y g:i a') }}</dd></div>
                <div><dt>Last synced</dt><dd>{{ $record->last_synced_at?->format('M j, Y g:i a') ?: '—' }}</dd></div>
            </dl>
        </section>

        <section class="workspace-card admin-user-show__card">
            <span class="eyebrow">Contact</span>
            <h3>Reachability</h3>
            <dl class="admin-user-show__dl">
                <div><dt>Email</dt><dd>{{ $record->email }}</dd></div>
                <div><dt>Verified</dt><dd>{{ $record->email_verified_at ? $record->email_verified_at->format('M j, Y') : 'Not verified' }}</dd></div>
                <div><dt>Phone</dt><dd>{{ $record->phone ?: '—' }}</dd></div>
                <div><dt>Facebook</dt><dd>@if($record->social_facebook_url)<a href="{{ $record->social_facebook_url }}" target="_blank" rel="noopener">Open</a>@else — @endif</dd></div>
                <div><dt>LinkedIn</dt><dd>@if($record->social_linkedin_url)<a href="{{ $record->social_linkedin_url }}" target="_blank" rel="noopener">Open</a>@else — @endif</dd></div>
            </dl>
        </section>

        <section class="workspace-card admin-user-show__card">
            <span class="eyebrow">Address</span>
            <h3>Mailing</h3>
            <dl class="admin-user-show__dl">
                <div><dt>Line 1</dt><dd>{{ $record->address_line_1 ?: '—' }}</dd></div>
                <div><dt>Line 2</dt><dd>{{ $record->address_line_2 ?: '—' }}</dd></div>
                <div><dt>City / State / ZIP</dt><dd>{{ collect([$record->city, $record->state, $record->zip_code])->filter()->implode(', ') ?: '—' }}</dd></div>
            </dl>
        </section>

        <section class="workspace-card admin-user-show__card">
            <span class="eyebrow">Business</span>
            <h3>Referrals &amp; plan</h3>
            <dl class="admin-user-show__dl">
                <div><dt>Referred by</dt><dd>
                    @if($record->referrer)
                        <a href="{{ route('admin.users.show', $record->referrer) }}">{{ $record->referrer->email }}</a>
                    @else
                        —
                    @endif
                </dd></div>
                <div><dt>Referrals registered</dt><dd>{{ number_format($record->referrals_count) }}</dd></div>
                <div><dt>Current plan</dt><dd>{{ $record->currentPlan?->name ?? '—' }}</dd></div>
                <div><dt>Stripe customer</dt><dd>{{ $record->stripe_customer_id ?: '—' }}</dd></div>
                <div><dt>GHL contact</dt><dd>{{ $record->ghl_contact_id ?: '—' }}</dd></div>
            </dl>
        </section>

        <section class="workspace-card admin-user-show__card">
            <span class="eyebrow">Security</span>
            <h3>Account protection</h3>
            <dl class="admin-user-show__dl">
                <div><dt>Password set</dt><dd>{{ $record->password_set_at?->format('M j, Y') ?: '—' }}</dd></div>
                <div><dt>Must reset password</dt><dd>{{ $record->must_reset_password ? 'Yes' : 'No' }}</dd></div>
                <div><dt>Two-factor preference</dt><dd>{{ $record->two_factor_enabled ? 'Enabled' : 'Off' }}</dd></div>
                <div><dt>Notify (product)</dt><dd>{{ $record->notify_email ? 'On' : 'Off' }}</dd></div>
                <div><dt>Notify (marketing)</dt><dd>{{ $record->notify_marketing ? 'On' : 'Off' }}</dd></div>
            </dl>
        </section>

        <section class="workspace-card admin-user-show__card">
            <span class="eyebrow">Roles</span>
            <h3>Workspace access</h3>
            <dl class="admin-user-show__dl">
                <div><dt>Role</dt><dd>{{ ucfirst($record->role) }}</dd></div>
                <div><dt>Staff team</dt><dd>{{ $record->staff_team ? strtoupper($record->staff_team) : '—' }}</dd></div>
                <div><dt>Onboarding</dt><dd>{{ $record->onboarding_completed_at?->format('M j, Y') ?: 'Incomplete' }}</dd></div>
                @if($record->realtorProfile)
                    <div><dt>Agent profile</dt><dd>
                        <a href="{{ route('admin.agent-profiles.show', $record->realtorProfile) }}">Review in admin</a>
                        @if($record->realtorProfile->isPublicVisible())
                            · <a href="{{ route('agents.profile', $record->realtorProfile) }}" target="_blank" rel="noopener">Public profile</a>
                        @else
                            · <span class="text-muted">Not public yet</span>
                        @endif
                    </dd></div>
                @endif
            </dl>
        </section>
    </div>

    <section class="workspace-card">
        <span class="eyebrow">Activity</span>
        <h3>Counts</h3>
        <div class="workspace-grid workspace-grid--4 admin-user-show__counts">
            <article class="workspace-kpi workspace-card">
                <span>Owned listings</span>
                <strong>{{ number_format($record->owned_properties_count) }}</strong>
            </article>
            <article class="workspace-kpi workspace-card">
                <span>Listed-by properties</span>
                <strong>{{ number_format($record->listed_properties_count) }}</strong>
            </article>
            <article class="workspace-kpi workspace-card">
                <span>Inquiries received</span>
                <strong>{{ number_format($record->enquiries_received_count) }}</strong>
            </article>
            <article class="workspace-kpi workspace-card">
                <span>Inquiries sent</span>
                <strong>{{ number_format($record->enquiries_sent_count) }}</strong>
            </article>
            <article class="workspace-kpi workspace-card">
                <span>Contacts (inbox)</span>
                <strong>{{ number_format($record->received_contacts_count) }}</strong>
            </article>
        </div>

        <div class="admin-user-show__tables">
            <div>
                <h4 class="admin-user-show__table-title">Recent enquiries received</h4>
                <div class="workspace-table-wrap">
                    <table class="workspace-table">
                        <thead><tr><th>When</th><th>Property</th><th>From</th></tr></thead>
                        <tbody>
                            @forelse($recentEnquiriesIn as $enq)
                                <tr>
                                    <td>{{ $enq->created_at?->diffForHumans() }}</td>
                                    <td>@if($enq->property)<a href="{{ route('properties.show', $enq->property) }}">{{ Str::limit($enq->property->title, 40) }}</a>@else — @endif</td>
                                    <td>{{ $enq->sender_name ?: optional($enq->sender)->email ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3"><div class="workspace-empty">No enquiries yet.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <h4 class="admin-user-show__table-title">Recent listings (Listed By)</h4>
                <div class="workspace-table-wrap">
                    <table class="workspace-table">
                        <thead><tr><th>Title</th><th>Status</th><th>Created</th></tr></thead>
                        <tbody>
                            @forelse($recentListings as $prop)
                                <tr>
                                    <td><a href="{{ route('properties.show', $prop) }}">{{ Str::limit($prop->title, 48) }}</a></td>
                                    <td>{{ $prop->status }}</td>
                                    <td>{{ $prop->created_at?->format('M j, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3"><div class="workspace-empty">No listings attributed.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
