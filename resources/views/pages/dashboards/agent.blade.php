@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Agent Workspace')
@section('dashboard_title', 'Agent Dashboard')
@section('dashboard_description', 'Use the updated agent overview and page-based navigation for leads, listings, messages, and profile settings.')

@section('dashboard_actions')
    <a href="{{ route('dashboard.agent') }}" class="button">Open Agent Overview</a>
@endsection

@section('content')
<section class="workspace-card">
    <p>This legacy route now points to the redesigned agent experience.</p>
    <div class="workspace-actions" style="margin-top: 0.8rem;">
        <a href="{{ route('dashboard.agent') }}" class="button">Go To Overview</a>
        <a href="{{ route('agent.leads.index') }}" class="button button--ghost-blue">Open Leads</a>
        <a href="{{ route('agent.listings.index') }}" class="button button--ghost-blue">Open Listings</a>
    </div>
</section>
@endsection
