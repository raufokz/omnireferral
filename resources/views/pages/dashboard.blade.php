@extends('layouts.app')

@section('content')
<section class="page-hero dashboard-page-hero">
    <div class="container page-hero__content">
        <span class="eyebrow">Platform Dashboard</span>
        <h1>{{ $currentRoleLabel }} workspace overview</h1>
        <p>OmniReferral keeps qualification, packaging, listings, and customer handoff connected inside one premium operating system.</p>
    </div>
</section>
<section class="section dashboard-page dashboard-page--premium">
    <div class="container dashboard-main-panel dashboard-main-panel--overview">
        <section class="dashboard-stat-strip">
            <article class="dashboard-stat-card"><span class="dashboard-stat-card__label">Leads in Pipeline</span><strong>{{ $leadCount }}</strong><p>Active requests moving through the platform.</p></article>
            <article class="dashboard-stat-card"><span class="dashboard-stat-card__label">Package Options</span><strong>{{ $packageCount }}</strong><p>Lead and support plans available across the ecosystem.</p></article>
            <article class="dashboard-stat-card"><span class="dashboard-stat-card__label">Listings</span><strong>{{ $propertyCount }}</strong><p>Properties available across the marketplace experience.</p></article>
            <article class="dashboard-stat-card"><span class="dashboard-stat-card__label">Partner Agents</span><strong>{{ $agentCount }}</strong><p>Realtors available for verified lead delivery.</p></article>
        </section>

        <div class="dashboard-grid-2col dashboard-grid-2col--wide">
            <section class="dashboard-surface">
                <div class="dashboard-surface__header">
                    <div>
                        <span class="eyebrow">Your Workspace</span>
                        <h2>Open the part of OmniReferral built for your role</h2>
                    </div>
                </div>
                <div class="service-grid service-grid--dashboard">
                    @foreach($roleCards as $card)
                        <article class="service-card dashboard-role-card">
                            <span class="pricing-label">Workspace</span>
                            <h3>{{ $card['title'] }}</h3>
                            <p>{{ $card['copy'] }}</p>
                            <a href="{{ $card['route'] }}" class="button button--orange">Open Workspace</a>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="dashboard-surface">
                <div class="dashboard-surface__header">
                    <div>
                        <span class="eyebrow">Quick Actions</span>
                        <h2>Move faster with the next best click</h2>
                    </div>
                </div>
                <div class="focus-list">
                    @foreach($quickActions as $action)
                        <article>
                            <strong>{{ $action['label'] }}</strong>
                            <p>Jump directly into the next conversion-focused task for your role.</p>
                            <a href="{{ $action['route'] }}" class="button button--ghost-blue">Open</a>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</section>
@endsection
