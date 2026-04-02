@extends('layouts.app')

@section('content')
<section class="page-hero dashboard-page-hero dashboard-page-hero--seller">
    <div class="container page-hero__content">
        <span class="eyebrow">Seller Workspace</span>
        <h1>Manage listings, buyer interest, and next steps with less friction</h1>
        <p>Keep the details clean, the pricing current, and the handoff to qualified agents simple and easy to trust.</p>
    </div>
</section>

<section class="section dashboard-page dashboard-page--metamorphosis" x-data="{ search: '', filter: 'all' }">
    <div class="container cockpit-grid">
        <!-- Sidebar Navigation (Preserved context) -->
        <aside class="cockpit-side" style="grid-row: span 3;">
            <div class="cockpit-table-card mb-8" style="padding: 1.5rem;">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-orange-800 font-bold">S</div>
                    <div>
                        <span class="eyebrow" style="margin: 0;">Seller Workspace</span>
                        <h2 style="font-size: 1.25rem; margin: 0;">{{ Auth::user()->name }}</h2>
                    </div>
                </div>
                <nav class="dashboard-side-nav" aria-label="Seller dashboard navigation">
                    <a class="is-active" href="{{ route('dashboard.seller') }}">Overview</a>
                    <a href="#intake">Add Property</a>
                    <a href="#tracker">Seller Journey</a>
                    <a href="{{ route('listings') }}">Live Listings</a>
                </nav>
            </div>

            <div class="cockpit-table-card mb-8" style="padding: 1.5rem;">
                <span class="eyebrow">Listing Status</span>
                <h3 class="mb-4">Handoff Success</h3>
                <div class="journey-stage-list">
                    @php($maxSellerStage = max(1, collect($sellerJourney)->max('count')))
                    @foreach($sellerJourney as $stage)
                        <div class="journey-stage-item mb-4">
                            <div class="flex justify-between text-xs mb-1">
                                <strong class="text-gray-700">{{ $stage['label'] }}</strong>
                                <span class="text-gray-400">{{ $stage['count'] }}</span>
                            </div>
                            <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-orange-500" style="width: {{ ($stage['count'] / $maxSellerStage) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="cockpit-table-card" style="padding: 1.5rem; background: var(--color-gateway-brand-bg); color: #fff;">
                <span class="eyebrow" style="color: rgba(255,255,255,0.7);">Market Advice</span>
                <h3 style="color: #fff; margin-bottom: 1rem;">Pricing Optimization</h3>
                <p style="color: rgba(255,255,255,0.8); font-size: 0.9rem; margin-bottom: 1.5rem;">Get a human-reviewed CMA (Comparative Market Analysis) for your property.</p>
                <a href="{{ route('contact') }}" class="button w-full" style="background: var(--color-gateway-accent); border: none;">Request Analysis</a>
            </div>
        </aside>

        <!-- KPI Row -->
        <div class="cockpit-kpi-row">
            <article class="cockpit-kpi-card">
                <span class="eyebrow">Active</span>
                <strong>{{ $sellerStats['active_listings'] }}</strong>
                <p>Live Listings</p>
            </article>
            <article class="cockpit-kpi-card" style="border-color: var(--color-gateway-accent);">
                <span class="eyebrow">Conversations</span>
                <strong>{{ $sellerStats['open_inquiries'] }}</strong>
                <p>Buyer Interest</p>
            </article>
            <article class="cockpit-kpi-card">
                <span class="eyebrow">Adjustments</span>
                <strong>{{ $sellerStats['price_updates'] }}</strong>
                <p>Market Syncs</p>
            </article>
            <article class="cockpit-kpi-card" style="border-color: #10b981;">
                <span class="eyebrow">Matches</span>
                <strong>{{ $sellerStats['buyer_matches'] }}</strong>
                <p>High Intent Hits</p>
            </article>
        </div>

        <main class="cockpit-main">
            <!-- Property Intake -->
            <div id="intake" class="mb-12">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Property Intake</h2>
                    <p class="text-gray-500">Add a new property to the OmniReferral ecosystem.</p>
                </div>

                <div class="cockpit-table-card p-8">
                    <form class="gateway-form grid grid-cols-2 gap-6" method="POST" action="{{ route('properties.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="floating-group col-span-2">
                            <input type="text" name="title" placeholder=" " required>
                            <label>Property Title (e.g. Modern Lakeview Residence)</label>
                        </div>
                        <div class="floating-group">
                            <input type="text" name="location" placeholder=" " required>
                            <label>Market / City (e.g. Dallas, TX)</label>
                        </div>
                        <div class="floating-group">
                            <input type="text" name="zip_code" placeholder=" " required>
                            <label>ZIP Code</label>
                        </div>
                        <div class="floating-group">
                            <select name="property_type" required>
                                <option value="house">House</option>
                                <option value="apartment">Apartment</option>
                                <option value="condo">Condo</option>
                                <option value="commercial">Commercial</option>
                            </select>
                            <label>Property Type</label>
                        </div>
                        <div class="floating-group">
                            <input type="number" name="price" placeholder=" " required>
                            <label>Asking Price ($)</label>
                        </div>
                        <div class="floating-group col-span-2">
                            <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
                        </div>
                        <div class="col-span-2 flex justify-end">
                            <button class="button" type="submit" style="background: var(--color-gateway-brand-bg); padding: 1rem 2rem;">Publish Listing</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Current Listings -->
            <div id="listings">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Active Listings</h2>
                    <a href="{{ route('listings') }}" class="text-blue-700 font-semibold hover:underline">View All &rarr;</a>
                </div>

                <div class="listing-grid listing-grid--dashboard">
                    @forelse($properties as $property)
                        <article class="listing-card" data-animate="up">
                            <div class="relative">
                                <img src="{{ $property->image_url }}" alt="Property" style="height: 180px; object-fit: cover;">
                                <span class="absolute top-4 right-4 status-pill status-pill--active">Active</span>
                            </div>
                            <div class="listing-card__body p-6">
                                <strong class="text-xl" style="color: var(--color-gateway-brand-bg);">${{ number_format($property->price) }}</strong>
                                <h3 style="font-size: 1.1rem; margin: 0.5rem 0 0.25rem;">{{ $property->title }}</h3>
                                <p class="listing-location mb-6 text-sm">{{ $property->location }}</p>
                                
                                <div class="grid grid-cols-2 gap-3">
                                    <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue" style="padding: 0.6rem; font-size: 0.8rem;">Edit</a>
                                    <button class="kebab-trigger">⋮</button>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full">
                            <div class="cockpit-empty-state">
                                <img src="{{ asset('images/illustrations/empty-leads.png') }}" alt="Empty" class="cockpit-empty-illustration">
                                <h3>No listings yet</h3>
                                <p class="text-gray-500">Your property will appear here once you complete the intake form above.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Request Feed -->
            <div id="tracker" class="mt-12">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Inquiry Tracker</h2>
                    <p class="text-gray-500">Real-time interest from buyers and agents.</p>
                </div>

                <div class="cockpit-table-card">
                    <table class="cockpit-table">
                        <thead>
                            <tr>
                                <th>Inquiry Target</th>
                                <th>ZIP / Market</th>
                                <th>Price Context</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sellerRequests as $request)
                                <tr>
                                    <td>
                                        <span class="cockpit-primary-data">{{ $request->name }}</span>
                                        <span class="cockpit-secondary-data">Targeting your listing</span>
                                    </td>
                                    <td>
                                        <span class="cockpit-primary-data">{{ $request->zip_code }}</span>
                                        <span class="cockpit-secondary-data">Market fit check</span>
                                    </td>
                                    <td>
                                        <span class="cockpit-primary-data">{{ $request->asking_price ? '$' . number_format($request->asking_price) : 'Pending' }}</span>
                                        <span class="cockpit-secondary-data">Valuation context</span>
                                    </td>
                                    <td>
                                        <span class="status-pill status-pill--{{ $request->status }}">{{ ucfirst($request->status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-gray-500">No inquiry updates found.</td>
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
