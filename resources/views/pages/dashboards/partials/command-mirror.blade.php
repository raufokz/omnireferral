@props([
    'stats' => [],
    'analyticsTrends' => [],
    'revenueTrend' => collect(),
    'userGrowthTrend' => collect(),
    'enquiryTrend' => collect(),
    'propertyTypeDistribution' => collect(),
    'pipelineHealth' => collect(),
    'teamQueues' => collect(),
    'recentLeads' => collect(),
    'userSubmittedListings' => collect(),
    'recentEnquiries' => collect(),
    'pendingAccounts' => collect(),
    'canViewFullAudit' => false,
    'recentAudit' => collect(),
    'listingSectionEyebrow' => 'Listing Review',
    'listingSectionTitle' => 'User-submitted listings',
    'listingSectionCopy' => 'Includes pending, approved, and rejected uploads from agent and seller workspaces.',
    'listingOwnerLabel' => 'Owner',
])

<section class="dashboard-command-panel workspace-card">
    <div>
        <span class="eyebrow">Executive Snapshot</span>
        <h2>Workspace performance at a glance</h2>
        <p>Revenue signals, user growth, marketplace health, and operational queue pressure are grouped for faster decisions.</p>
    </div>
    <div class="dashboard-chart-filter" data-dashboard-chart-filter data-dashboard-analytics='@json($analyticsTrends ?? [])' aria-label="Dashboard date range">
        <button type="button" data-dashboard-period="daily">Daily</button>
        <button type="button" data-dashboard-period="weekly">Weekly</button>
        <button type="button" data-dashboard-period="monthly" class="is-active">Monthly</button>
        <button type="button" data-dashboard-period="yearly">Yearly</button>
    </div>
</section>

<section class="dashboard-metric-grid">
    <article class="workspace-card workspace-kpi" data-icon="👥" data-trend="↑ {{ number_format($stats['usersActive'] ?? 0) }} active">
        <span>Total Users</span>
        <strong>{{ number_format($stats['usersTotal'] ?? 0) }}</strong>
        <span>{{ number_format($stats['usersSuspended'] ?? 0) }} suspended accounts</span>
    </article>
    <article class="workspace-card workspace-kpi" data-icon="🏠" data-trend="↑ {{ number_format($stats['activeListings'] ?? 0) }} active">
        <span>Total Properties</span>
        <strong>{{ number_format($stats['properties'] ?? 0) }}</strong>
        <span>{{ number_format($stats['featuredListings'] ?? 0) }} featured listings</span>
    </article>
    <article class="workspace-card workspace-kpi" data-icon="✉" data-trend="↑ {{ number_format($stats['contacts'] ?? 0) }} contacts">
        <span>Total Enquiries</span>
        <strong>{{ number_format($stats['enquiries'] ?? 0) }}</strong>
        <span>Listing conversations and inbound interest</span>
    </article>
    <article class="workspace-card workspace-kpi workspace-kpi--warm" data-icon="$" data-trend="↑ Revenue signal">
        <span>Total Revenue</span>
        <strong>${{ number_format(($stats['leadPipelineValue'] ?? 0) + ($stats['mrrEstimate'] ?? 0)) }}</strong>
        <span>OmniReferral pipeline + active plan MRR</span>
    </article>
    <article class="workspace-card workspace-kpi" data-icon="📌" data-trend="Review {{ number_format($stats['pendingListings'] ?? 0) }}">
        <span>Active Listings</span>
        <strong>{{ number_format($stats['activeListings'] ?? 0) }}</strong>
        <span>{{ number_format($stats['pendingListings'] ?? 0) }} pending moderation</span>
    </article>
    <article class="workspace-card workspace-kpi workspace-kpi--violet" data-icon="★" data-trend="Featured mix">
        <span>Featured Listings</span>
        <strong>{{ number_format($stats['featuredListings'] ?? 0) }}</strong>
        <span>{{ number_format($stats['propertyFavorites'] ?? 0) }} saved listing signals</span>
    </article>
</section>

<section class="dashboard-analytics-grid">
    <article class="workspace-card dashboard-chart-card">
        <span class="eyebrow">Analytics</span>
        <h2>Revenue trends</h2>
        <div class="dashboard-line-chart" data-dashboard-chart="revenue" role="img" aria-label="Revenue trend by month">
            @forelse($revenueTrend as $point)
                <span data-chart-point style="--chart-value: {{ max(6, $point['percent']) }}%;" title="{{ $point['label'] }}: ${{ number_format($point['amount']) }}"></span>
            @empty
                <div class="workspace-empty">No revenue trend yet.</div>
            @endforelse
        </div>
        <div class="dashboard-chart-legend" data-dashboard-legend="revenue">
            @forelse($revenueTrend as $point)
                <span>{{ $point['label'] }} ${{ number_format($point['amount']) }}</span>
            @empty
            @endforelse
        </div>
    </article>

    <article class="workspace-card dashboard-chart-card">
        <span class="eyebrow">Analytics</span>
        <h2>User growth</h2>
        <div class="admin-chart admin-chart--bars" data-dashboard-chart="users" role="img" aria-label="Users created per month">
            @forelse($userGrowthTrend as $point)
                <div class="admin-chart__col" data-chart-column>
                    <div class="admin-chart__bar" style="height: {{ max(8, $point['percent']) }}px;"></div>
                    <span class="admin-chart__label">{{ $point['label'] }}</span>
                    <span class="admin-chart__value">{{ number_format($point['count']) }}</span>
                </div>
            @empty
                <div class="workspace-empty">No user growth trend yet.</div>
            @endforelse
        </div>
    </article>

    <article class="workspace-card dashboard-chart-card">
        <span class="eyebrow">Analytics</span>
        <h2>Enquiry activity</h2>
        <div class="admin-chart admin-chart--bars admin-chart--teal" data-dashboard-chart="enquiries" role="img" aria-label="Enquiries per month">
            @forelse($enquiryTrend as $point)
                <div class="admin-chart__col" data-chart-column>
                    <div class="admin-chart__bar" style="height: {{ max(8, $point['percent']) }}px;"></div>
                    <span class="admin-chart__label">{{ $point['label'] }}</span>
                    <span class="admin-chart__value">{{ number_format($point['count']) }}</span>
                </div>
            @empty
                <div class="workspace-empty">No enquiry trend yet.</div>
            @endforelse
        </div>
    </article>

    <article class="workspace-card dashboard-chart-card">
        <span class="eyebrow">Analytics</span>
        <h2>Property performance</h2>
        <div class="dashboard-donut-list">
            @forelse($propertyTypeDistribution as $slice)
                <div>
                    <span>{{ \Illuminate\Support\Str::headline($slice['label']) }}</span>
                    <strong>{{ number_format($slice['count']) }}</strong>
                    <i style="width: {{ max(6, $slice['percent']) }}%;"></i>
                </div>
            @empty
                <div class="workspace-empty">No property distribution yet.</div>
            @endforelse
        </div>
    </article>
</section>

<section class="workspace-grid workspace-grid--3">
    <article class="workspace-card workspace-kpi" data-icon="📈" data-trend="Lead pipeline">
        <span>Lead Pipeline Value</span>
        <strong>${{ number_format($stats['leadPipelineValue'] ?? 0) }}</strong>
        <span>All visible leads by mapped package price</span>
    </article>
    <article class="workspace-card workspace-kpi" data-icon="⏳" data-trend="Action required">
        <span>Pending Sign-ups</span>
        <strong>{{ number_format($stats['pendingAccounts'] ?? 0) }}</strong>
        <span>Workspace registrations awaiting activation</span>
    </article>
    <article class="workspace-card workspace-kpi" data-icon="🏷" data-trend="Latest below">
        <span>User-submitted Listings</span>
        <strong>{{ number_format($stats['userSubmittedListingsTotal'] ?? 0) }}</strong>
        <span>Role-scoped uploads across moderation states</span>
    </article>
</section>

@if($canViewFullAudit && $recentAudit->isNotEmpty())
    <section class="workspace-card">
        <div class="workspace-actions" style="justify-content: space-between; margin-bottom: 0.65rem;">
            <div>
                <span class="eyebrow">Audit &amp; Notifications</span>
                <h2>Recent administrative actions</h2>
            </div>
            <a href="{{ route('admin.activity.index') }}" class="button button--ghost-blue">Full log</a>
        </div>
        <ul class="workspace-list">
            @foreach($recentAudit as $log)
                <li>
                    <strong>{{ $log->action }}</strong>
                    <small>{{ $log->actor?->name ?? 'System' }} · {{ $log->created_at?->diffForHumans() }} · {{ $log->ip_address }}</small>
                    @if($log->subject_type)
                        <small>Subject: {{ $log->subject_type }} #{{ $log->subject_id }}</small>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>
@endif

<section class="workspace-grid workspace-grid--2">
    <article class="workspace-card">
        <span class="eyebrow">Lead Pipeline</span>
        <h2>Stage Distribution</h2>
        <div class="workspace-stack">
            @forelse($pipelineHealth as $stage)
                <div>
                    <div class="workspace-actions" style="justify-content: space-between;">
                        <strong>{{ $stage['label'] }}</strong>
                        <small>{{ number_format($stage['count']) }}</small>
                    </div>
                    <div class="dashboard-progress-track">
                        <div style="width: {{ $stage['percent'] }}%;"></div>
                    </div>
                </div>
            @empty
                <div class="workspace-empty">No pipeline stages yet.</div>
            @endforelse
        </div>
    </article>

    <article class="workspace-card">
        <span class="eyebrow">Team Queues</span>
        <h2>Operational Pressure Points</h2>
        <ul class="workspace-list">
            @forelse($teamQueues as $queue)
                <li>
                    <strong>{{ $queue['team'] }} · {{ number_format($queue['count']) }}</strong>
                    <small>{{ $queue['copy'] }}</small>
                </li>
            @empty
                <li>
                    <strong>No queue pressure yet</strong>
                    <small>Operational queues will appear as workspace activity grows.</small>
                </li>
            @endforelse
        </ul>
    </article>
</section>

@if($pendingAccounts->isNotEmpty())
    <section class="workspace-card">
        <span class="eyebrow">Account Activation</span>
        <h2>Pending registrations</h2>
        <ul class="workspace-list">
            @foreach($pendingAccounts as $account)
                <li>
                    <strong>{{ $account->name }}</strong>
                    <small>{{ ucfirst($account->role) }} · {{ $account->email }} · {{ $account->created_at?->format('M j, Y g:i A') }}</small>
                    <div class="workspace-actions" style="margin-top: 0.6rem;">
                        <form method="POST" action="{{ route('admin.users.review', $account) }}">
                            @csrf
                            <input type="hidden" name="decision" value="approve">
                            <button type="submit" class="button">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.review', $account) }}">
                            @csrf
                            <input type="hidden" name="decision" value="suspend">
                            <button type="submit" class="button button--ghost-blue">Suspend</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
@endif

<section class="workspace-card">
    <div class="workspace-actions" style="justify-content: space-between; margin-bottom: 0.7rem;">
        <div>
            <span class="eyebrow">Latest Leads</span>
            <h2>Recent registry records</h2>
        </div>
        <a href="{{ match(auth()->user()->role ?? '') { 'seller' => route('dashboard.seller.requests'), 'agent' => route('agent.leads.index'), default => route('dashboard.buyer.requests') } }}" class="button button--ghost-blue">Open full registry</a>
    </div>

    <div class="workspace-table-wrap">
        <table class="workspace-table">
            <thead>
                <tr>
                    <th>Lead</th>
                    <th>Intent</th>
                    <th>Package</th>
                    <th>Status</th>
                    <th>Assigned</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentLeads as $lead)
                    <tr>
                        <td data-label="Lead">
                            <strong>{{ $lead->name }}</strong>
                            <div class="workspace-property__meta">{{ $lead->email ?: 'No email' }}</div>
                        </td>
                        <td data-label="Intent">
                            <strong>{{ ucfirst($lead->intent ?: 'Unknown') }}</strong>
                            <div class="workspace-property__meta">{{ $lead->zip_code ?: 'No ZIP' }}</div>
                        </td>
                        <td data-label="Package">{{ strtoupper($lead->package_type ?: 'N/A') }}</td>
                        <td data-label="Status">
                            <span class="status-pill status-pill--{{ \Illuminate\Support\Str::slug((string) $lead->status, '_') }}">
                                {{ $lead->statusLabel() }}
                            </span>
                        </td>
                        <td data-label="Assigned">{{ $lead->assignedAgent?->name ?? 'Unassigned' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="workspace-empty">No recent leads yet.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="workspace-card">
    <span class="eyebrow">{{ $listingSectionEyebrow }}</span>
    <h2>{{ $listingSectionTitle }}</h2>
    <p style="margin: 0 0 0.75rem; color: #64748b; font-size: 0.9rem;">{{ $listingSectionCopy }}</p>
    <div class="workspace-table-wrap">
        <table class="workspace-table">
            <thead>
                <tr>
                    <th>Listing</th>
                    <th>Source</th>
                    <th>Moderation</th>
                    <th>{{ $listingOwnerLabel }}</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($userSubmittedListings as $property)
                    @php
                        $canEditProperty = auth()->check() && ((int) $property->owner_user_id === (int) auth()->id() || optional($property->realtorProfile?->user)->id === (int) auth()->id());
                        $canReviewProperty = auth()->check() && auth()->user()->isAdmin();
                    @endphp
                    <tr class="{{ (int) $property->price >= 750000 ? 'is-priority-row' : '' }}">
                        <td data-label="Listing">
                            <strong>{{ $property->title }}</strong>
                            <div class="workspace-property__meta">{{ $property->location }}</div>
                        </td>
                        <td data-label="Source">{{ $property->source }}</td>
                        <td data-label="Moderation">
                            <span class="status-pill status-pill--{{ \Illuminate\Support\Str::slug($property->approval_status, '_') }}">
                                {{ $property->approvalStatusLabel() }}
                            </span>
                        </td>
                        <td data-label="{{ $listingOwnerLabel }}">
                            {{ $property->owner?->name ?? optional($property->realtorProfile?->user)->name ?? '—' }}
                        </td>
                        <td data-label="Actions">
                            <div class="workspace-actions" style="flex-wrap: wrap; gap: 0.35rem;">
                                <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue">Preview</a>
                                @if($canEditProperty)
                                    <a href="{{ route('properties.edit', $property) }}" class="button button--ghost-blue">Edit</a>
                                @endif
                                @if($canReviewProperty && $property->approval_status === \App\Models\Property::APPROVAL_PENDING)
                                    <form method="POST" action="{{ route('admin.properties.review', $property) }}" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="decision" value="approve">
                                        <button type="submit" class="button">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.properties.review', $property) }}" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="decision" value="reject">
                                        <button type="submit" class="button button--ghost-blue">Reject</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="workspace-empty">No user-submitted listings yet.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="workspace-card">
    <div class="workspace-actions" style="justify-content: space-between; margin-bottom: 0.65rem;">
        <div>
            <span class="eyebrow">Listing Inquiries</span>
            <h2>Recent enquiries</h2>
        </div>
        <a href="{{ match(auth()->user()->role ?? '') { 'agent' => route('agent.messages.index'), default => route('dashboard.enquiries.index') } }}" class="button button--ghost-blue">All enquiries</a>
    </div>
    <ul class="workspace-list">
        @forelse($recentEnquiries as $enq)
            @php
                $enquiryTitle = $enq->property?->title ? \Illuminate\Support\Str::limit($enq->property->title, 52) : ($enq->subject ?: 'Listing enquiry');
                $enquiryMeta = $enq->sender_name ?? $enq->name ?? 'Contact';
                $enquiryStatus = $enq->status ?? $enq->message_status ?? 'new';
                $enquiryReceiver = $enq->receiver?->name ?? $enq->recipient?->name ?? '—';
                $enquiryRoute = $enq instanceof \App\Models\Contact ? route('agent.messages.index') : route('dashboard.enquiries.show', $enq);
            @endphp
            <li>
                <strong>{{ $enquiryTitle }}</strong>
                <small>{{ $enquiryMeta }} · {{ ucfirst($enquiryStatus) }} · {{ $enq->created_at?->format('M j, Y g:i A') }}</small>
                <small>Listed by {{ $enquiryReceiver }}</small>
                <div class="workspace-actions" style="margin-top:0.45rem;">
                    <a href="{{ $enquiryRoute }}" class="button button--ghost-blue">Open thread</a>
                </div>
            </li>
        @empty
            <li>
                <strong>No listing enquiries yet</strong>
                <small>New property inquiries will appear here with conversation links.</small>
            </li>
        @endforelse
    </ul>
</section>
