@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Affiliate Workspace')
@section('dashboard_title', 'Affiliate Hub')
@section('dashboard_description', 'Track referrals, conversions, and payouts from a dedicated affiliate dashboard page.')

@section('content')
<div class="workspace-stack">
    <section class="workspace-grid workspace-grid--3" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
        <article class="workspace-card workspace-kpi" data-icon="↗" data-trend="Traffic">
            <span>Link Clicks</span>
            <strong>{{ number_format($profile->click_count) }}</strong>
            <span>Total referral visits</span>
        </article>
        <article class="workspace-card workspace-kpi workspace-kpi--warm" data-icon="👥" data-trend="Growth">
            <span>Signups</span>
            <strong>{{ number_format($referralSignupCount ?? 0) }}</strong>
            <span>Accounts created with your referral</span>
        </article>
        <article class="workspace-card workspace-kpi" data-icon="$" data-trend="Paid">
            <span>Paid plans</span>
            <strong>{{ number_format($referralPaidPlanCount ?? 0) }}</strong>
            <span>Referred users with an active package</span>
        </article>
        <article class="workspace-card workspace-kpi workspace-kpi--violet" data-icon="✓" data-trend="Legacy">
            <span>Legacy counter</span>
            <strong>{{ number_format($profile->conversion_count) }}</strong>
            <span>Cookie signup conversions (kept for compatibility)</span>
        </article>
        <article class="workspace-card workspace-kpi workspace-kpi--warm" data-icon="$" data-trend="Payout">
            <span>Pending Payout</span>
            <strong>${{ number_format($profile->pending_payout_cents / 100, 2) }}</strong>
            <span>Clears on payout cycle</span>
        </article>
    </section>

    <section class="workspace-grid workspace-grid--2">
        <article class="workspace-card">
            <span class="eyebrow">Referral Log</span>
            <h2>Recent Referrals</h2>
            <ul class="workspace-list">
                @forelse($referrals as $referredUser)
                    <li>
                        <strong>{{ $referredUser->name }}</strong>
                        <small>{{ ucfirst($referredUser->role) }} · Joined {{ $referredUser->created_at->format('M j, Y') }}</small>
                    </li>
                @empty
                    <li>
                        <strong>No referrals yet</strong>
                        <small>Share your link to start earning commissions.</small>
                    </li>
                @endforelse
            </ul>
        </article>

        <article class="workspace-card">
            <span class="eyebrow">Your Link</span>
            <h2>Affiliate Link</h2>
            <label class="workspace-field">
                <span>Share URL</span>
                <input type="text" readonly value="{{ $referralShareUrl ?? url('/?ref=' . $profile->referral_code) }}">
            </label>
            <div class="workspace-actions" style="margin-top: 0.8rem;">
                <button
                    class="button"
                    type="button"
                    onclick="navigator.clipboard.writeText('{{ $referralShareUrl ?? url('/?ref=' . $profile->referral_code) }}'); this.textContent='Copied'; setTimeout(() => this.textContent='Copy Link', 1500);"
                >
                    Copy Link
                </button>
            </div>
        </article>
    </section>

    <section class="workspace-card">
        <span class="eyebrow">Click log</span>
        <h2>Recent referral visits</h2>
        <ul class="workspace-list">
            @forelse($recentClicks ?? [] as $click)
                <li>
                    <strong>{{ $click->created_at->format('M j, g:i a') }}</strong>
                    <small>Code {{ $click->referral_code }}</small>
                </li>
            @empty
                <li><small>No logged clicks yet. Share your link with <code>?ref=</code> to start tracking.</small></li>
            @endforelse
        </ul>
    </section>
</div>
@endsection
