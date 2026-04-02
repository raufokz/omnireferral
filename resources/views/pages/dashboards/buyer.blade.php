@extends('layouts.app')

@section('content')
<section class="page-hero dashboard-page-hero dashboard-page-hero--buyer">
    <div class="container page-hero__content">
        <span class="eyebrow">Buyer Workspace</span>
        <h1>Track your home search without losing the human touch</h1>
        <p>Saved homes, ZIP-based browsing, and request updates stay organized in one calm, trust-building space.</p>
    </div>
</section>

<section class="section dashboard-page dashboard-page--metamorphosis" x-data="{ search: '', filter: 'all' }">
    <div class="container cockpit-grid">
        <!-- Sidebar Navigation (Preserved context) -->
        <aside class="cockpit-side" style="grid-row: span 3;">
            <div class="cockpit-table-card mb-8" style="padding: 1.5rem;">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-800 font-bold">B</div>
                    <div>
                        <span class="eyebrow" style="margin: 0;">Buyer Workspace</span>
                        <h2 style="font-size: 1.25rem; margin: 0;">{{ Auth::user()->name }}</h2>
                    </div>
                </div>
                <nav class="dashboard-side-nav" aria-label="Buyer dashboard navigation">
                    <a class="is-active" href="{{ route('dashboard.buyer') }}">Overview</a>
                    <a href="#shortlist">My Shortlist</a>
                    <a href="#requests">Requests</a>
                    <a href="{{ route('listings') }}">Browse All</a>
                </nav>
            </div>

            <div class="cockpit-table-card mb-8" style="padding: 1.5rem;">
                <span class="eyebrow">Search Depth</span>
                <h3 class="mb-4">Target Market</h3>
                <div class="map-card" style="height: 200px; border-radius: 12px; overflow: hidden;">
                    <iframe src="https://www.google.com/maps?q={{ urlencode($properties->first()?->zip_code ?? 'Dallas, TX') }}&output=embed" style="width: 100%; height: 100%; border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
                <button class="button button--ghost-blue w-full mt-4" style="padding: 0.75rem;">Change Area</button>
            </div>

            <div class="cockpit-table-card" style="padding: 1.5rem; background: var(--color-gateway-brand-bg); color: #fff;">
                <span class="eyebrow" style="color: rgba(255,255,255,0.7);">Need Help?</span>
                <h3 style="color: #fff; margin-bottom: 1rem;">Concierge Support</h3>
                <p style="color: rgba(255,255,255,0.8); font-size: 0.9rem; margin-bottom: 1.5rem;">Our team is ready to help you verify property details or schedule tours.</p>
                <a href="{{ route('contact') }}" class="button w-full" style="background: var(--color-gateway-accent); border: none;">Contact Advisor</a>
            </div>
        </aside>

        <!-- KPI Row -->
        <div class="cockpit-kpi-row">
            <article class="cockpit-kpi-card">
                <span class="eyebrow">Shortlist</span>
                <strong>{{ $buyerStats['saved_listings'] }}</strong>
                <p>Saved Homes</p>
            </article>
            <article class="cockpit-kpi-card" style="border-color: var(--color-gateway-accent);">
                <span class="eyebrow">Top Picks</span>
                <strong>{{ $buyerStats['favorites'] }}</strong>
                <p>High Priority</p>
            </article>
            <article class="cockpit-kpi-card">
                <span class="eyebrow">Searches</span>
                <strong>{{ $buyerStats['saved_searches'] }}</strong>
                <p>Saved Criteria</p>
            </article>
            <article class="cockpit-kpi-card" style="border-color: #10b981;">
                <span class="eyebrow">Alerts</span>
                <strong>{{ $buyerStats['new_alerts'] }}</strong>
                <p>Market Updates</p>
            </article>
        </div>

        <main class="cockpit-main">
            <!-- Shortlist Section -->
            <div class="flex items-center justify-between mb-6" id="shortlist">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Your Shortlist</h2>
                    <p class="text-gray-500">Homes that match your target markets.</p>
                </div>
                <div class="flex gap-4">
                    <div class="floating-group" style="margin-bottom: 0; min-width: 240px;">
                        <input type="text" x-model="search" placeholder=" ">
                        <label>Filter by ZIP...</label>
                    </div>
                </div>
            </div>

            <div class="listing-grid listing-grid--dashboard mb-12">
                @forelse($properties as $property)
                    <article class="listing-card" x-show="!search || '{{ $property->zip_code }}'.includes(search)" data-animate="up">
                        <div class="relative">
                            <img src="{{ $property->image_url }}" alt="Home" style="height: 200px; object-fit: cover;">
                            <span class="absolute top-4 left-4 status-pill status-pill--qualified" style="background: var(--color-gateway-brand-bg); color: white;">{{ $property->zip_code }}</span>
                        </div>
                        <div class="listing-card__body p-6">
                            <strong class="text-2xl" style="color: var(--color-gateway-brand-bg);">${{ number_format($property->price) }}</strong>
                            <h3 style="margin: 0.5rem 0 0.25rem;">{{ $property->title }}</h3>
                            <p class="listing-location mb-6">{{ $property->location }}</p>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue" style="padding: 0.75rem; font-size: 0.85rem;">Details</a>
                                <a href="{{ route('contact') }}?property={{ urlencode($property->title) }}" class="button" style="padding: 0.75rem; font-size: 0.85rem; background: var(--color-gateway-brand-bg);">Tour</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full">
                        <div class="cockpit-empty-state">
                            <img src="{{ asset('images/illustrations/empty-leads.png') }}" alt="Empty" class="cockpit-empty-illustration">
                            <h3>Start your search</h3>
                            <p class="text-gray-500">Browse our active listings to find homes that fit your criteria.</p>
                            <a href="{{ route('listings') }}" class="button mt-6">Explore Listings</a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Request Tracker -->
            <div id="requests">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Request Journey</h2>
                    <p class="text-gray-500">Track your verified inquiries in real-time.</p>
                </div>

                <div class="cockpit-table-card p-8">
                    <div class="grid grid-cols-4 gap-8 mb-12">
                        @php($maxBuyerStage = max(1, collect($buyerJourney)->max('count')))
                        @foreach($buyerJourney as $stage)
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-900 mb-1">{{ $stage['count'] }}</div>
                                <div class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-3">{{ $stage['label'] }}</div>
                                <div class="h-1 w-full bg-gray-100 rounded-full">
                                    <div class="h-full bg-orange-500 rounded-full" style="width: {{ ($stage['count'] / $maxBuyerStage) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <table class="cockpit-table">
                        <thead>
                            <tr>
                                <th>Inquiry Target</th>
                                <th>ZIP / Market</th>
                                <th>Status</th>
                                <th style="width: 60px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($buyerRequests as $request)
                                <tr>
                                    <td>
                                        <span class="cockpit-primary-data">{{ $request->name }}</span>
                                        <span class="cockpit-secondary-data">{{ $request->property_type ?: 'Resident search' }}</span>
                                    </td>
                                    <td>
                                        <span class="cockpit-primary-data">{{ $request->zip_code }}</span>
                                        <span class="cockpit-secondary-data">Targeted market</span>
                                    </td>
                                    <td>
                                        <span class="status-pill status-pill--{{ $request->status }}">{{ ucfirst($request->status) }}</span>
                                    </td>
                                    <td>
                                        <button class="kebab-trigger">⋮</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-gray-500">No active inquiries submitted yet.</td>
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
