@extends('layouts.dashboard')

@section('dashboard_nav')
    <a href="{{ route('dashboard') }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        Dashboard
    </a>
    <a href="{{ route('dashboard.agent') }}" class="active">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        Agent Workspace
    </a>
    <a href="{{ route('agent.leads') }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        Assigned Leads
    </a>
    <a href="{{ route('agent.listings') }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
        Listings
    </a>
    <a href="{{ route('profile.edit') }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
        Setting
    </a>
@endsection

@section('content')
@php
    $agentStats = $agentStats ?? ['score' => 'A+', 'leads_received' => 12, 'response_rate' => '95%'];
    $pipeline = $pipeline ?? [['label' => 'New', 'count' => 5], ['label' => 'Contacted', 'count' => 3], ['label' => 'Qualified', 'count' => 2]];
    $closedLeadCount = 2;
    $leadTableMax = max(1, collect($pipeline)->max('count'));
    $leads = $leads ?? collect([]);
@endphp

    <div class="dash-cards-grid">
        <div class="dash-card dash-card--purple">
            <div class="dash-card-top">
                <div class="dash-card-avatars">
                    <span>A+</span>
                </div>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            </div>
            <div class="dash-card-bottom">
                <h3>Profile Rating</h3>
                <div class="dash-card-meta">
                    <strong>{{ $agentStats['score'] }}</strong> <span>Trust Score</span>
                </div>
                <div class="dash-progress-bar">
                    <div class="dash-progress-fill" style="width: 95%"></div>
                </div>
            </div>
        </div>

        <div class="dash-card dash-card--orange">
            <div class="dash-card-top">
                <div class="dash-card-avatars">
                    <img src="{{ asset('images/realtors/2.png') }}" alt="Lead">
                    <span>+{{ $agentStats['leads_received'] }}</span>
                </div>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
            </div>
            <div class="dash-card-bottom">
                <h3>Assigned Leads</h3>
                <div class="dash-card-meta">
                    <strong>{{ $agentStats['leads_received'] }}</strong> <span>queue limit</span>
                </div>
                <div class="dash-progress-bar">
                    <div class="dash-progress-fill" style="width: 70%"></div>
                </div>
            </div>
        </div>

        <div class="dash-card dash-card--teal">
            <div class="dash-card-top">
                <div class="dash-card-avatars">
                    <span>{{ $agentStats['response_rate'] }}</span>
                </div>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
            </div>
            <div class="dash-card-bottom">
                <h3>Response Rate</h3>
                <div class="dash-card-meta">
                    <strong>{{ $agentStats['response_rate'] }}</strong> <span>Follow-up speed</span>
                </div>
                <div class="dash-progress-bar">
                    <div class="dash-progress-fill" style="width: 85%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="dash-bottom-grid">
        <section>
            <span class="dash-section-title">Lead Queue For Today</span>
            <div class="dash-task-list">
                @forelse($leads->take(3) as $lead)
                <div class="dash-task-item">
                    <div class="dash-task-item__content">
                        <strong>{{ $lead->name }}</strong>
                        <span>{{ ucfirst($lead->intent) }} lead • {{ $lead->phone }}</span>
                    </div>
                    <form action="{{ route('agent.leads.status', $lead) }}" method="POST" style="margin:0;">
                        @csrf
                        <input type="hidden" name="status" value="contacted">
                        <button type="submit" class="dash-radio {{ $lead->status !== 'new' ? 'checked' : '' }}" aria-label="Mark as contacted"></button>
                    </form>
                </div>
                @empty
                <div class="dash-task-item">
                    <div class="dash-task-item__content">
                        <strong>No leads assigned</strong>
                        <span>Your queue is clear. View past closed leads or wait for new assignments.</span>
                    </div>
                </div>
                @endforelse
                
                <a href="#leads" class="dash-btn-primary" style="margin-top: 1rem; justify-content: center; width: 100%;">View All Queue</a>
            </div>
        </section>

        <section>
            <span class="dash-section-title">Pipeline Progress</span>
            <div class="dash-stats-grid">
                @foreach($pipeline as $stage)
                <div class="dash-stat-box">
                    <strong>{{ $stage['count'] }}</strong>
                    <span>{{ $stage['label'] }}</span>
                </div>
                @endforeach
            </div>

            <div class="dash-pro-banner mt-4" style="flex-direction: column; align-items: flex-start; gap: 1rem;">
                <div>
                    <h4>Active Package</h4>
                    <p>{{ $activePlan?->name ?? 'No active package' }}</p>
                </div>
                <a href="{{ route('pricing') }}" class="dash-btn-icon" style="width:auto; padding: 0 1rem;">Compare Plans</a>
            </div>
        </section>
    </div>
@endsection
