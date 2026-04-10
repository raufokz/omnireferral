@extends('layouts.app')

@section('content')
@php
    $sellerUser = auth()->user();
    $sellerAvatar = $sellerUser?->avatar
        ? asset('storage/' . ltrim($sellerUser->avatar, '/'))
        : asset('images/realtors/3.png');
    $sellerJourneyMax = max(1, collect($sellerJourney)->max('count'));
    $marketCoverage = $properties->pluck('zip_code')->filter()->unique()->count();
    $submittedSellerCount = data_get(collect($sellerJourney)->firstWhere('label', 'Submitted'), 'count', 0);
    $closedSellerCount = data_get(collect($sellerJourney)->firstWhere('label', 'Closed'), 'count', 0);
    $sellerHighlights = [
        ['label' => 'Listings', 'value' => $sellerStats['active_listings']],
        ['label' => 'Inquiries', 'value' => $sellerStats['open_inquiries']],
        ['label' => 'Matches', 'value' => $sellerStats['buyer_matches']],
        ['label' => 'Coverage', 'value' => $marketCoverage],
    ];
@endphp

<section class="or-dashboard or-dashboard--seller">
    <div class="or-dashboard__shell">
        <aside class="or-dashboard__sidebar">
            <div class="or-dashboard__brand">
                <img src="{{ asset('images/omnireferral-logo.png') }}" alt="OmniReferral logo">
                <div class="or-dashboard__brand-copy">
                    <strong>Seller Workspace</strong>
                    <span>OmniReferral listing desk</span>
                </div>
            </div>

            <nav class="or-dashboard__nav" aria-label="Seller workspace navigation">
                <a class="is-active" href="{{ route('dashboard.seller') }}">
                    <span>Overview</span>
                    <small>Track listing readiness, requests, and activity</small>
                </a>
                <a href="#seller-intake">
                    <span>Submit Listing</span>
                    <small>Send a property to admin review from the dashboard</small>
                </a>
                <a href="#seller-requests">
                    <span>Request Queue</span>
                    <small>Review seller-side intake and buyer interest</small>
                </a>
                <a href="{{ route('listings') }}">
                    <span>Marketplace</span>
                    <small>See how approved homes appear publicly</small>
                </a>
                <a href="{{ route('contact') }}">
                    <span>Support</span>
                    <small>Talk with OmniReferral about your listing plan</small>
                </a>
            </nav>

            <article class="or-dashboard__profile-card">
                <div class="or-dashboard__profile-head">
                    <div class="or-dashboard__avatar or-dashboard__avatar--warm">
                        <img src="{{ $sellerAvatar }}" alt="{{ $sellerUser?->name ?: 'Seller' }} profile image" loading="lazy">
                    </div>
                    <div class="or-dashboard__profile-copy">
                        <span class="eyebrow">Seller Profile</span>
                        <h2>{{ $sellerUser?->name ?: 'OmniReferral Seller' }}</h2>
                        <p>{{ $sellerUser?->email ?: 'Ready to submit your property' }}</p>
                    </div>
                </div>

                <div class="or-dashboard__chip-row">
                    <span>Seller</span>
                    <span>{{ $marketCoverage }} active ZIP{{ $marketCoverage === 1 ? '' : 's' }}</span>
                    <span>{{ $sellerStats['price_updates'] }} pricing updates</span>
                </div>

                <div class="or-dashboard__profile-grid">
                    @foreach($sellerHighlights as $highlight)
                        <div>
                            <span>{{ $highlight['label'] }}</span>
                            <strong>{{ $highlight['value'] }}</strong>
                        </div>
                    @endforeach
                </div>

                <div class="or-dashboard__action-row">
                    <a href="#seller-intake" class="button button--orange">Submit Property</a>
                    <a href="{{ route('contact') }}" class="button button--ghost-blue">Talk To Support</a>
                </div>
            </article>

            <article class="or-dashboard__mini-card">
                <span class="eyebrow">Review Flow</span>
                <strong>Every seller listing goes through admin approval</strong>
                <p>Once submitted, your property stays pending until the OmniReferral team reviews and approves it for public visibility.</p>
                <div class="or-dashboard__mini-grid">
                    <div>
                        <span>Submitted</span>
                        <strong>{{ $submittedSellerCount }}</strong>
                    </div>
                    <div>
                        <span>Closed</span>
                        <strong>{{ $closedSellerCount }}</strong>
                    </div>
                </div>
                <a href="#seller-requests" class="button">Review Activity</a>
            </article>
        </aside>

        <main class="or-dashboard__main">
            <header class="or-dashboard__header">
                <div class="or-dashboard__header-copy">
                    <span class="eyebrow">Seller Dashboard</span>
                    <h1>Publish properties, monitor demand, and keep review steps visible.</h1>
                    <p>This seller workspace now follows the same Omnireferral dashboard system as agent, buyer, admin, and staff so the experience stays aligned across every role.</p>
                    <div class="or-dashboard__header-chips">
                        <span>{{ $sellerStats['active_listings'] }} active showcase listings</span>
                        <span>{{ $sellerRequests->count() }} seller requests</span>
                        <span>{{ $sellerStats['buyer_matches'] }} buyer matches</span>
                    </div>
                </div>

                <div class="or-dashboard__header-actions">
                    <a href="#seller-intake" class="button">Add Listing</a>
                    <a href="{{ route('listings') }}" class="button button--ghost-blue">Open Marketplace</a>
                </div>
            </header>

            <div class="or-dashboard__stat-row">
                <article class="or-dashboard__stat-card">
                    <span>Active Listings</span>
                    <strong>{{ $sellerStats['active_listings'] }}</strong>
                    <p>Approved properties currently visible in the marketplace</p>
                </article>
                <article class="or-dashboard__stat-card">
                    <span>Open Inquiries</span>
                    <strong>{{ $sellerStats['open_inquiries'] }}</strong>
                    <p>Seller-side conversations and intake activity in the system</p>
                </article>
                <article class="or-dashboard__stat-card">
                    <span>Buyer Matches</span>
                    <strong>{{ $sellerStats['buyer_matches'] }}</strong>
                    <p>Signals from buyers that may align with your market</p>
                </article>
                <article class="or-dashboard__stat-card or-dashboard__stat-card--warm">
                    <span>Price Updates</span>
                    <strong>{{ $sellerStats['price_updates'] }}</strong>
                    <p>Recent pricing adjustments tracked inside the workspace</p>
                </article>
            </div>

            <div class="or-dashboard__content-grid">
                <section class="or-dashboard__surface">
                    <div class="or-dashboard__surface-header">
                        <div>
                            <span class="eyebrow">Journey Pulse</span>
                            <h2>Seller request movement</h2>
                            <p>Track submission, qualification, market entry, and closing progress from one clean panel.</p>
                        </div>
                    </div>

                    <div class="or-dashboard__progress-list">
                        @foreach($sellerJourney as $stage)
                            <article class="or-dashboard__progress-item">
                                <div class="or-dashboard__progress-item-top">
                                    <strong>{{ $stage['label'] }}</strong>
                                    <span>{{ $stage['count'] }} records</span>
                                </div>
                                <div class="or-dashboard__progress-track">
                                    <span style="width: {{ ($stage['count'] / $sellerJourneyMax) * 100 }}%"></span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="or-dashboard__surface">
                    <div class="or-dashboard__surface-header">
                        <div>
                            <span class="eyebrow">Market Pulse</span>
                            <h2>What to watch before you submit</h2>
                            <p>Use this view to keep an eye on demand, coverage, and listing activity around your market.</p>
                        </div>
                    </div>

                    <div class="or-dashboard__mini-grid">
                        <div>
                            <span>Coverage</span>
                            <strong>{{ $marketCoverage }}</strong>
                        </div>
                        <div>
                            <span>Open Requests</span>
                            <strong>{{ $sellerRequests->count() }}</strong>
                        </div>
                        <div>
                            <span>Buyer Matches</span>
                            <strong>{{ $sellerStats['buyer_matches'] }}</strong>
                        </div>
                        <div>
                            <span>Price Updates</span>
                            <strong>{{ $sellerStats['price_updates'] }}</strong>
                        </div>
                    </div>

                    <div class="or-dashboard__tag-cloud">
                        <span>Admin review required</span>
                        <span>Marketplace ready</span>
                        <span>Seller support available</span>
                    </div>
                </section>
            </div>

            <section class="or-dashboard__surface or-dashboard__surface--wide" id="seller-intake">
                <div class="or-dashboard__surface-header">
                    <div>
                        <span class="eyebrow">Listing Intake</span>
                        <h2>Submit a property for admin review</h2>
                        <p>Seller submissions stay pending until the OmniReferral team accepts or rejects the listing.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('properties.store') }}" enctype="multipart/form-data" class="or-dashboard__form-grid">
                    @csrf
                    <div class="or-dashboard__form-grid or-dashboard__form-grid--two">
                        <label class="or-dashboard__field or-dashboard__field--full">
                            <span>Property Title</span>
                            <input type="text" name="title" value="{{ old('title') }}" required>
                        </label>
                        <label class="or-dashboard__field">
                            <span>Location</span>
                            <input type="text" name="location" value="{{ old('location') }}" required>
                        </label>
                        <label class="or-dashboard__field">
                            <span>ZIP Code</span>
                            <input type="text" name="zip_code" value="{{ old('zip_code') }}" required>
                        </label>
                        <label class="or-dashboard__field">
                            <span>Property Type</span>
                            <select name="property_type" required>
                                @foreach(['house' => 'House', 'apartment' => 'Apartment', 'condo' => 'Condo', 'commercial' => 'Commercial'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('property_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="or-dashboard__field">
                            <span>Price</span>
                            <input type="number" name="price" value="{{ old('price') }}" min="0" required>
                        </label>
                        <label class="or-dashboard__field">
                            <span>Beds</span>
                            <input type="number" name="beds" value="{{ old('beds') }}" min="0">
                        </label>
                        <label class="or-dashboard__field">
                            <span>Baths</span>
                            <input type="number" name="baths" value="{{ old('baths') }}" min="0" step="0.5">
                        </label>
                        <label class="or-dashboard__field">
                            <span>Square Feet</span>
                            <input type="number" name="sqft" value="{{ old('sqft') }}" min="0">
                        </label>
                        <label class="or-dashboard__field or-dashboard__field--full">
                            <span>Description</span>
                            <textarea name="description">{{ old('description') }}</textarea>
                        </label>
                        <label class="or-dashboard__field or-dashboard__field--full">
                            <span>Listing Image</span>
                            <input type="file" name="image" accept="image/*">
                        </label>
                    </div>

                    <div class="or-dashboard__action-row">
                        <button type="submit" class="button button--orange">Submit For Review</button>
                        <a href="{{ route('contact') }}" class="button button--ghost-blue">Ask About Listing Support</a>
                    </div>
                </form>
            </section>

            <section class="or-dashboard__surface or-dashboard__surface--wide" id="seller-requests">
                <div class="or-dashboard__surface-header">
                    <div>
                        <span class="eyebrow">Recent Activity</span>
                        <h2>Seller requests and live marketplace examples</h2>
                        <p>Keep seller-side activity and approved listing examples in the same visual rhythm as the rest of the dashboard.</p>
                    </div>
                </div>

                <div class="or-dashboard__dual-grid">
                    <section class="or-dashboard__surface or-dashboard__surface--compact">
                        <div class="or-dashboard__surface-header">
                            <div>
                                <span class="eyebrow">Request Queue</span>
                                <h3>Latest seller requests</h3>
                            </div>
                        </div>

                        <div class="or-dashboard__table-wrap">
                            <table class="or-dashboard__table">
                                <thead>
                                    <tr>
                                        <th>Request</th>
                                        <th>Market</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sellerRequests->take(4) as $request)
                                        <tr>
                                            <td>
                                                <div class="or-dashboard__detail-stack">
                                                    <strong>{{ $request->name }}</strong>
                                                    <span>{{ $request->email ?: 'No email provided' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="or-dashboard__detail-stack">
                                                    <strong>{{ $request->zip_code ?: 'No ZIP yet' }}</strong>
                                                    <span>{{ $request->property_type ?: 'Property details pending' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="status-pill status-pill--{{ \Illuminate\Support\Str::slug((string) $request->status, '_') }}">
                                                    {{ $request->statusLabel() }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3">
                                                <div class="or-dashboard__empty">
                                                    <h3>No seller requests yet</h3>
                                                    <p>Seller intake activity will appear here after the first submission.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="or-dashboard__surface or-dashboard__surface--compact">
                        <div class="or-dashboard__surface-header">
                            <div>
                                <span class="eyebrow">Marketplace Examples</span>
                                <h3>Current live properties</h3>
                            </div>
                            <a href="{{ route('listings') }}" class="button button--ghost-blue">View All</a>
                        </div>

                        <div class="or-dashboard__listing-grid">
                            @forelse($properties->take(2) as $property)
                                <article class="or-dashboard__listing-card">
                                    <div class="or-dashboard__listing-media">
                                        <img src="{{ $property->image_url }}" alt="{{ $property->title }}" loading="lazy">
                                        <span class="or-dashboard__listing-badge">{{ ucfirst($property->property_type ?: 'Home') }}</span>
                                    </div>
                                    <div class="or-dashboard__listing-body">
                                        <div class="or-dashboard__listing-top">
                                            <strong>${{ number_format($property->price) }}</strong>
                                            <span>{{ $property->status }}</span>
                                        </div>
                                        <h3>{{ $property->title }}</h3>
                                        <p>{{ $property->location }}</p>
                                        <div class="or-dashboard__listing-actions">
                                            <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue">View Details</a>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="or-dashboard__empty">
                                    <h3>No approved listings yet</h3>
                                    <p>Once listings are approved they will appear in the public marketplace.</p>
                                </div>
                            @endforelse
                        </div>
                    </section>
                </div>
            </section>
        </main>

        <aside class="or-dashboard__rail">
            <article class="or-dashboard__summary-card">
                <span class="eyebrow">Seller Snapshot</span>
                <h3>Listing readiness summary</h3>
                <p>The right rail uses the same summary treatment as the other OmniReferral role dashboards for visual consistency.</p>
                <strong class="or-dashboard__summary-total">{{ $sellerStats['active_listings'] }}</strong>
                <div class="or-dashboard__summary-meta">
                    <div>
                        <span>Open Inquiries</span>
                        <strong>{{ $sellerStats['open_inquiries'] }}</strong>
                    </div>
                    <div>
                        <span>Buyer Matches</span>
                        <strong>{{ $sellerStats['buyer_matches'] }}</strong>
                    </div>
                    <div>
                        <span>Coverage</span>
                        <strong>{{ $marketCoverage }}</strong>
                    </div>
                    <div>
                        <span>Price Updates</span>
                        <strong>{{ $sellerStats['price_updates'] }}</strong>
                    </div>
                </div>
                <div class="or-dashboard__summary-actions">
                    <a href="#seller-intake" class="button button--orange">Submit Property</a>
                    <a href="{{ route('contact') }}" class="button button--ghost-blue">Contact Support</a>
                </div>
            </article>

            <article class="or-dashboard__panel">
                <div class="or-dashboard__surface-header">
                    <div>
                        <span class="eyebrow">Recent Requests</span>
                        <h2>Latest seller-side activity</h2>
                    </div>
                </div>

                <div class="or-dashboard__queue-list">
                    @forelse($sellerRequests->take(3) as $request)
                        <article>
                            <strong>{{ $request->name }}</strong>
                            <small>{{ $request->created_at?->format('M j, g:i A') ?: 'Pending' }}</small>
                            <p>{{ $request->statusLabel() }} in {{ $request->zip_code ?: 'an active market' }}.</p>
                        </article>
                    @empty
                        <article>
                            <strong>No recent seller activity</strong>
                            <p>Requests and buyer interest will show here once the pipeline starts moving.</p>
                        </article>
                    @endforelse
                </div>
            </article>

            <article class="or-dashboard__panel">
                <div class="or-dashboard__surface-header">
                    <div>
                        <span class="eyebrow">Submission Tips</span>
                        <h2>What helps approval</h2>
                    </div>
                </div>

                <div class="or-dashboard__spotlight">
                    <article>
                        <span class="or-dashboard__spotlight-index">01</span>
                        <div>
                            <strong>Use complete property details</strong>
                            <p>Full descriptions, pricing, and accurate location details speed up admin review.</p>
                        </div>
                    </article>
                    <article>
                        <span class="or-dashboard__spotlight-index">02</span>
                        <div>
                            <strong>Add a clean listing image</strong>
                            <p>Visual quality helps the team approve listings faster and present them better publicly.</p>
                        </div>
                    </article>
                    <article>
                        <span class="or-dashboard__spotlight-index">03</span>
                        <div>
                            <strong>Stay available for follow-up</strong>
                            <p>If the team needs clarification, quick replies help move pending submissions into the marketplace.</p>
                        </div>
                    </article>
                </div>
            </article>
        </aside>
    </div>
</section>
@endsection
