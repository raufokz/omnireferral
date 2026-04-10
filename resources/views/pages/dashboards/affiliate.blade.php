@extends('layouts.dashboard')

@section('dashboard_nav')
    <a href="{{ route('dashboard') }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        Dashboard
    </a>
    <a href="{{ route('dashboard.affiliate') }}" class="active">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
        Affiliate Hub
    </a>
    <a href="#referrals">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
        Referral Log
    </a>
    <a href="#link">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
        Your Link
    </a>
@endsection

@section('content')

    <div class="dash-cards-grid">
        <div class="dash-card dash-card--purple">
            <div class="dash-card-top">
                <div class="dash-card-avatars" style="font-weight: bold;">
                    <span>{{ number_format($profile->click_count) }}</span>
                </div>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2"><path d="M15 3h6v6"></path><path d="M9 21H3v-6"></path><path d="M21 3l-7 7"></path><path d="M3 21l7-7"></path></svg>
            </div>
            <div class="dash-card-bottom">
                <h3>Link Clicks</h3>
                <div class="dash-card-meta">
                    <strong>{{ number_format($profile->click_count) }}</strong> <span>total visits</span>
                </div>
                <div class="dash-progress-bar">
                    <div class="dash-progress-fill" style="width: 100%"></div>
                </div>
            </div>
        </div>

        <div class="dash-card dash-card--teal">
            <div class="dash-card-top">
                <div class="dash-card-avatars" style="font-weight: bold;">
                    <span>{{ number_format($profile->conversion_count) }}</span>
                </div>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
            </div>
            <div class="dash-card-bottom">
                <h3>Converted Users</h3>
                <div class="dash-card-meta">
                    <strong>{{ number_format($profile->conversion_count) }}</strong> <span>active referrals</span>
                </div>
                <div class="dash-progress-bar">
                    <div class="dash-progress-fill" style="width: 40%"></div>
                </div>
            </div>
        </div>

        <div class="dash-card dash-card--orange">
            <div class="dash-card-top">
                <div class="dash-card-avatars" style="font-weight: bold;">
                    <span style="width: auto; padding: 0 8px; border-radius: 12px; font-size: 0.8rem;">${{ number_format($profile->pending_payout_cents / 100, 0) }}</span>
                </div>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
            <div class="dash-card-bottom">
                <h3>Pending Payout</h3>
                <div class="dash-card-meta">
                    <strong>${{ number_format($profile->pending_payout_cents / 100, 2) }}</strong> <span>clearing soon</span>
                </div>
                <div class="dash-progress-bar">
                    <div class="dash-progress-fill" style="width: 70%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="dash-bottom-grid">
        <section id="referrals">
            <span class="dash-section-title">Referral Log</span>
            <div class="dash-task-list">
                @forelse($referrals as $referredUser)
                <div class="dash-task-item" style="border-left-color: {{ $referredUser->status === 'active' ? '#4BB2B2' : '#FA734A' }};">
                    <div class="dash-task-item__content">
                        <strong>{{ $referredUser->name }}</strong>
                        <span>{{ ucfirst($referredUser->role) }} • Joined {{ $referredUser->created_at->format('M j, Y') }}</span>
                    </div>
                    <span style="font-size: 0.8rem; font-weight: 600; padding: 4px 10px; border-radius: 20px; background: {{ $referredUser->status === 'active' ? '#E6F4F4' : '#FFF0EA' }}; color: {{ $referredUser->status === 'active' ? '#4BB2B2' : '#FA734A' }};">
                        {{ ucfirst($referredUser->status) }}
                    </span>
                </div>
                @empty
                <div class="dash-task-item">
                    <div class="dash-task-item__content">
                        <strong>No referrals yet</strong>
                        <span>Share your link to begin earning commissions.</span>
                    </div>
                </div>
                @endforelse
            </div>
        </section>

        <section id="link">
            <span class="dash-section-title">Program Overview</span>
            <div class="dash-stats-grid">
                <div class="dash-stat-box">
                    <strong>{{ number_format($profile->commission_rate, 0) }}%</strong>
                    <span>Base Tier Payout</span>
                </div>
                <div class="dash-stat-box">
                    <strong>30</strong>
                    <span>Days to Clear</span>
                </div>
            </div>

            <div class="dash-pro-banner mt-4" style="flex-direction: column; align-items: flex-start; gap: 1rem; background: #EFEAF2; border: 1px solid #E1D3E7;">
                <div>
                    <h4>Your Affiliate Link</h4>
                    <p style="color: #6A3771; opacity: 0.8; margin-bottom: 0.5rem;">Copy and share to start earning.</p>
                </div>
                
                <div style="background: #FFF; width: 100%; border-radius: 8px; padding: 0.75rem; border: 1px dashed #6A3771;">
                    <input type="text" class="text-xs font-mono w-full bg-transparent border-none focus:ring-0" style="color: #6A3771; outline: none; width: 100%;" readonly value="{{ url('/?ref=' . $profile->referral_code) }}" onclick="this.select(); document.execCommand('copy');">
                </div>
                <button class="dash-btn-primary" style="background:#6A3771; width:100%; justify-content:center;" onclick="navigator.clipboard.writeText('{{ url('/?ref=' . $profile->referral_code) }}'); alert('Link copied!');">Copy Link</button>
            </div>
            
            <div class="dash-pro-banner mt-4">
                <div>
                    <h4>Next Payout Goal</h4>
                    <p>Track towards $500.00 goal.</p>
                </div>
                <div style="text-align:right;">
                    <span>65%</span>
                </div>
            </div>
        </section>
    </div>
@endsection
