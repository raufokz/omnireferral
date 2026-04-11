@extends('layouts.dashboard')

@section('dashboard_nav')
    <a href="{{ route('dashboard') }}" class="active">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        Dashboard
    </a>
    <a href="{{ route('dashboard.agent') }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        Agent Workspace
    </a>
    <a href="{{ route('admin.dashboard') }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
        Admin Portal
    </a>
    <a href="{{ route('profile.edit') }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
        Setting
    </a>
@endsection

@section('content')
    <div class="dash-cards-grid">
        <div class="dash-card dash-card--purple">
            <div class="dash-card-top">
                <div class="dash-card-avatars">
                    <img src="{{ asset('images/realtors/1.png') }}" alt="User">
                    <img src="{{ asset('images/realtors/2.png') }}" alt="User">
                    <span>+7</span>
                </div>
                <!-- icon -->
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
            </div>
            <div class="dash-card-bottom">
                <h3>Agent Workspace</h3>
                <div class="dash-card-meta">
                    <strong>{{ number_format($agentCount ?? 0) }}</strong> <span>agents</span> • <span>Active</span>
                </div>
                <div class="dash-progress-bar">
                    <div class="dash-progress-fill" style="width: 80%"></div>
                </div>
            </div>
        </div>

        <div class="dash-card dash-card--teal">
            <div class="dash-card-top">
                <div class="dash-card-avatars">
                    <img src="{{ asset('images/realtors/3.png') }}" alt="User">
                    <span>+2</span>
                </div>
                <!-- icon -->
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
            </div>
            <div class="dash-card-bottom">
                <h3>Global Listings</h3>
                <div class="dash-card-meta">
                    <strong>{{ number_format($propertyCount ?? 0) }}</strong> <span>market listings</span>
                </div>
                <div class="dash-progress-bar">
                    <div class="dash-progress-fill" style="width: 46%"></div>
                </div>
            </div>
        </div>

        <div class="dash-card dash-card--orange">
            <div class="dash-card-top">
                <div class="dash-card-avatars">
                    <img src="{{ asset('images/realtors/4.png') }}" alt="User">
                    <img src="{{ asset('images/realtors/1.png') }}" alt="User">
                    <span>+15</span>
                </div>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
            </div>
            <div class="dash-card-bottom">
                <h3>Live Leads Queue</h3>
                <div class="dash-card-meta">
                    <strong>{{ number_format($leadCount ?? 0) }}</strong> <span>requests</span>
                </div>
                <div class="dash-progress-bar">
                    <div class="dash-progress-fill" style="width: 65%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="dash-bottom-grid">
        <section>
            <span class="dash-section-title">Focus for today</span>
            <div class="dash-task-list">
                @foreach($quickActions ?? [['label' => 'Complete profile onboarding', 'route' => '#'], ['label' => 'Review unassigned leads', 'route' => '#']] as $action)
                <div class="dash-task-item">
                    <div class="dash-task-item__content">
                        <strong>{{ $action['label'] }}</strong>
                        <span>Suggested action based on your role</span>
                    </div>
                    <a href="{{ $action['route'] ?? '#' }}" class="dash-radio"></a>
                </div>
                @endforeach
                
                <div class="dash-task-item">
                    <div class="dash-task-item__content">
                        <strong>Explore Package Plans</strong>
                        <span>Open pricing to scale your limits</span>
                    </div>
                    <a href="{{ route('pricing') }}" class="dash-radio checked"></a>
                </div>
            </div>
        </section>

        <section>
            <span class="dash-section-title">Overview</span>
            <div class="dash-stats-grid">
                <div class="dash-stat-box">
                    <strong>{{ number_format($leadCount ?? 0) }}</strong>
                    <span>Total requests</span>
                </div>
                <div class="dash-stat-box">
                    <strong>{{ count($roleCards ?? []) }}</strong>
                    <span>Roles</span>
                </div>
                <div class="dash-stat-box dashed">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span style="display:block; margin-top:5px;">New Data</span>
                </div>
            </div>

            <div class="dash-pro-banner mt-4">
                <div>
                    <h4>Enterprise Plan</h4>
                    <p>Unlock more lead capacity with premium.</p>
                </div>
                <div style="text-align:right;">
                    <span>$199 /m</span>
                </div>
            </div>
        </section>
    </div>
@endsection
