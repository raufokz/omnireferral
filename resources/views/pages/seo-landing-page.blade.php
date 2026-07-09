@extends('layouts.app')

@push('styles')
    @vite('resources/css/modules/seo-landing.css')
@endpush

@php
    $c = $page->content ?? [];
    $city = $page->city;
    $state = $page->state;
    $primaryKwd = $page->primary_keyword;
    $secondaryKeywords = collect($page->getSecondaryKeywordsArray())->filter()->take(6);
    $serviceAreas = collect($page->getServiceAreas())->filter()->values();
    $faqs = collect($page->getFaqs())->filter(fn ($faq) => !empty($faq['question']) && !empty($faq['answer']))->values();
    $assignedProfile = $page->realtorProfile;
    $assignedUser = $assignedProfile?->user;
    $agentDisplayName = $assignedUser?->publicDisplayName() ?: ($c['agent_name'] ?? 'Your ' . $city . ' Real Estate Expert');
    $agentFirstName = trim(explode(' ', $agentDisplayName)[0] ?? 'Agent');
    $agentBrokerage = $assignedProfile?->brokerage_name ?: ($c['agent_title'] ?? 'OmniReferral Partner Network');
    $agentServiceArea = $assignedProfile?->serviceAreaLabel() ?: $city . ', ' . $state;
    $agentSpecialties = collect($assignedProfile?->specialtiesList() ?? [])->filter()->take(7);
    $agentLanguages = collect(preg_split('/,|\r\n|\r|\n/', (string) ($assignedProfile?->languages ?? 'English')))->map(fn ($item) => trim($item))->filter()->take(6)->values();
    $marketAreas = collect(preg_split('/,|\r\n|\r|\n/', (string) ($assignedProfile?->market_areas ?? '')))->map(fn ($item) => trim($item))->filter()->take(8)->values();
    $displayServiceAreas = $serviceAreas->isNotEmpty() ? $serviceAreas : ($marketAreas->isNotEmpty() ? $marketAreas : collect([$city, $agentServiceArea])->unique()->values());
    $rating = $assignedProfile ? number_format((float) $assignedProfile->rating, 1) : '4.9';
    $reviewCount = (int) ($assignedProfile?->review_count ?? 187);
    $yearsExperience = max(2, (int) ($assignedProfile?->years_of_experience ?? 2));
    $closedDeals = max(0, (int) ($assignedProfile?->leads_closed ?? 0));
    $satisfactionRate = $rating >= 4.8 ? '99%' : '97%';
    $heroImage = $page->hero_image ? asset($page->hero_image) : asset('images/home/hero_backdrop_v2.png');
    $profileImage = $page->realtor_photo
        ? asset($page->realtor_photo)
        : ($assignedProfile
            ? $assignedProfile->headshotPublicUrl($assignedUser)
            : asset('images/realtors/logo-bydefault_agent.png'));
    $phoneLabel = $assignedUser?->phone ?: ($c['agent_phone'] ?? 'Contact through OmniReferral');
    $emailLabel = $assignedUser?->email ?: ($c['agent_email'] ?? 'Available by request');
    $websiteLabel = $assignedProfile?->source_url ?: ($c['agent_website'] ?? 'OmniReferral');
    $licenseLabel = $assignedProfile?->license_number ?: 'Verified network agent';
    $publicProfileUrl = $assignedProfile?->isPublicVisible() ? route('agents.profile', $assignedProfile) : null;
    $canonicalUrl = $page->canonical_url ?: url()->current();
    $ogImage = $page->og_image ? asset($page->og_image) : $profileImage;
    $hasBodyContent = !empty($c['body_content'] ?? '');
    $defaultSections = [
        [$c['why_local_heading'] ?? 'Why Work With a Local ' . $city . ' Realtor?', $c['why_local_content'] ?? 'Choosing a local ' . $city . ' real estate agent gives you practical guidance on neighborhood demand, pricing movement, school zones, commute patterns, offer strategy, and seller expectations. The right advisor helps you compare opportunities clearly instead of relying only on listing portals.'],
        [$c['buying_heading'] ?? 'Home Buying Services in ' . $city, $c['buying_content'] ?? $agentFirstName . ' helps buyers understand inventory, tour neighborhoods, compare homes, structure competitive offers, coordinate inspections, and stay organized through closing in the ' . $city . ' market.'],
        [$c['selling_heading'] ?? 'Home Selling Services in ' . $city, $c['selling_content'] ?? 'Selling a home in ' . $city . ' requires accurate pricing, strong presentation, thoughtful launch timing, qualified buyer follow-up, and clear negotiation support from preparation through settlement.'],
        [$c['relocation_heading'] ?? 'Relocation Assistance for ' . $city, $c['relocation_content'] ?? 'Relocation clients receive neighborhood orientation, school and commute context, remote showing coordination, offer guidance, and step-by-step support before and after the move.'],
        [$c['luxury_heading'] ?? $city . ' Luxury Realtor Guidance', $c['luxury_content'] ?? 'Luxury clients need discretion, elevated property positioning, private showing coordination, premium marketing, and a refined negotiation strategy for higher-value homes.'],
        [$c['investment_heading'] ?? $city . ' Investment Property Insight', $c['investment_content'] ?? 'Investors can evaluate rental demand, location fundamentals, renovation considerations, appreciation signals, and local vendor needs before making a purchase decision.'],
    ];
@endphp

@section('head')
    <meta name="robots" content="index, follow, max-image-preview:large">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:type" content="profile">
    <meta property="og:title" content="{{ $page->seo_title }}">
    <meta property="og:description" content="{{ $page->meta_description }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:site_name" content="OmniReferral">
    <meta property="og:locale" content="en_US">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $page->seo_title }}">
    <meta name="twitter:description" content="{{ $page->meta_description }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
@endsection

@section('schema')
@php
    $agentSchema = [
        '@context' => 'https://schema.org',
        '@type' => ['RealEstateAgent', 'LocalBusiness'],
        '@id' => $canonicalUrl . '#agent',
        'name' => $agentDisplayName,
        'image' => $profileImage,
        'url' => $canonicalUrl,
        'telephone' => $assignedUser?->phone ?: null,
        'email' => $assignedUser?->email ?: null,
        'priceRange' => '$$',
        'description' => $page->meta_description,
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => $city,
            'addressRegion' => $state,
            'addressCountry' => 'US',
        ],
        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => $rating,
            'reviewCount' => (string) $reviewCount,
            'bestRating' => '5',
        ],
        'areaServed' => $displayServiceAreas->map(fn ($area) => [
            '@type' => 'Place',
            'name' => $area,
        ])->values()->all(),
        'knowsAbout' => $agentSpecialties->merge($secondaryKeywords)->values()->all(),
    ];

    $schema = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            '@id' => $canonicalUrl . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Agents', 'item' => route('agents.index')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $city . ' Real Estate Agent', 'item' => $canonicalUrl],
            ],
        ],
        array_filter($agentSchema),
        [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            '@id' => $canonicalUrl . '#webpage',
            'url' => $canonicalUrl,
            'name' => $page->seo_title,
            'description' => $page->meta_description,
            'about' => ['@id' => $canonicalUrl . '#agent'],
            'breadcrumb' => ['@id' => $canonicalUrl . '#breadcrumb'],
        ],
    ];

    if ($faqs->isNotEmpty()) {
        $schema[] = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqs->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ])->values()->all(),
        ];
    }
@endphp
@foreach($schema as $schemaItem)
    <script type="application/ld+json">{!! json_encode($schemaItem, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endforeach
@endsection

@section('content')
<main class="seo-profile-page">
    <section class="seo-profile-hero" aria-labelledby="seo-page-title">
        <picture class="seo-profile-hero__bg" aria-hidden="true">
            <img src="{{ $heroImage }}" alt="" loading="eager" fetchpriority="high" width="1600" height="900">
        </picture>

        <div class="container seo-profile-shell">
            <article class="seo-profile-panel">
                <aside class="seo-profile-side" aria-label="{{ $agentDisplayName }} contact details">
                    <img src="{{ $profileImage }}" alt="{{ $agentDisplayName }} headshot" loading="eager" fetchpriority="high" width="360" height="360" onerror="this.onerror=null;this.src='{{ asset('images/realtors/logo-bydefault_agent.png') }}'">

                    <div class="seo-contact-card">
                        <div><span>Phone</span><strong>{{ $phoneLabel }}</strong></div>
                        <div><span>Email</span><strong>{{ $emailLabel }}</strong></div>
                        <div><span>Website</span><strong>{{ $websiteLabel }}</strong></div>
                        <div><span>License</span><strong>{{ $licenseLabel }}</strong></div>
                        <div><span>Service Area</span><strong>{{ $agentServiceArea }}</strong></div>
                    </div>

                    <div class="seo-profile-pills" aria-label="Specialties">
                        @forelse($agentSpecialties as $specialty)
                            <span>{{ $specialty }}</span>
                        @empty
                            <span>Residential</span>
                            <span>Buyer Representation</span>
                            <span>Listing Strategy</span>
                        @endforelse
                    </div>
                </aside>

                <div class="seo-profile-main">
                    <header class="seo-profile-head">
                        <span class="seo-kicker">{{ $city }}, {{ $state }} Real Estate</span>
                        <div class="seo-profile-title-row">
                            <h1 id="seo-page-title" class="visually-hidden">{{ $c['hero_heading'] ?? $primaryKwd . ' in ' . $city . ', ' . $state }}</h1>
                            <span>{{ $assignedProfile?->isFeatured() ? 'Featured Agent' : 'Verified Agent' }}</span>
                        </div>
                        <p class="seo-profile-brokerage">{{ $agentDisplayName }} | {{ $agentBrokerage }}</p>
                        <p class="seo-profile-summary">{{ $c['hero_subheading'] ?? 'Premium local real estate guidance for buyers, sellers, relocation clients, luxury homes, and investment decisions in ' . $city . '.' }}</p>

                        <div class="seo-rating" aria-label="{{ $rating }} out of 5 rating">
                            <strong>{{ $rating }}</strong>
                            <span aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                            <small>{{ number_format($reviewCount) }} reviews</small>
                        </div>
                    </header>

                    <div class="seo-profile-actions">
                        <a href="#contact-form" class="seo-btn seo-btn--orange">Contact Agent</a>
                        <a href="#market-guidance" class="seo-btn seo-btn--blue">Read Market Guide</a>
                        @if($publicProfileUrl)
                            <a href="{{ $publicProfileUrl }}" class="seo-btn seo-btn--ghost">View Directory Profile</a>
                        @endif
                    </div>

                    <section class="seo-profile-metrics" aria-label="Agent performance metrics">
                        <div><strong>{{ $yearsExperience }}+</strong><span>Years Experience</span></div>
                        @if($closedDeals > 0)
                            <div><strong>{{ number_format($closedDeals) }}+</strong><span>Deals Closed</span></div>
                        @else
                            <div><strong>{{ number_format($reviewCount) }}+</strong><span>Client Reviews</span></div>
                        @endif
                        <div><strong>{{ $satisfactionRate }}</strong><span>Client Satisfaction</span></div>
                        <div><strong>{{ $city }}</strong><span>Primary Market</span></div>
                    </section>

                    <section class="seo-profile-section">
                        <h2>About {{ $agentFirstName }}</h2>
                        <div class="seo-rich-copy">
                            @if($assignedProfile?->bio)
                                {!! nl2br(e($assignedProfile->bio)) !!}
                            @elseif(!empty($c['agent_bio']))
                                {!! $c['agent_bio'] !!}
                            @else
                                {{ $agentDisplayName }} provides responsive, market-aware real estate guidance for clients comparing homes, preparing to sell, relocating, or evaluating opportunities across {{ $city }} and surrounding communities.
                            @endif
                        </div>
                    </section>
                </div>
            </article>
        </div>
    </section>

    <section class="seo-profile-content" id="market-guidance">
        <div class="container seo-profile-content__grid">
            <div class="seo-content-column">
                <section class="seo-profile-section seo-profile-section--intro">
                    <span class="seo-kicker">Local SEO Market Guide</span>
                    <h2>{{ $c['market_heading'] ?? 'Real Estate Support Built Around ' . $city . ' Decisions' }}</h2>
                    <p>{{ $c['market_content'] ?? 'The strongest real estate decisions start with local context. This guide explains how ' . $agentDisplayName . ' supports buyers, sellers, relocation clients, luxury clients, and investors who want clear advice in the ' . $city . ' market.' }}</p>
                </section>

                @if($hasBodyContent)
                    <section class="seo-profile-section seo-rich-body">{!! $c['body_content'] !!}</section>
                @else
                    <div class="seo-guide-grid">
                        @foreach($defaultSections as [$heading, $body])
                            <section class="seo-guide-card">
                                <h2>{{ $heading }}</h2>
                                <div>{!! nl2br(e($body)) !!}</div>
                            </section>
                        @endforeach
                    </div>
                @endif

                @if($faqs->isNotEmpty())
                    <section class="seo-profile-section">
                        <span class="seo-kicker">Questions</span>
                        <h2>Frequently Asked Questions About {{ $city }} Real Estate</h2>
                        <div class="seo-faq-stack">
                            @foreach($faqs as $faq)
                                <details class="seo-faq-card">
                                    <summary>{{ $faq['question'] }}</summary>
                                    <p>{{ $faq['answer'] }}</p>
                                </details>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="seo-support-column" aria-label="Local agent details">
                <section class="seo-profile-section">
                    <span class="seo-kicker">Service Areas</span>
                    <h2>Serving {{ $city }} and Nearby Communities</h2>
                    <div class="seo-profile-pills">
                        @foreach($displayServiceAreas as $area)
                            <span>{{ $area }}</span>
                        @endforeach
                    </div>
                </section>

                <section class="seo-profile-section">
                    <span class="seo-kicker">Search Relevance</span>
                    <h2>Popular Local Searches</h2>
                    <div class="seo-profile-pills seo-profile-pills--outline">
                        <span>{{ $primaryKwd }}</span>
                        @foreach($secondaryKeywords as $keyword)
                            <span>{{ $keyword }}</span>
                        @endforeach
                        <span>Best Real Estate Agent Near Me</span>
                        <span>{{ $city }} Luxury Realtor</span>
                    </div>
                </section>

                <section class="seo-profile-section">
                    <span class="seo-kicker">Languages</span>
                    <h2>Client Support</h2>
                    <div class="seo-profile-pills">
                        @foreach($agentLanguages as $language)
                            <span>{{ $language }}</span>
                        @endforeach
                    </div>
                </section>
            </aside>
        </div>
    </section>

    <section class="seo-lead-section" id="contact-form">
        <div class="container seo-lead-grid">
            <div class="seo-lead-copy">
                <span class="seo-kicker">Next Step</span>
                <h2>{{ $c['cta_heading'] ?? 'Ready to Talk With a ' . $city . ' Real Estate Expert?' }}</h2>
                <p>{{ $c['cta_subheading'] ?? 'Share your timeline, location, and goals. OmniReferral routes your request so you can get clear local guidance without pressure.' }}</p>
                <ul>
                    <li>Personalized buyer, seller, relocation, or investment guidance</li>
                    <li>Local pricing insight and neighborhood context</li>
                    <li>Fast follow-up from a verified real estate professional</li>
                </ul>
            </div>

            <div class="seo-lead-card">
                <h2>{{ $c['form_heading'] ?? 'Contact ' . $agentFirstName }}</h2>
                <p>{{ $c['form_subheading'] ?? 'Tell us what you need help with and we will be in touch shortly.' }}</p>

                @if(session('success'))
                    <div class="seo-form-success">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('seo-landing-page.lead', $page->slug) }}" class="seo-lead-form">
                    @csrf
                    <label>
                        <span>Name *</span>
                        <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name">
                        @error('name') <small>{{ $message }}</small> @enderror
                    </label>
                    <label>
                        <span>Email *</span>
                        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                        @error('email') <small>{{ $message }}</small> @enderror
                    </label>
                    <label>
                        <span>Phone *</span>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel">
                        @error('phone') <small>{{ $message }}</small> @enderror
                    </label>
                    <label>
                        <span>I am interested in</span>
                        <select name="interest">
                            <option value="">Select...</option>
                            <option value="buying" @selected(old('interest') === 'buying')>Buying a Home</option>
                            <option value="selling" @selected(old('interest') === 'selling')>Selling a Home</option>
                            <option value="both" @selected(old('interest') === 'both')>Both Buying and Selling</option>
                            <option value="investment" @selected(old('interest') === 'investment')>Investment Property</option>
                            <option value="relocation" @selected(old('interest') === 'relocation')>Relocation</option>
                            <option value="other" @selected(old('interest') === 'other')>Other</option>
                        </select>
                    </label>
                    <label class="seo-lead-form__full">
                        <span>Message</span>
                        <textarea name="message" rows="4">{{ old('message') }}</textarea>
                    </label>
                    <button type="submit" class="seo-btn seo-btn--orange seo-lead-form__full">{{ $c['form_submit_text'] ?? 'Send Message' }}</button>
                </form>
            </div>
        </div>
    </section>
</main>
@endsection
