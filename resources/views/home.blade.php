@extends('layouts.app')

@push('styles')
    @vite('resources/css/modules/home.css')
@endpush

@section('content')
@php
    $publishedTestimonials = \App\Models\Testimonial::published()
        ->orderByDesc('is_featured')
        ->orderBy('sort_order')
        ->latest()
        ->get();

    $dbHomepageTestimonials = $publishedTestimonials
        ->take(9)
        ->map(function ($testimonial) {
            return [
                'quote' => $testimonial->quote,
                'name' => $testimonial->name,
                'role' => $testimonial->company ?: ($testimonial->audience_label . ' Client'),
                'location' => $testimonial->location ?: 'OmniReferral Network',
                'path' => $testimonial->photo_url,
                'audience' => $testimonial->audience_label,
                'has_video' => $testimonial->has_video,
            ];
        });

    $fallbackHomepageTestimonials = collect([
        [
            'quote' => 'The buyer path felt organized from day one. We never felt lost, and every next step came with real clarity.',
            'name' => 'Ariana Holt',
            'role' => 'Buyer Client',
            'location' => 'Dallas, TX',
            'path' => asset('images/reviews/review-1.svg'),
            'audience' => 'Buyer',
            'has_video' => false,
        ],
        [
            'quote' => 'Our seller lead was handled with more professionalism than we expected. The team moved fast and kept communication clean.',
            'name' => 'Marcus Dean',
            'role' => 'Seller Client',
            'location' => 'Charlotte, NC',
            'path' => asset('images/reviews/review-2.svg'),
            'audience' => 'Seller',
            'has_video' => false,
        ],
        [
            'quote' => 'The context on each lead changed our first call quality immediately. Our agents spend less time sorting and more time converting.',
            'name' => 'Jordan Miles',
            'role' => 'Broker | Miles Realty Group',
            'location' => 'Austin, TX',
            'path' => asset('images/reviews/review-3.svg'),
            'audience' => 'Agent',
            'has_video' => false,
        ],
        [
            'quote' => 'We loved that the seller experience still felt premium even before we were matched. It made the whole journey easier to trust.',
            'name' => 'Nina Foster',
            'role' => 'Seller Client',
            'location' => 'Phoenix, AZ',
            'path' => asset('images/reviews/review-4.svg'),
            'audience' => 'Seller',
            'has_video' => false,
        ],
        [
            'quote' => 'OmniReferral feels more like an operating partner than a lead vendor. The packaging and handoff are much sharper.',
            'name' => 'Chris Everett',
            'role' => 'Investor Advisor | Everett Homes',
            'location' => 'Phoenix, AZ',
            'path' => asset('images/reviews/review-1.svg'),
            'audience' => 'Agent',
            'has_video' => false,
        ],
        [
            'quote' => 'The intake questions were simple, but the follow-through felt highly personalized. That balance really stood out for us.',
            'name' => 'Leah Monroe',
            'role' => 'Buyer Client',
            'location' => 'Tampa, FL',
            'path' => asset('images/reviews/review-2.svg'),
            'audience' => 'Buyer',
            'has_video' => false,
        ],
    ]);

    $homepageTestimonials = ($dbHomepageTestimonials->count() >= 4
        ? $dbHomepageTestimonials
        : $dbHomepageTestimonials->concat($fallbackHomepageTestimonials))
        ->unique('name')
        ->take(9)
        ->values();

    $homepageFeaturedTestimonial = $homepageTestimonials->first();
    $homepageCarouselTestimonials = $homepageTestimonials->skip(1)->values();

    if ($homepageCarouselTestimonials->isEmpty() && $homepageFeaturedTestimonial) {
        $homepageCarouselTestimonials = collect([$homepageFeaturedTestimonial]);
    }

    $homepageAudienceCounts = [
        'buyer' => $publishedTestimonials->where('audience', 'buyer')->count(),
        'seller' => $publishedTestimonials->where('audience', 'seller')->count(),
        'agent' => $publishedTestimonials->where('audience', 'agent')->count(),
    ];

    $homepageAverageRating = number_format((float) ($publishedTestimonials->avg('rating') ?: 5), 1);
    $homepageMarketplaceProperties = collect($properties)
        ->filter(fn ($property) => $property->approval_status === \App\Models\Property::APPROVAL_APPROVED && $property->status === 'Active')
        ->values();

    $partnerLogoRows = [
        $partnerLogos->values(),
        $partnerLogos->reverse()->values(),
    ];
@endphp
<div class="homepage-shell homepage-shell--refined">
    {{-- Hero Section --}}
    <section class="hero hero--premium homepage-hero homepage-hero--minimal homepage-hero--with-image" aria-labelledby="hero-headline">
        <div class="hero__backdrop"></div>
        <div class="container hero__content hero__content--premium homepage-hero__layout">
            <div class="hero__copy hero__copy--premium homepage-hero__copy" data-reveal="left">
                <span class="eyebrow">Premium Real Estate Lead Engine</span>
                <h1 id="hero-headline">Buyer &amp; Seller Leads.<br>Faster Closings.</h1>
                <p>AI + ISA screening, smart routing, and conversion-first delivery built for modern teams.</p>

                <div class="hero__actions hero__actions--spacious">
                    <a href="#lead-forms" class="button button--orange">Get Started</a>
                    <a href="{{ route('pricing') }}" class="button button--ghost-light">View Leads</a>
                </div>

                <div class="hero__trust-row" data-stagger>
                    <div class="hero-trust-chip">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2 4 5v6c0 5 3.5 9.74 8 11 4.5-1.26 8-6 8-11V5l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                        <span>Verified intake</span>
                    </div>
                    <div class="hero-trust-chip">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h4l2 5 5-10 2 5h7"/></svg>
                        <span>ZIP-based routing</span>
                    </div>
                    <div class="hero-trust-chip">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5h18"/><path d="M6 12h12"/><path d="M9 19h6"/></svg>
                        <span>Dashboard delivery</span>
                    </div>
                </div>
            </div>

            <div class="search-card hero-search-card hero-search-card--premium homepage-hero__form" data-animate="right" id="lead-forms">
                <div class="hero-search-card__header">
                    <span class="eyebrow">Start Your Request</span>
                    <h2>Launch your lead request</h2>
                    <p>Pick Buyer or Seller and complete the short step flow.</p>
                </div>

                <div class="tab-switcher hero-tabs" role="tablist" aria-label="Lead type">
                    <button class="tab-switcher__button is-active" id="hero-tab-buyer" type="button" role="tab" aria-selected="true" aria-controls="hero-panel-buyer" tabindex="0" data-tab-trigger="buyer">Buyer</button>
                    <button class="tab-switcher__button" id="hero-tab-seller" type="button" role="tab" aria-selected="false" aria-controls="hero-panel-seller" tabindex="-1" data-tab-trigger="seller">Seller</button>
                </div>

                {{-- Buyer Panel --}}
                <div class="tab-panel is-active" id="hero-panel-buyer" role="tabpanel" aria-labelledby="hero-tab-buyer" data-tab-panel="buyer">
                    <form class="hero-form hero-form--buyer-home" method="POST" action="{{ route('leads.store') }}" data-multi-step novalidate>
                        @csrf
                        <input type="hidden" name="intent" value="buyer">

                        <div class="form-progress">
                            <div class="form-progress-bar"></div>
                        </div>

                        <div class="form-step is-active">
                            <div class="hero-form-step-meta">
                                <span>Step 1 of 4</span>
                                <strong>Contact Details</strong>
                            </div>
                            <div class="form-intro hero-form-intro">
                                <h2>Find buyer-ready opportunities</h2>
                                <p>Start with your details and we will connect you to the right local path.</p>
                            </div>
                            <div class="hero-form__grid">
                                <label><span>Full name</span><input type="text" name="name" placeholder="Taylor Morgan" required autocomplete="name"></label>
                                <label><span>Email address</span><input type="email" name="email" placeholder="you@example.com" required autocomplete="email"></label>
                                <label><span>Phone number</span><input type="tel" name="phone" placeholder="(555) 123-4567" required autocomplete="tel" inputmode="tel"></label>
                            </div>
                            <div class="hero-form__footer">
                                <button type="button" class="button button--orange" data-form-next>Continue</button>
                            </div>
                        </div>

                        <div class="form-step">
                            <div class="hero-form-step-meta">
                                <span>Step 2 of 4</span>
                                <strong>Market Details</strong>
                            </div>
                            <div class="form-intro hero-form-intro">
                                <h2>Where are you looking?</h2>
                                <p>We use your location, budget, and timing to route you intelligently.</p>
                            </div>

                            <div class="hero-map-card">
                                <div class="hero-map" id="hero-map" aria-label="Lead search map">Map preview - ZIP routing</div>
                                <div class="hero-map-overlay">
                                    <div class="hero-map-overlay__item">
                                        <strong>ZIP</strong>
                                        <span id="buyer-zip-display">Enter ZIP</span>
                                    </div>
                                    <div class="hero-map-overlay__item">
                                        <strong>Result</strong>
                                        <span id="buyer-zip-status">Awaiting input</span>
                                    </div>
                                </div>
                            </div>

                            <div class="hero-form__grid hero-form__grid--buyer-home">
                                <input type="hidden" name="package_slug" value="quick-leads">
                                <div class="buyer-zip-card hero-form__full">
                                    <div class="buyer-zip-card__header">
                                        <span class="buyer-zip-card__eyebrow">Target Market</span>
                                        <h3>Choose your first target ZIP code</h3>
                                        <p>Start with one market and we will widen the search area later if you want nearby neighborhoods too.</p>
                                    </div>
                                    <label class="buyer-zip-card__field">
                                        <span>Preferred ZIP code</span>
                                        <input type="text" name="zip_code" placeholder="75201" data-market-zip inputmode="numeric" maxlength="10" required pattern="^\d{5}(?:-\d{4})?$">
                                    </label>
                                    <small class="buyer-zip-card__hint">Use a valid 5-digit ZIP code to help our team route your request accurately.</small>
                                </div>
                                <label><span>Property type</span><select name="property_type"><option value="">Select type</option><option>House</option><option>Apartment</option><option>Condo</option><option>Commercial</option></select></label>
                            </div>
                            <div class="hero-form__footer">
                                <button type="button" class="button button--ghost" data-form-prev>Back</button>
                                <button type="button" class="button button--orange" data-form-next>Continue</button>
                            </div>
                        </div>

                        <div class="form-step">
                            <div class="hero-form-step-meta">
                                <span>Step 3 of 4</span>
                                <strong>Budget &amp; Timing</strong>
                            </div>
                            <div class="form-intro hero-form-intro">
                                <h2>Set your buyer criteria</h2>
                                <p>These details help us route the request to the right specialist faster.</p>
                            </div>
                            <div class="hero-form__grid hero-form__grid--buyer-home">
                                <label><span>Budget range</span><input type="number" name="budget" placeholder="450000" min="0" inputmode="numeric"></label>
                                <label><span>Timeline</span><select name="timeline"><option value="">Select timing</option><option>ASAP</option><option>0-30 days</option><option>1-3 months</option><option>3-6 months</option><option>Exploring</option></select></label>
                                <label><span>Financing status</span><select name="financing_status"><option value="">Select status</option><option>Cash buyer</option><option>Pre-approved</option><option>Need financing guidance</option><option>Just exploring</option></select></label>
                            </div>
                            <div class="hero-form__footer">
                                <button type="button" class="button button--ghost" data-form-prev>Back</button>
                                <button type="button" class="button button--orange" data-form-next>Continue</button>
                            </div>
                        </div>

                        <div class="form-step">
                            <div class="hero-form-step-meta">
                                <span>Step 4 of 4</span>
                                <strong>Preferences &amp; Contact</strong>
                            </div>
                            <div class="form-intro hero-form-intro">
                                <h2>Finalize your request</h2>
                                <p>Tell us your preferred contact style and anything else your agent should know.</p>
                            </div>
                            <div class="hero-form__grid hero-form__grid--buyer-home">
                                <label><span>Preferred contact</span><select name="contact_preference"><option value="">Choose contact method</option><option>Email</option><option>Phone</option><option>Text</option></select></label>
                                <label class="hero-form__full"><span>Preferences &amp; constraints</span><textarea name="preferences" rows="2" placeholder="Tell us about your ideal home, timing, renovation concerns, and must-haves."></textarea></label>
                            </div>
                            <div class="hero-form__footer">
                                <button type="button" class="button button--ghost" data-form-prev>Back</button>
                                <button type="submit" class="button button--orange">Request Buyer Match</button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Seller Panel --}}
                <div class="tab-panel" id="hero-panel-seller" role="tabpanel" aria-labelledby="hero-tab-seller" data-tab-panel="seller" hidden>
                    <form class="hero-form hero-form--seller-home" method="POST" action="{{ route('leads.store') }}" enctype="multipart/form-data" data-multi-step novalidate>
                        @csrf
                        <input type="hidden" name="intent" value="seller">

                        <div class="form-progress">
                            <div class="form-progress-bar"></div>
                        </div>

                        <div class="form-step is-active">
                            <div class="hero-form-step-meta">
                                <span>Step 1 of 4</span>
                                <strong>Contact Details</strong>
                            </div>
                            <div class="form-intro hero-form-intro">
                                <h2>Share a seller opportunity</h2>
                                <p>Give us the contact details first and we will handle the structured handoff.</p>
                            </div>
                            <div class="hero-form__grid">
                                <label><span>Full name</span><input type="text" name="name" placeholder="Jamie Carter" required autocomplete="name"></label>
                                <label><span>Email address</span><input type="email" name="email" placeholder="jamie@example.com" required autocomplete="email"></label>
                                <label><span>Phone number</span><input type="tel" name="phone" placeholder="(555) 987-6543" required autocomplete="tel" inputmode="tel"></label>
                            </div>
                            <div class="hero-form__footer">
                                <button type="button" class="button button--orange" data-form-next>Continue</button>
                            </div>
                        </div>

                        <div class="form-step">
                            <div class="hero-form-step-meta">
                                <span>Step 2 of 4</span>
                                <strong>Property Basics</strong>
                            </div>
                            <div class="form-intro hero-form-intro">
                                <h2>Tell us about the property</h2>
                                <p>Use the full property address so our team can review the opportunity with better market context.</p>
                            </div>
                            <div class="hero-form__grid">
                                <input type="hidden" name="package_slug" value="power-leads">
                                <label class="hero-form__full"><span>Property address</span><input type="text" name="property_address" placeholder="123 Main St, Dallas, TX 75201" required autocomplete="street-address"></label>
                                <label><span>Property type</span><select name="property_type"><option value="">Select type</option><option>House</option><option>Apartment</option><option>Condo</option><option>Commercial</option></select></label>
                            </div>
                            <div class="hero-form__footer">
                                <button type="button" class="button button--ghost" data-form-prev>Back</button>
                                <button type="button" class="button button--orange" data-form-next>Continue</button>
                            </div>
                        </div>

                        <div class="form-step">
                            <div class="hero-form-step-meta">
                                <span>Step 3 of 4</span>
                                <strong>Pricing &amp; Timeline</strong>
                            </div>
                            <div class="form-intro hero-form-intro">
                                <h2>Confirm your sale timeline</h2>
                                <p>This helps our team prioritize and match the right buyer and outreach motion.</p>
                            </div>
                            <div class="hero-form__grid">
                                <label><span>Asking price</span><input type="number" name="asking_price" placeholder="625000" min="0" inputmode="numeric"></label>
                                <label><span>Timeline</span><select name="timeline"><option value="">Select timing</option><option>ASAP</option><option>0-30 days</option><option>1-3 months</option><option>3-6 months</option><option>Exploring options</option></select></label>
                                <label><span>Financing / deal constraints</span><select name="financing_status"><option value="">Select status</option><option>Need pricing help</option><option>As-is sale</option><option>Open to renovations</option><option>Need quick close</option></select></label>
                            </div>
                            <div class="hero-form__footer">
                                <button type="button" class="button button--ghost" data-form-prev>Back</button>
                                <button type="button" class="button button--orange" data-form-next>Continue</button>
                            </div>
                        </div>

                        <div class="form-step">
                            <div class="hero-form-step-meta">
                                <span>Step 4 of 4</span>
                                <strong>Final Handoff</strong>
                            </div>
                            <div class="form-intro hero-form-intro">
                                <h2>Finish the seller handoff</h2>
                                <p>Share final details so the ISA team can verify and route your lead correctly.</p>
                            </div>
                            <div class="hero-form__grid">
                                <label><span>Preferred contact</span><select name="contact_preference"><option value="">Choose contact method</option><option>Email</option><option>Phone</option><option>Text</option></select></label>
                                <label class="hero-form__full"><span>Upload property image</span><input type="file" name="property_image" accept="image/*"></label>
                                <label class="hero-form__full"><span>Property details</span><textarea name="preferences" rows="2" placeholder="Describe the home, your timeline, repair needs, and anything our team should know."></textarea></label>
                            </div>
                            <div class="hero-form__footer">
                                <button type="button" class="button button--ghost" data-form-prev>Back</button>
                                <button type="submit" class="button button--orange">Submit Seller Lead</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats Strip --}}
    <div class="stat-strip" aria-label="Platform statistics">
        <div class="container stat-strip__grid">
            <div class="stat-strip__item" data-counter="3200" data-suffix="+"><span class="stat-strip__number">3,200+</span><span class="stat-strip__label">Qualified leads delivered</span></div>
            <div class="stat-strip__item" data-counter="450" data-suffix="+"><span class="stat-strip__number">450+</span><span class="stat-strip__label">Active agent partners</span></div>
            <div class="stat-strip__item" data-counter="92" data-suffix="%"><span class="stat-strip__number">92%</span><span class="stat-strip__label">Client satisfaction</span></div>
            <div class="stat-strip__item" data-counter="48" data-suffix=" hr"><span class="stat-strip__number">48 hr</span><span class="stat-strip__label">Average routing window</span></div>
        </div>
    </div>

    {{-- About Section --}}
    <section class="section section--light homepage-section homepage-section--about" aria-labelledby="about-omnireferral-heading" data-animate>
        <div class="container two-column about-layout homepage-about-grid">
            <div class="homepage-about-copy" data-animate="left">
                <span class="eyebrow">Why OmniReferral</span>
                <h2 id="about-omnireferral-heading">A modern lead generation system built around trust, clarity, and faster handoffs.</h2>
                <p>OmniReferral is designed for the full journey, from the first qualification call to the final delivery inside an agent dashboard. Buyers and sellers get clearer next steps. Agents get cleaner opportunities. Internal teams get a workflow they can actually manage.</p>
                <ul class="feature-list compact">
                    <li>Role-based workflows for ISA, sales, admins, buyers, sellers, and agents.</li>
                    <li>Structured package routing so lead quality matches the right growth tier.</li>
                    <li>Conversion-focused forms and onboarding flows that feel guided, not overwhelming.</li>
                </ul>
            </div>
            <div class="about-home-visual" data-animate="right">
                <div class="about-home-visual__image-wrap">
                    <img src="{{ asset('images/about/about-omnireferral.svg') }}" alt="Illustration representing OmniReferral's coordinated lead routing platform" loading="lazy">
                </div>
                <div class="about-home-visual__note">
                    <h3>Designed to remove friction</h3>
                    <p>One platform for qualification, assignment, onboarding, and follow-up makes the entire referral journey easier to understand.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- How It Works --}}
    <section class="section section--gray homepage-section homepage-section--workflow" id="how-it-works" aria-labelledby="how-it-works-heading" data-animate>
        <div class="container">
            <div class="section-heading homepage-section__heading" data-animate="left">
                <span class="eyebrow">How It Works</span>
                <h2 id="how-it-works-heading">One coordinated workflow from cold outreach to closed deal</h2>
                <p>OmniReferral gives every team a defined role, so buyers and sellers move through a cleaner experience and agents get more useful context at delivery.</p>
            </div>
            <div class="timeline-flow homepage-timeline" data-stagger>
                <div class="work-step" data-animate="up">
                    <div class="work-step__number">1</div>
                    <div class="work-step__visual">
                        <img src="{{ asset('images/illustrations/verification.png') }}" alt="Verification Illustration" loading="lazy">
                    </div>
                    <div class="work-step__content">
                        <span class="work-step__role">ISA Team</span>
                        <h3>Qualify every conversation</h3>
                        <p>Inside sales agents verify budget, location, intent, and timing before a lead ever reaches the next team, ensuring zero wasted effort.</p>
                        <div class="work-step__pills">
                            <span>Intent confirmed</span>
                            <span>Budget & timeline verified</span>
                            <span>Human quality review</span>
                        </div>
                    </div>
                </div>

                <div class="work-step" data-animate="up">
                    <div class="work-step__number">2</div>
                    <div class="work-step__visual">
                        <img src="{{ asset('images/illustrations/matching.png') }}" alt="Matching Illustration" loading="lazy">
                    </div>
                    <div class="work-step__content">
                        <span class="work-step__role">Sales Team</span>
                        <h3>Match the right package</h3>
                        <p>Sales executives package each lead into the right Starter, Growth, or Elite path so the delivery matches the urgency and value of your team's capacity.</p>
                        <div class="work-step__pills">
                            <span>Lead tier assigned</span>
                            <span>ZIP & market fit checked</span>
                            <span>Optional VA support layered</span>
                        </div>
                    </div>
                </div>

                <div class="work-step" data-animate="up">
                    <div class="work-step__number">3</div>
                    <div class="work-step__visual">
                        <img src="{{ asset('images/illustrations/conversion.png') }}" alt="Conversion Illustration" loading="lazy">
                    </div>
                    <div class="work-step__content">
                        <span class="work-step__role">Agent Network</span>
                        <h3>Deliver and close faster</h3>
                        <p>Agents receive structured details inside their premium cockpit so they can act quickly, follow up with confidence, and keep momentum at the point of interest.</p>
                        <div class="work-step__pills">
                            <span>Cockpit handoff</span>
                            <span>Lead context at touch</span>
                            <span>Clear next-step support</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Services / Features --}}
    <section class="section section--light homepage-section homepage-section--services" aria-labelledby="services-heading" data-animate>
        <div class="container">
            <div class="section-heading homepage-section__heading" data-animate="right">
                <span class="eyebrow">Built For Growth</span>
                <h2 id="services-heading">Everything needed to keep lead quality high and operational friction low</h2>
                <p>The platform is designed to feel premium on the front end and organized behind the scenes, so every role sees what matters next.</p>
            </div>
            <div class="why-grid homepage-why-grid" data-stagger>
                <article class="feature-icon-card">
                    <div class="feature-icon-card__icon">QL</div>
                    <h3>Qualified lead delivery</h3>
                    <p>Every request is shaped into cleaner data before it gets routed to an eligible realtor.</p>
                </article>
                <article class="feature-icon-card">
                    <div class="feature-icon-card__icon">PK</div>
                    <h3>Package logic that makes sense</h3>
                    <p>Starter, Growth, and Elite are easy to understand and mapped to real growth stages.</p>
                </article>
                <article class="feature-icon-card">
                    <div class="feature-icon-card__icon">DB</div>
                    <h3>Dashboards that stay actionable</h3>
                    <p>Agents, admins, buyers, and sellers all get clearer status visibility without extra noise.</p>
                </article>
                <article class="feature-icon-card">
                    <div class="feature-icon-card__icon">VA</div>
                    <h3>Optional support built in</h3>
                    <p>Virtual assistant and onboarding support help teams scale without losing responsiveness.</p>
                </article>
            </div>
        </div>
    </section>

    {{-- Pricing Preview --}}
    <!-- <section class="section section--gray homepage-section homepage-section--pricing" id="pricing-preview" aria-labelledby="pricing-preview-heading" data-animate>
        <div class="container">
            <div class="section-heading homepage-section__heading" data-animate="left">
                <span class="eyebrow">Pricing Snapshot</span>
                <h2 id="pricing-preview-heading">Choose a lead package that matches your growth stage</h2>
                <p>Each plan is positioned to make the next move obvious, whether you are testing a market or scaling a high-performing team.</p>
            </div>

            @include('partials.pricing-plan-switcher', [
                'pricingPlans' => $pricingPlans,
                'toggleGroup' => 'home',
                'leadActionUrl' => route('contact'),
            ])
        </div>
    </section> -->

    <section class="section section--light homepage-section homepage-section--featured" aria-labelledby="featured-listings-heading" data-animate>
        <div class="container">
            <div class="section-heading homepage-section__heading" data-animate="left">
                <span class="eyebrow">Featured Listings</span>
                <h2 id="featured-listings-heading">Marketplace-style property discovery with better visual clarity</h2>
                <p>Pricing, property type, location, and the next action are surfaced immediately so users can browse faster and with more confidence.</p>
            </div>
            <div class="listing-grid listing-grid--showcase homepage-featured-listings" data-stagger>
                @foreach($homepageMarketplaceProperties as $property)
                    <article class="listing-card listing-card--showcase homepage-listing-card" data-animate>
                        <div class="listing-card__media">
                            <img src="{{ $property->image_url }}" alt="{{ $property->title }} property image" loading="lazy">
                            <span class="listing-card__badge">{{ $property->status ?? 'New Listing' }}</span>
                            <div class="listing-card__price-badge">${{ number_format($property->price) }}</div>
                            <div class="listing-card__save-group">
                                <form method="POST" action="{{ route('properties.favorite.toggle', $property) }}" class="listing-card__save-form">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="listing-card__save {{ $property->is_favorited ? 'is-active' : '' }}"
                                        aria-label="{{ $property->is_favorited ? 'Remove property from favorites' : 'Add property to favorites' }}"
                                    >
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="{{ $property->is_favorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                                    </button>
                                </form>
                                <span class="listing-card__save-count">{{ number_format($property->favorites_count ?? 0) }}</span>
                            </div>
                        </div>
                        <div class="listing-card__body">
                            <span class="listing-card__type">{{ $property->property_type ?? 'Property' }}</span>
                            <h3>{{ $property->title }}</h3>
                            <p class="listing-location">{{ $property->location }}</p>

                            <div class="listing-card__meta-grid">
                                <div class="listing-meta-chip">
                                    <strong>{{ $property->beds ?? '3' }}</strong>
                                    <span>Beds</span>
                                </div>
                                <div class="listing-meta-chip">
                                    <strong>{{ $property->baths ?? '2' }}</strong>
                                    <span>Baths</span>
                                </div>
                                <div class="listing-meta-chip">
                                    <strong>{{ $property->sqft ? number_format($property->sqft) : '1,200' }}</strong>
                                    <span>Sqft</span>
                                </div>
                            </div>

                            <div class="listing-card__footer">
                                <div class="listing-agent-mini">
                                    <div>
                                        <small>Listed by</small>
                                        <p title="{{ optional(optional($property->realtorProfile)->user)->name ?? 'OmniReferral Partner' }}">{{ optional(optional($property->realtorProfile)->user)->name ?? 'OmniReferral Partner' }}</p>
                                    </div>
                                </div>
                                <div class="listing-card__actions">
                                    <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue">Details</a>
                                    <a href="{{ route('properties.show', $property) }}#property-contact" class="button button--orange">Contact Agent</a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section section--gray homepage-section homepage-section--testimonials" id="testimonials" aria-labelledby="testimonials-heading" data-animate>
        <div class="container">
            <div class="section-heading homepage-section__heading" data-animate="left">
                <span class="eyebrow">Testimonials</span>
                <h2 id="testimonials-heading">Trusted by buyers, sellers, and agents who want a cleaner, more credible experience</h2>
                <p>We upgraded this section to feel more like real proof: stronger stories, clearer segments, and testimonials that speak to quality, clarity, and follow-through.</p>
            </div>
            <div class="homepage-testimonial-overview" data-animate="right">
                <div class="homepage-testimonial-overview__stats">
                    <article class="homepage-testimonial-overview__stat">
                        <strong>{{ number_format(max($publishedTestimonials->count(), $homepageTestimonials->count())) }}+</strong>
                        <span>Published and curated stories</span>
                    </article>
                    <article class="homepage-testimonial-overview__stat">
                        <strong>{{ $homepageAverageRating }}/5</strong>
                        <span>Average testimonial rating</span>
                    </article>
                    <article class="homepage-testimonial-overview__stat">
                        <strong>{{ number_format($homepageAudienceCounts['agent']) }}</strong>
                        <span>Agent-focused reviews</span>
                    </article>
                </div>
                <div class="homepage-testimonial-overview__actions">
                    <div class="homepage-testimonial-overview__pill-group">
                        <div class="homepage-testimonial-overview__pill">Buyer confidence</div>
                        <div class="homepage-testimonial-overview__pill">Seller clarity</div>
                        <div class="homepage-testimonial-overview__pill">Agent conversion quality</div>
                    </div>
                    <a href="{{ route('reviews') }}" class="button button--ghost-blue">See All Testimonials</a>
                </div>
            </div>

            <div class="homepage-testimonial-stage">
                @if($homepageFeaturedTestimonial)
                    <article class="testimonial-card homepage-testimonial-featured" data-animate="left">
                        <div class="homepage-testimonial-featured__meta">
                            <span class="homepage-testimonial-card__badge">Featured Story</span>
                            <span class="homepage-testimonial-card__badge homepage-testimonial-card__badge--video">{{ $homepageFeaturedTestimonial['audience'] }}</span>
                        </div>
                        <div class="testimonial-stars" aria-label="Five star rating">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                        <p class="homepage-testimonial-featured__quote">"{{ $homepageFeaturedTestimonial['quote'] }}"</p>
                        <div class="testimonial-card__footer homepage-testimonial-featured__footer">
                            <img src="{{ $homepageFeaturedTestimonial['path'] }}" alt="{{ $homepageFeaturedTestimonial['name'] }}" loading="lazy">
                            <div>
                                <strong>{{ $homepageFeaturedTestimonial['name'] }}</strong>
                                <span>{{ $homepageFeaturedTestimonial['role'] }}</span>
                                <small>{{ $homepageFeaturedTestimonial['location'] }}</small>
                            </div>
                        </div>
                        <a href="{{ route('reviews') }}" class="button button--orange">Read More Stories</a>
                    </article>
                @endif

                <div class="testimonial-carousel homepage-testimonial-carousel" data-carousel data-animate="right">
                    <div class="testimonial-track">
                        @foreach($homepageCarouselTestimonials as $testimonial)
                            <article class="testimonial-card homepage-testimonial-card">
                                <div class="homepage-testimonial-card__meta">
                                    <span class="homepage-testimonial-card__badge">{{ $testimonial['audience'] }}</span>
                                    @if(!empty($testimonial['has_video']))
                                        <span class="homepage-testimonial-card__badge homepage-testimonial-card__badge--video">Video story</span>
                                    @endif
                                </div>
                                <div class="testimonial-stars" aria-label="Five star rating">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                                <p class="testimonial-card__quote">"{{ $testimonial['quote'] }}"</p>
                                <div class="testimonial-card__footer">
                                    <img src="{{ $testimonial['path'] }}" alt="{{ $testimonial['name'] }}" loading="lazy">
                                    <div>
                                        <strong>{{ $testimonial['name'] }}</strong>
                                        <span>{{ $testimonial['role'] }}</span>
                                        <small>{{ $testimonial['location'] }}</small>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="carousel-controls homepage-carousel-controls">
                        <div class="homepage-carousel-meta">
                            <div class="homepage-carousel-status" data-carousel-status>1 / {{ max($homepageCarouselTestimonials->count(), 1) }}</div>
                            <div class="homepage-carousel-progress">
                                <span data-carousel-progress></span>
                            </div>
                        </div>
                        <div class="homepage-carousel-buttons">
                            <button type="button" data-carousel-prev aria-label="Previous testimonial">Previous</button>
                            <button type="button" data-carousel-next aria-label="Next testimonial">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section section--light homepage-section homepage-section--partners" id="partners" aria-labelledby="partner-logos-heading" data-animate>
        <div class="container">
            <div class="section-heading homepage-section__heading" data-animate="left">
                <span class="eyebrow">Our Partners</span>
                <h2 id="partner-logos-heading">Connected with trusted brands across the referral journey</h2>
                <p>Brand proof works best when it feels calm, credible, and premium. These partner logos now move in a smoother trust-first presentation.</p>
            </div>
            <div class="homepage-partner-shell">
                <div class="homepage-partner-shell__intro">
                    <div class="homepage-partner-shell__card">
                        <strong>{{ number_format($partnerLogos->count()) }}+</strong>
                        <span>Recognizable brands and marketplace references</span>
                    </div>
                    <div class="homepage-partner-shell__card">
                        <strong>Premium trust layer</strong>
                        <span>Presented with calmer motion and stronger visual consistency</span>
                    </div>
                </div>
                <div class="partner-marquee" aria-label="Partner brand logos">
                    @foreach($partnerLogoRows as $rowIndex => $row)
                        <div class="partner-marquee__track {{ $rowIndex === 1 ? 'partner-marquee__track--reverse' : '' }}">
                            @foreach($row->concat($row) as $logo)
                                <div class="homepage-partner-chip">
                                    <img src="{{ asset($logo['path']) }}" alt="{{ $logo['name'] }} logo" loading="lazy">
                                    <span>{{ $logo['name'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="section section--gray homepage-section homepage-section--blog" id="blog" aria-labelledby="blog-heading" data-animate>
        <div class="container">
            <div class="section-heading homepage-section__heading" data-animate="right">
                <span class="eyebrow">From The Blog</span>
                <h2 id="blog-heading">Insights for teams that care about growth, routing, and conversion quality</h2>
                <p>The content should reinforce expertise and trust, not just fill space. These cards are positioned as a premium editorial layer.</p>
            </div>
            <div class="blog-grid homepage-blog-grid" data-stagger>
                @foreach($blogs as $blog)
                    <article class="blog-card homepage-blog-card">
                        <img src="{{ $blog->image_url }}" alt="{{ $blog->title }}" loading="lazy">
                        <div class="blog-card__content">
                            <h3>{{ $blog->title }}</h3>
                            <p>{{ Str::limit($blog->excerpt, 110) }}</p>
                            <a href="{{ route('blog.show', $blog) }}" class="button button--ghost-blue">Read More</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="cta-band homepage-cta-band" data-animate>
        <div class="container cta-band__inner homepage-cta-band__inner">
            <div class="cta-band__content">
                <span class="eyebrow">Next Step</span>
                <h2>Ready to move from generic leads to a cleaner referral engine?</h2>
                <p>Explore packages, talk to the team, or submit your buyer or seller request and let OmniReferral guide the next step.</p>
            </div>
            <div class="cta-band__actions">
                <a href="{{ route('pricing') }}" class="button button--orange">View Packages</a>
                <a href="{{ route('contact') }}" class="button button--ghost">Talk To Our Team</a>
            </div>
        </div>
    </section>
</div>
@push('scripts')
    @include('partials.pricing-toggle-script')
@endpush
@endsection
