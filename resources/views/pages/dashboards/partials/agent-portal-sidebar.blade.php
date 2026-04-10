@php
    $agentHeadshot = $agentProfile?->headshot;
    $agentImage = $agentHeadshot
        ? (\Illuminate\Support\Str::startsWith($agentHeadshot, ['http://', 'https://', '/storage/', 'storage/']) ? $agentHeadshot : asset($agentHeadshot))
        : ($agentUser?->avatar ? asset('storage/' . ltrim($agentUser->avatar, '/')) : asset('images/realtors/3.png'));

    $agentLocation = collect([
        $agentProfile?->city ?: $agentUser?->city,
        $agentProfile?->state ?: $agentUser?->state,
        $agentProfile?->zip_code ?: $agentUser?->zip_code,
    ])->filter()->implode(', ');
@endphp

<aside class="agent-portal-sidebar">
    <div class="cockpit-table-card agent-portal-profile-card">
        <div class="agent-portal-profile-card__header">
            <img src="{{ $agentImage }}" alt="{{ $agentUser?->name ?? 'Agent' }} profile image" loading="lazy">
            <div>
                <span class="eyebrow">Agent Portal</span>
                <h2>{{ $agentUser?->name ?? 'Partner Agent' }}</h2>
                <p>{{ $agentProfile?->brokerage_name ?: 'Brokerage information pending' }}</p>
            </div>
        </div>

        <div class="agent-portal-profile-card__chips">
            <span class="status-pill status-pill--assigned">Agent</span>
            <span class="status-pill status-pill--qualified">{{ $activePlan?->name ?: 'No active package' }}</span>
            @if($agentLocation)
                <span class="status-pill status-pill--new">{{ $agentLocation }}</span>
            @endif
        </div>

        <dl class="agent-portal-profile-card__meta">
            <div>
                <dt>Email</dt>
                <dd>{{ $agentUser?->email ?? 'Not available' }}</dd>
            </div>
            <div>
                <dt>Phone</dt>
                <dd>{{ $agentUser?->phone ?: 'Pending update' }}</dd>
            </div>
            <div>
                <dt>License</dt>
                <dd>{{ $agentProfile?->license_number ?: 'Pending completion' }}</dd>
            </div>
            <div>
                <dt>Service Area</dt>
                <dd>{{ $agentLocation ?: 'Pending completion' }}</dd>
            </div>
        </dl>

        <div class="agent-portal-profile-card__actions">
            <a href="{{ route('agents.show', $agentProfile) }}" class="button button--ghost-blue">View Public Profile</a>
            <a href="{{ route('agent.profile') }}" class="button button--blue">Edit Profile</a>
        </div>
    </div>

    <div class="cockpit-table-card agent-portal-nav-card">
        <span class="eyebrow">Workspace</span>
        <nav class="dashboard-side-nav" aria-label="Agent workspace navigation">
            <a class="{{ $activeAgentPage === 'overview' ? 'is-active' : '' }}" href="{{ route('dashboard.agent') }}">Overview</a>
            <a class="{{ $activeAgentPage === 'profile' ? 'is-active' : '' }}" href="{{ route('agent.profile') }}">Profile</a>
            <a class="{{ $activeAgentPage === 'leads' ? 'is-active' : '' }}" href="{{ route('agent.leads.index') }}">Leads</a>
            <a class="{{ $activeAgentPage === 'listings' ? 'is-active' : '' }}" href="{{ route('agent.listings.index') }}">Listings</a>
            <a class="{{ $activeAgentPage === 'messages' ? 'is-active' : '' }}" href="{{ route('agent.messages.index') }}">Messages</a>
        </nav>
    </div>

    <div class="cockpit-table-card agent-portal-access-card">
        <span class="eyebrow">Package Access</span>
        <h3>{{ $activePlan?->name ?: 'No active lead package' }}</h3>
        <p>{{ $listingLimitLabel }}</p>

        <div class="agent-portal-access-card__stats">
            <div>
                <span>Active listings</span>
                <strong>{{ $activeListingCount }}</strong>
            </div>
            <div>
                <span>Slots left</span>
                <strong>{{ $remainingListingSlots }}</strong>
            </div>
            <div>
                <span>Unread messages</span>
                <strong>{{ $unreadMessagesCount }}</strong>
            </div>
            <div>
                <span>Response rate</span>
                <strong>{{ $agentStats['response_rate'] }}</strong>
            </div>
        </div>

        <a href="{{ route('pricing') }}" class="button button--orange">Compare Packages</a>
    </div>
</aside>
