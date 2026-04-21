@extends('layouts.dashboard')

@section('dashboard_eyebrow', $currentRoleLabel ?? 'Workspace')
@section('dashboard_title', 'Platform Dashboard')
@section('dashboard_description', 'Pick a workspace based on your access role and move directly into the relevant operational page.')

@section('content')
<div class="workspace-stack">
    <section class="workspace-grid workspace-grid--4">
        <article class="workspace-card workspace-kpi">
            <span>Total Leads</span>
            <strong>{{ number_format($leadCount ?? 0) }}</strong>
            <span>Across all workspace flows</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Packages</span>
            <strong>{{ number_format($packageCount ?? 0) }}</strong>
            <span>Active plans on platform</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Properties</span>
            <strong>{{ number_format($propertyCount ?? 0) }}</strong>
            <span>Total listing records</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Agents</span>
            <strong>{{ number_format($agentCount ?? 0) }}</strong>
            <span>Partner profiles in network</span>
        </article>
    </section>

    <section class="workspace-grid workspace-grid--2">
        <article class="workspace-card">
            <span class="eyebrow">Your Workspaces</span>
            <h2>Role-Based Access</h2>
            <ul class="workspace-list">
                @forelse($roleCards as $card)
                    <li>
                        <strong>{{ $card['title'] }}</strong>
                        <small>{{ $card['copy'] }}</small>
                        <div class="workspace-actions" style="margin-top: 0.6rem;">
                            <a href="{{ $card['route'] }}" class="button">Open Workspace</a>
                        </div>
                    </li>
                @empty
                    <li>
                        <strong>No specific workspace assigned</strong>
                        <small>Contact support to configure your dashboard access.</small>
                    </li>
                @endforelse
            </ul>
        </article>

        <article class="workspace-card">
            <span class="eyebrow">Quick Actions</span>
            <h2>Next Steps</h2>
            <ul class="workspace-list">
                @foreach($quickActions as $action)
                    <li>
                        <strong>{{ $action['label'] }}</strong>
                        <div class="workspace-actions" style="margin-top: 0.6rem;">
                            <a href="{{ $action['route'] }}" class="button button--ghost-blue">Open</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </article>
    </section>
</div>
@endsection
