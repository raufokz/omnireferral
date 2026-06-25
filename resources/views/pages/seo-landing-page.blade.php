@extends('layouts.app')

@push('styles')
    @vite('resources/css/modules/seo-landing.css')
@endpush

@php
    $c = $page->content ?? [];
    $city = $page->city;
    $state = $page->state;
    $primaryKwd = $page->primary_keyword;
    $serviceAreas = $page->getServiceAreas();
    $faqs = $page->getFaqs();
    $assignedProfile = $page->realtorProfile;
    $assignedUser = $assignedProfile?->user;
    $agentDisplayName = $assignedUser?->publicDisplayName() ?: ($c['agent_name'] ?? 'Your ' . $city . ' Real Estate Expert');
    $agentBrokerage = $assignedProfile?->brokerage_name ?: ($c['agent_title'] ?? 'OmniReferral Partner Network');
    $agentServiceArea = $assignedProfile?->serviceAreaLabel();
    $agentSpecialties = collect($assignedProfile?->specialtiesList() ?? [])->take(5);
    $heroImage = $page->hero_image ? asset($page->hero_image) : asset('images/home/hero_backdrop_v2.png');
    $profileImage = $assignedProfile
        ? $assignedProfile->headshotPublicUrl($assignedUser)
        : asset('images/realtors/logo-bydefault_agent.png');
@endphp

@section('head')
    <link rel="canonical" href="{{ $page->canonical_url ?: url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $page->seo_title }}">
    <meta property="og:description" content="{{ $page->meta_description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $page->og_image ? asset($page->og_image) : $heroImage }}">
    <meta property="og:site_name" content="OmniReferral">
    <meta property="og:locale" content="en_US">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $page->seo_title }}">
    <meta name="twitter:description" content="{{ $page->meta_description }}">
    <meta name="twitter:image" content="{{ $page->og_image ? asset($page->og_image) : $heroImage }}">
@endsection

@section('schema')
@php
    $schema = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $city . ' Real Estate', 'item' => url()->current()],
            ],
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'RealEstateAgent',
            'name' => $agentDisplayName,
            'image' => $profileImage,
            'url' => url()->current(),
            'telephone' => $assignedUser?->phone ?: '+1-512-555-0100',
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
                'ratingValue' => $assignedProfile ? number_format((float) $assignedProfile->rating, 1) : '4.9',
                'reviewCount' => (string) ($assignedProfile?->review_count ?? 187),
                'bestRating' => '5',
            ],
            'areaServed' => [
                '@type' => 'City',
                'name' => $city,
            ],
        ],
    ];

    if (count($faqs)) {
        $schema[] = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq['question'] ?? '',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'] ?? '',
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
@php
    $hasBodyContent = !empty($c['body_content'] ?? '');
    if (!$hasBodyContent) {
        $contentSections = [
            [$c['why_local_heading'] ?? 'Why Work With a Local ' . $city . ' Realtor?', $c['why_local_content'] ?? 'A local real estate agent brings hyper-local expertise that online searches cannot match. From school districts and commute patterns to neighborhood appreciation and pricing strategy, a ' . $city . '-based agent gives you practical insight at every step.'],
            [$c['buying_heading'] ?? 'Home Buying Services in ' . $city, $c['buying_content'] ?? 'Finding the right home in ' . $city . ' takes more than browsing listings. We help with property searches, neighborhood tours, financing coordination, offer strategy, inspection support, and closing guidance.'],
            [$c['selling_heading'] ?? 'Home Selling Services in ' . $city, $c['selling_content'] ?? 'Selling your ' . $city . ' home requires pricing clarity, strong marketing, skilled negotiation, and organized transaction management from listing through closing.'],
            [$c['relocation_heading'] ?? 'Relocation Assistance for ' . $city, $c['relocation_content'] ?? 'Moving to ' . $city . ' is easier with area consultations, neighborhood matching, school research, service setup guidance, and steady support until you are settled.'],
            [$c['luxury_heading'] ?? $city . ' Luxury Home Expertise', $c['luxury_content'] ?? 'Luxury clients need discretion, stronger presentation, private showing coordination, and elevated market strategy.'],
            [$c['investment_heading'] ?? $city . ' Investment Property Guidance', $c['investment_content'] ?? 'Investors receive help with market selection, rental demand, cash-flow review, appreciation potential, and local vendor connections.'],
            [$c['market_heading'] ?? $city . ' Local Market Knowledge', $c['market_content'] ?? 'Stay current on pricing, inventory, seasonal patterns, new construction, and neighborhood-level trends.'],
        ];
    }
@endphp

<main class="seo-page-shell">
    <section class="hero hero--premium seo-page-hero" aria-labelledby="seo-page-title">
        <div class="hero__backdrop">
            <img src="{{ $heroImage }}" alt="{{ $city }} real estate market" class="hero__backdrop-img" loading="eager">
            <div class="hero__backdrop-overlay"></div>
        </div>

        <div class="container seo-page-hero__layout">
            <div class="seo-page-hero__copy">
                <span class="eyebrow">{{ $city }}, {{ $state }} Real Estate</span>
                <h1 id="seo-page-title">{{ $c['hero_heading'] ?? $primaryKwd . ' in ' . $city . ', ' . $state }}</h1>
                <p>{{ $c['hero_subheading'] ?? 'Local real estate guidance for buying, selling, relocation, luxury homes, and investment opportunities.' }}</p>
                <div class="hero__actions hero__actions--spacious">
                    <a href="#contact-form" class="button button--orange">Contact a {{ $city }} Expert</a>
                    @if($assignedProfile?->isPublicVisible())
                        <a href="{{ route('agents.profile', $assignedProfile) }}" class="button button--ghost-light">View Realtor Profile</a>
                    @endif
                </div>
            </div>

            <aside class="seo-agent-card">
                <img src="{{ $profileImage }}" alt="{{ $agentDisplayName }} headshot" loading="lazy">
                <div>
                    <span class="eyebrow">Featured Realtor</span>
                    <h2>{{ $agentDisplayName }}</h2>
                    <p>{{ $agentBrokerage }}</p>
                </div>
                <dl>
                    <div><dt>Rating</dt><dd>{{ $assignedProfile ? number_format((float) $assignedProfile->rating, 1) : '4.9' }}</dd></div>
                    <div><dt>Reviews</dt><dd>{{ $assignedProfile?->review_count ?? '187' }}</dd></div>
                    <div><dt>Area</dt><dd>{{ $agentServiceArea ?: $city . ', ' . $state }}</dd></div>
                </dl>
            </aside>
        </div>
    </section>

    <section class="section section--light seo-section">
        <div class="container seo-agent-feature">
            <div class="seo-agent-feature__copy">
                <span class="eyebrow">Local Representation</span>
                <h2>{{ $agentDisplayName }}</h2>
                <p class="seo-agent-feature__brokerage">{{ $agentBrokerage }}</p>

                @if($assignedProfile)
                    <div class="seo-chip-row">
                        <span>{{ number_format((float) $assignedProfile->rating, 1) }} rating</span>
                        <span>{{ $assignedProfile->review_count }} reviews</span>
                        @if($agentServiceArea)<span>{{ $agentServiceArea }}</span>@endif
                    </div>
                    <div class="seo-rich-copy">{!! $assignedProfile->bio ? nl2br(e($assignedProfile->bio)) : ($c['agent_bio'] ?? 'This local OmniReferral partner is ready to support buyers and sellers with responsive, market-aware guidance.') !!}</div>
                @elseif(!empty($c['agent_bio']))
                    <div class="seo-rich-copy">{!! $c['agent_bio'] !!}</div>
                @else
                    <p>With years of dedicated service in the {{ $city }} metro area, our team brings local market knowledge, negotiation expertise, and a client-first approach to every transaction.</p>
                @endif

                @if($agentSpecialties->isNotEmpty())
                    <div class="seo-outline-chip-row">
                        @foreach($agentSpecialties as $specialty)
                            <span>{{ $specialty }}</span>
                        @endforeach
                    </div>
                @endif

                @if($assignedProfile?->isPublicVisible())
                    <a href="{{ route('agents.profile', $assignedProfile) }}" class="button button--ghost-blue">View Full Realtor Profile</a>
                @endif
            </div>
            <div class="seo-agent-feature__media">
                <img src="{{ $profileImage }}" alt="{{ $agentDisplayName }} profile image" loading="lazy">
            </div>
        </div>
    </section>

    <section class="section section--gray seo-section">
        <div class="container">
            @if($hasBodyContent)
                <div class="seo-rich-body">{!! $c['body_content'] !!}</div>
            @else
                <div class="section-heading seo-section__heading">
                    <span class="eyebrow">Market Guidance</span>
                    <h2>Real estate support built around {{ $city }} decisions</h2>
                    <p>Each section can be edited from the admin portal, while the page keeps the same site-wide visual system.</p>
                </div>
                <div class="seo-content-grid">
                    @foreach($contentSections as [$heading, $body])
                        <article class="seo-content-card">
                            <h3>{{ $heading }}</h3>
                            <div>{!! nl2br(e($body)) !!}</div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if(count($serviceAreas))
        <section class="section section--light seo-section">
            <div class="container">
                <div class="section-heading seo-section__heading">
                    <span class="eyebrow">Service Areas</span>
                    <h2>Serving {{ $city }} and nearby communities</h2>
                </div>
                <div class="seo-chip-row seo-chip-row--center">
                    @foreach($serviceAreas as $area)
                        <span>{{ $area }}</span>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if(count($faqs))
        <section class="section section--gray seo-section">
            <div class="container container-sm">
                <div class="section-heading seo-section__heading">
                    <span class="eyebrow">Questions</span>
                    <h2>Frequently asked questions about {{ $city }} real estate</h2>
                </div>
                <div class="seo-faq-stack">
                    @foreach($faqs as $faq)
                        <details class="seo-faq-card">
                            <summary>{{ $faq['question'] }}</summary>
                            <p>{{ $faq['answer'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section seo-contact-section" id="contact-form">
        <div class="container seo-contact-grid">
            <div class="seo-contact-copy">
                <span class="eyebrow">Next Step</span>
                <h2>{{ $c['cta_heading'] ?? 'Ready to Find Your Dream Home in ' . $city . '?' }}</h2>
                <p>{{ $c['cta_subheading'] ?? 'Fill out the form and a local real estate expert will reach out within 24 hours to discuss your goals and help you take the next step.' }}</p>
                <ul class="feature-list compact">
                    <li>Personalized home search tailored to you</li>
                    <li>Expert negotiation and market insights</li>
                    <li>Dedicated support from start to close</li>
                    <li>No obligation, just real answers</li>
                </ul>
            </div>

            <div class="seo-contact-card">
                <h3>{{ $c['form_heading'] ?? 'Contact a ' . $city . ' Expert' }}</h3>
                <p>{{ $c['form_subheading'] ?? 'Fill out the form below and we will be in touch shortly.' }}</p>

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
                    <label>
                        <span>Message</span>
                        <textarea name="message" rows="4">{{ old('message') }}</textarea>
                    </label>
                    <button type="submit" class="button button--orange w-full">{{ $c['form_submit_text'] ?? 'Send Message' }}</button>
                </form>
            </div>
        </div>
    </section>
</main>
@endsection
