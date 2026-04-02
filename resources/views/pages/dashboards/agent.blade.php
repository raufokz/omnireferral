@extends('layouts.app')

@section('content')
<section class="page-hero dashboard-page-hero dashboard-page-hero--agent">
    <div class="container page-hero__content">
        <span class="eyebrow">Agent Revenue Hub</span>
        <h1>Own the pipeline from first touch to closed deal</h1>
        <p>Keep your lead flow, package upgrades, VA support, and conversion momentum visible in one premium workspace.</p>
    </div>
</section>

<section class="section dashboard-page dashboard-page--metamorphosis" x-data="{ search: '', filter: 'all' }">
    <div class="container cockpit-grid">
        <!-- Sidebar context (Not explicitly in cockpit but preserved for navigation) -->
        <aside class="cockpit-side" style="grid-row: span 2;">
            <div class="cockpit-table-card mb-8" style="padding: 1.5rem;">
                <div class="flex items-center gap-4 mb-6">
                    <img src="{{ asset($agent?->headshot ?? 'images/realtors/3.png') }}" alt="Agent" style="width: 64px; height: 60px; border-radius: 50%; object-fit: cover;">
                    <div>
                        <span class="eyebrow" style="margin: 0;">Agent Profile</span>
                        <h2 style="font-size: 1.25rem; margin: 0;">{{ $agent?->user?->name ?? 'Partner Agent' }}</h2>
                    </div>
                </div>
                <nav class="dashboard-side-nav" aria-label="Agent dashboard navigation">
                    <a class="is-active" href="{{ route('dashboard.agent') }}">Overview</a>
                    <a href="#leads">Lead Queue</a>
                    <a href="#packages">Active Packages</a>
                    <a href="{{ route('onboarding', 'agent') }}">Profile Settings</a>
                </nav>
            </div>

            <div class="cockpit-table-card" style="padding: 1.5rem; background: var(--color-gateway-brand-bg); color: #fff;">
                <span class="eyebrow" style="color: rgba(255,255,255,0.7);">Ready to Scale?</span>
                <h3 style="color: #fff; margin-bottom: 1rem;">Unlock Premium Flow</h3>
                <p style="color: rgba(255,255,255,0.8); font-size: 0.9rem; margin-bottom: 1.5rem;">Verified leads with higher intent are waiting in the Power and Prime pools.</p>
                <a href="{{ route('pricing') }}" class="button w-full" style="background: var(--color-gateway-accent); border: none;">View Packages</a>
            </div>
        </aside>

        <!-- KPI Row -->
        <div class="cockpit-kpi-row">
            <article class="cockpit-kpi-card">
                <span class="eyebrow">Trust Score</span>
                <strong>{{ $agentStats['score'] }}</strong>
                <p>Speed & Quality Index</p>
            </article>
            <article class="cockpit-kpi-card" style="border-color: var(--color-gateway-accent);">
                <span class="eyebrow">Total Leads</span>
                <strong>{{ $agentStats['leads_received'] }}</strong>
                <p>Lifetime Routed</p>
            </article>
            <article class="cockpit-kpi-card">
                <span class="eyebrow">Response</span>
                <strong>{{ $agentStats['response_rate'] }}</strong>
                <p>First-touch Performance</p>
            </article>
            <article class="cockpit-kpi-card" style="border-color: #10b981;">
                <span class="eyebrow">Rewards</span>
                <strong>{{ $agentStats['rewards'] }}</strong>
                <p>Operational Milestones</p>
            </article>
        </div>

        <main class="cockpit-main" id="leads">
            <!-- Table Controls -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Lead Queue</h2>
                    <p class="text-gray-500">Manage your verified opportunities.</p>
                </div>
                <div class="flex gap-4">
                    <div class="floating-group" style="margin-bottom: 0; min-width: 240px;">
                        <input type="text" x-model="search" placeholder=" " @input.debounce.250ms="/* Search logic */">
                        <label>Filter leads...</label>
                    </div>
                </div>
            </div>

            <div class="cockpit-table-card">
                <table class="cockpit-table">
                    <thead>
                        <tr>
                            <th>Lead Detail</th>
                            <th>Market & Target</th>
                            <th>Handoff Source</th>
                            <th>Current State</th>
                            <th style="width: 60px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $lead)
                            <tr x-show="!search || '{{ strtolower($lead->name) }}'.includes(search.toLowerCase())">
                                <td>
                                    <span class="cockpit-primary-data">{{ $lead->name }}</span>
                                    <span class="cockpit-secondary-data">{{ ucfirst($lead->intent) }} · {{ $lead->phone }}</span>
                                </td>
                                <td>
                                    <span class="cockpit-primary-data">{{ $lead->zip_code }}</span>
                                    <span class="cockpit-secondary-data">{{ $lead->property_type ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="cockpit-primary-data">{{ strtoupper($lead->package_type) }}</span>
                                    <span class="cockpit-secondary-data">Route ID: {{ $lead->id }}</span>
                                </td>
                                <td>
                                    <span class="status-pill status-pill--{{ $lead->status }}" style="margin-bottom: 0.25rem;">{{ ucfirst($lead->status) }}</span>
                                    <span class="cockpit-secondary-data">{{ $lead->status === 'new' ? 'Action Required' : 'In Progress' }}</span>
                                </td>
                                <td>
                                    <div class="relative" x-data="{ open: false }">
                                        <button class="kebab-trigger" @click="open = !open" @click.away="open = false">⋮</button>
                                        <div class="cockpit-table-card absolute right-0 mt-2 w-48 z-10 shadow-xl border border-gray-100" x-show="open" style="display: none; padding: 0.5rem;">
                                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md">View Details</a>
                                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md">Log Contact</a>
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md">Mark as Closed</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="cockpit-empty-state">
                                        <img src="{{ asset('images/illustrations/empty-leads.png') }}" alt="Empty" class="cockpit-empty-illustration">
                                        <h3>Your queue is clear</h3>
                                        <p class="text-gray-500">Your next routed opportunities will appear here automatically.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pipeline Visual preserved but refined -->
            <div class="cockpit-grid mt-12">
                <section class="cockpit-table-card col-span-12 p-8" id="agent-pipeline">
                    <div class="mb-8">
                        <span class="eyebrow">Visual Flow</span>
                        <h3>Handoff Pipeline Velocity</h3>
                    </div>
                    <div class="grid grid-cols-5 gap-8">
                        @foreach($pipeline as $stage)
                            @php($maxPipelineCount = max(1, collect($pipeline)->max('count')))
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blue-900 mb-2">{{ $stage['count'] }}</div>
                                <div class="text-xs uppercase tracking-wider text-gray-500 font-bold mb-4">{{ $stage['label'] }}</div>
                                <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-600 rounded-full" style="width: {{ ($stage['count'] / $maxPipelineCount) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </main>
        </div>
    </div>
</section>
@endsection
