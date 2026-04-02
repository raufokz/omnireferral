@extends('layouts.app')

@section('content')
<section class="page-hero dashboard-page-hero dashboard-page-hero--affiliate">
    <div class="container page-hero__content">
        <span class="eyebrow">Affiliate Workspace</span>
        <h1>Grow your network and earn commissions</h1>
        <p>Share OmniReferral with your colleagues. Track clicks, package purchases, and upcoming payouts in one unified hub.</p>
    </div>
</section>

<section class="section dashboard-page dashboard-page--metamorphosis" x-data="{ search: '', filter: 'all' }">
    <div class="container cockpit-grid">
        <!-- Sidebar Navigation (Preserved context) -->
        <aside class="cockpit-side" style="grid-row: span 2;">
            <div class="cockpit-table-card mb-8" style="padding: 1.5rem;">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-800 font-bold">A</div>
                    <div>
                        <span class="eyebrow" style="margin: 0;">Affiliate Profile</span>
                        <h2 style="font-size: 1.25rem; margin: 0;">{{ Auth::user()->name }}</h2>
                    </div>
                </div>
                <nav class="dashboard-side-nav" aria-label="Affiliate dashboard navigation">
                    <a class="is-active" href="{{ route('dashboard.affiliate') }}">Overview</a>
                    <a href="#link">My Link</a>
                    <a href="#referrals">Referral Log</a>
                    <a href="{{ route('dashboard') }}">Main Workspace</a>
                </nav>
            </div>

            <div class="cockpit-table-card mb-8" id="link" style="padding: 1.5rem;">
                <span class="eyebrow">Your Unique Link</span>
                <h3 class="mb-4">Start referring</h3>
                <div class="bg-gray-50 p-4 rounded-xl border border-dashed border-indigo-200">
                    <input type="text" class="text-xs font-mono w-full bg-transparent border-none focus:ring-0 text-indigo-900" readonly value="{{ url('/?ref=' . $profile->referral_code) }}" onclick="this.select(); document.execCommand('copy');">
                </div>
                <button class="button w-full mt-4" style="background: var(--color-gateway-brand-bg); padding: 0.75rem;" onclick="navigator.clipboard.writeText('{{ url('/?ref=' . $profile->referral_code) }}'); alert('Link copied!');">Copy Link</button>
            </div>

            <div class="cockpit-table-card" style="padding: 1.5rem; background: var(--color-gateway-brand-bg); color: #fff;">
                <span class="eyebrow" style="color: rgba(255,255,255,0.7);">Goal Progress</span>
                <h3 style="color: #fff; margin-bottom: 1rem;">Direct Payouts</h3>
                <p style="color: rgba(255,255,255,0.8); font-size: 0.9rem; margin-bottom: 1.5rem;">Conversions become pending payouts 30 days after package verification.</p>
                <div class="h-2 w-full bg-white/10 rounded-full mb-2">
                    <div class="h-full bg-indigo-400 rounded-full" style="width: 65%;"></div>
                </div>
                <div class="text-xs text-white/60">Next payout target: $500.00</div>
            </div>
        </aside>

        <!-- KPI Row -->
        <div class="cockpit-kpi-row">
            <article class="cockpit-kpi-card">
                <span class="eyebrow">Clicks</span>
                <strong>{{ number_format($profile->click_count) }}</strong>
                <p>Link Awareness</p>
            </article>
            <article class="cockpit-kpi-card" style="border-color: var(--color-gateway-accent);">
                <span class="eyebrow">Converted</span>
                <strong>{{ number_format($profile->conversion_count) }}</strong>
                <p>Active Referrals</p>
            </article>
            <article class="cockpit-kpi-card">
                <span class="eyebrow">Rate</span>
                <strong>{{ number_format($profile->commission_rate, 0) }}%</strong>
                <p>Base Tier Payout</p>
            </article>
            <article class="cockpit-kpi-card" style="border-color: #10b981;">
                <span class="eyebrow">Pending</span>
                <strong>${{ number_format($profile->pending_payout_cents / 100, 2) }}</strong>
                <p>Cleared Payouts</p>
            </article>
        </div>

        <main class="cockpit-main">
            <!-- Referral Log -->
            <div id="referrals">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Referral Log</h2>
                        <p class="text-gray-500">Accounts created via your link.</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="floating-group" style="margin-bottom: 0; min-width: 240px;">
                            <input type="text" x-model="search" placeholder=" ">
                            <label>Filter referrals...</label>
                        </div>
                    </div>
                </div>

                <div class="cockpit-table-card">
                    <table class="cockpit-table">
                        <thead>
                            <tr>
                                <th>Account Details</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Status</th>
                                <th style="width: 60px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($referrals as $referredUser)
                                <tr x-show="!search || '{{ strtolower($referredUser->name) }}'.includes(search.toLowerCase())">
                                    <td>
                                        <span class="cockpit-primary-data">{{ $referredUser->name }}</span>
                                        <span class="cockpit-secondary-data">User ID: #{{ $referredUser->id }}</span>
                                    </td>
                                    <td>
                                        <span class="cockpit-primary-data">{{ ucfirst($referredUser->role) }}</span>
                                        <span class="cockpit-secondary-data">Target workspace</span>
                                    </td>
                                    <td>
                                        <span class="cockpit-primary-data">{{ $referredUser->created_at->format('M j, Y') }}</span>
                                        <span class="cockpit-secondary-data">{{ $referredUser->created_at->diffForHumans() }}</span>
                                    </td>
                                    <td>
                                        <span class="status-pill status-pill--{{ $referredUser->status === 'active' ? 'assigned' : 'new' }}">{{ ucfirst($referredUser->status) }}</span>
                                    </td>
                                    <td>
                                        <button class="kebab-trigger">⋮</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="cockpit-empty-state">
                                            <img src="{{ asset('images/illustrations/empty-leads.png') }}" alt="Empty" class="cockpit-empty-illustration">
                                            <h3>Start your network</h3>
                                            <p class="text-gray-500">Share your unique link above to see your first referrals here.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</section>

        </div>
    </div>
</section>
@endsection
