@extends('layouts.app')

@section('content')
<div class="homepage-shell homepage-shell--refined">
    <section class="hero hero--premium homepage-hero homepage-hero--minimal" aria-labelledby="hero-headline" style="background-image: linear-gradient(120deg, rgba(0, 28, 72, 0.86), rgba(0, 28, 72, 0.78)), url('{{ asset('images/hero/bg.jpg') }}'); background-size: cover; background-position: center;">
        <div class="hero__backdrop"></div>
        <div class="container hero__content hero__content--premium homepage-hero__layout">
            <div class="hero__copy hero__copy--premium homepage-hero__copy" data-reveal="left">
                <span class="eyebrow">Premium Real Estate Lead Engine</span>
                <h1 id="hero-headline">Find serious buyers & sellers faster.</h1>
                <p>ISA-qualified intake, smarter routing, and dashboards that feel like a modern marketplace.</p>

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
                    <h2>Tell us what kind of opportunity you need</h2>
                    <p>Choose the buyer or seller path and we will guide your request into the right next step without adding friction.</p>
                </div>

                <div class="tab-switcher hero-tabs" role="tablist" aria-label="Lead type">
                    <button class="tab-switcher__button is-active" type="button" role="tab" aria-selected="true" data-tab-trigger="buyer">Buyer</button>
                    <button class="tab-switcher__button" type="button" role="tab" aria-selected="false" data-tab-trigger="seller">Seller</button>
                </div>

                <div class="tab-panel is-active" data-tab-panel="buyer">
                    <form class="hero-form" method="POST" action="{{ route('leads.store') }}" data-multi-step novalidate>
                        @csrf
                        <input type="hidden" name="intent" value="buyer">

                        <div class="form-progress">
                            <div class="form-progress-bar"></div>
                        </div>

                        <div class="form-step is-active">
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
                            <div class="form-intro hero-form-intro">
                                <h2>Where are you looking?</h2>
                                <p>We use your location, budget, and timing to route you intelligently.</p>
                            </div>

                            <div class="hero-map-card">
                                <div class="hero-map" id="hero-map" aria-label="Lead search map"></div>
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

                            <div class="hero-form__grid">
                                <input type="hidden" name="package_slug" value="quick-leads">
                                <label class="floating-field zip-tags" data-zip-tags>
                                    <input type="hidden" name="zip_code" value="">
                                    <input type="text" placeholder="Enter ZIP code" data-zip-entry inputmode="numeric" maxlength="10">
                                    <span>ZIP code</span>
                                    <div class="zip-tag-list" aria-live="polite"></div>
                                    <button type="button" class="zip-add-btn" data-zip-add>Add another ZIP</button>
                                </label>
                                <label><span>Property type</span><select name="property_type"><option value="">Select type</option><option>House</option><option>Apartment</option><option>Condo</option><option>Commercial</option></select></label>
                                <label><span>Budget range</span><input type="number" name="budget" placeholder="450000" min="0" inputmode="numeric"></label>
                                <label><span>Timeline</span><select name="timeline"><option value="">Select timing</option><option>ASAP</option><option>0-30 days</option><option>1-3 months</option><option>3-6 months</option><option>Exploring</option></select></label>
                                <label><span>Financing status</span><select name="financing_status"><option value="">Select status</option><option>Cash buyer</option><option>Pre-approved</option><option>Need financing guidance</option><option>Just exploring</option></select></label>
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

                <div class="tab-panel" data-tab-panel="seller">
                    <form class="hero-form" method="POST" action="{{ route('leads.store') }}" enctype="multipart/form-data" data-multi-step novalidate>
                        @csrf
                        <input type="hidden" name="intent" value="seller">

                        <div class="form-progress">
                            <div class="form-progress-bar"></div>
                        </div>

                        <div class="form-step is-active">
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
                            <div class="form-intro hero-form-intro">
                                <h2>Property details</h2>
                                <p>Share the basics so our team can qualify and route the lead correctly.</p>
                            </div>
                            <div class="hero-form__grid">
                                <input type="hidden" name="package_slug" value="power-leads">
                                    <label class="floating-field zip-tags" data-zip-tags>
                                        <input type="hidden" name="zip_code" value="">
                                        <input type="text" placeholder="Enter ZIP code" data-zip-entry inputmode="numeric" maxlength="10">
                                        <span>Property ZIP code</span>
                                        <div class="zip-tag-list" aria-live="polite"></div>
                                        <button type="button" class="zip-add-btn" data-zip-add>Add another ZIP</button>
                                    </label>
                                <label><span>Property type</span><select name="property_type"><option value="">Select type</option><option>House</option><option>Apartment</option><option>Condo</option><option>Commercial</option></select></label>
                                <label><span>Asking price</span><input type="number" name="asking_price" placeholder="625000" min="0" inputmode="numeric"></label>
                                <label><span>Timeline</span><select name="timeline"><option value="">Select timing</option><option>ASAP</option><option>0-30 days</option><option>1-3 months</option><option>3-6 months</option><option>Exploring options</option></select></label>
                                <label><span>Financing / deal constraints</span><select name="financing_status"><option value="">Select status</option><option>Need pricing help</option><option>As-is sale</option><option>Open to renovations</option><option>Need quick close</option></select></label>
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

    <div class="stat-strip" aria-label="Platform statistics">
        <div class="container stat-strip__grid">
            <div class="stat-strip__item" data-counter="3200" data-suffix="+"><span class="stat-strip__number">3,200+</span><span class="stat-strip__label">Qualified leads delivered</span></div>
            <div class="stat-strip__item" data-counter="450" data-suffix="+"><span class="stat-strip__number">450+</span><span class="stat-strip__label">Active agent partners</span></div>
            <div class="stat-strip__item" data-counter="92" data-suffix="%"><span class="stat-strip__number">92%</span><span class="stat-strip__label">Client satisfaction</span></div>
            <div class="stat-strip__item" data-counter="48" data-suffix=" hr"><span class="stat-strip__number">48 hr</span><span class="stat-strip__label">Average routing window</span></div>
        </div>
    </div>

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
                        <p>Sales executives package each lead into the right Quick, Power, or Prime path so the delivery matches the urgency and value of your team's capacity.</p>
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
                    <p>Quick, Power, and Prime are easy to understand and mapped to real growth stages.</p>
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

    <section class="section section--gray homepage-section homepage-section--pricing" id="pricing-preview" aria-labelledby="pricing-preview-heading" data-animate>
        <div class="container">
            <div class="section-heading homepage-section__heading" data-animate="left">
                <span class="eyebrow">Pricing Snapshot</span>
                <h2 id="pricing-preview-heading">Choose a lead package that matches your growth stage</h2>
                <p>Each plan is positioned to make the next move obvious, whether you are testing a market or scaling a high-performing team.</p>
            </div>
            <div class="pricing-grid pricing-grid--spotlight" data-stagger>
                @foreach($packages->take(3) as $package)
                    @php
                        $packageSummary = match (true) {
                            str_contains(strtolower($package->name), 'quick') => 'A lighter entry point for agents who want verified contacts and clean routing.',
                            str_contains(strtolower($package->name), 'power') => 'Balanced lead detail and urgency for steady month-over-month growth.',
                            default => 'Higher-intent opportunities with the strongest qualification and faster support.',
                        };
                    @endphp
                    <article class="pricing-card pricing-card--interactive homepage-pricing-card {{ $package->is_featured ? 'pricing-card--featured' : '' }}">
                        <div class="homepage-pricing-card__header">
                            <div class="homepage-pricing-card__eyebrow-row">
                                <span class="pricing-label">{{ $package->is_featured ? 'Most Chosen' : 'Lead Package' }}</span>
                                @if($package->is_featured)
                                    <div class="pricing-badge-popular">Most Popular</div>
                                @endif
                            </div>
                            <h3>{{ $package->name }}</h3>
                            <p class="homepage-pricing-card__summary">{{ $packageSummary }}</p>
                        </div>
                        <div class="price-row homepage-pricing-card__price">
                            <strong>${{ number_format($package->one_time_price ?? 0) }}</strong>
                            <span>Starting one-time</span>
                        </div>
                        <ul class="feature-check-list homepage-pricing-card__features">
                            @foreach($package->features as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ route('pricing') }}" class="button {{ $package->is_featured ? 'button--orange' : 'button--blue' }}">View Tier</a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section section--light homepage-section homepage-section--featured" aria-labelledby="featured-listings-heading" data-animate>
        <div class="container">
            <div class="section-heading homepage-section__heading" data-animate="left">
                <span class="eyebrow">Featured Listings</span>
                <h2 id="featured-listings-heading">Marketplace-style property discovery with better visual clarity</h2>
                <p>Pricing, property type, location, and the next action are surfaced immediately so users can browse faster and with more confidence.</p>
            </div>
            <div class="listing-grid listing-grid--showcase homepage-featured-listings" data-stagger>
                @foreach($properties as $property)
                    <article class="listing-card listing-card--showcase homepage-listing-card" data-animate>
                        <div class="listing-card__media">
                            <img src="{{ $property->image_url }}" alt="{{ $property->title }} property image" loading="lazy">
                            <span class="listing-card__badge">{{ $property->status ?? 'New Listing' }}</span>
                            <div class="listing-card__price-badge">${{ number_format($property->price) }}</div>
                            <button type="button" class="listing-card__save" aria-label="Save property">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                            </button>
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
                                        <p>{{ optional(optional($property->realtorProfile)->user)->name ?? 'OmniReferral Partner' }}</p>
                                    </div>
                                </div>
                                <div class="listing-card__actions">
                                    <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue">Details</a>
                                    <a href="{{ route('contact') }}?property={{ urlencode($property->title) }}" class="button button--orange">Contact</a>
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
                <h2 id="testimonials-heading">Trusted by realtors who want cleaner lead flow and stronger handoffs</h2>
                <p>Social proof should feel credible, specific, and polished. These agent stories are positioned to reinforce exactly that.</p>
            </div>
            <div class="testimonial-carousel homepage-testimonial-carousel" data-carousel data-stagger>
                <div class="testimonial-track">
                    @foreach($testimonials as $testimonial)
                        <article class="testimonial-card homepage-testimonial-card">
                            <div class="testimonial-stars" aria-label="Five star rating">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                            <p class="testimonial-card__quote">"{{ $testimonial['quote'] }}"</p>
                            <div class="testimonial-card__footer">
                                <img src="{{ asset($testimonial['path']) }}" alt="{{ $testimonial['name'] }}" loading="lazy">
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
                    <button type="button" data-carousel-prev aria-label="Previous testimonial">Previous</button>
                    <button type="button" data-carousel-next aria-label="Next testimonial">Next</button>
                </div>
            </div>
        </div>
    </section>

    <section class="section section--light homepage-section homepage-section--partners" id="partners" aria-labelledby="partner-logos-heading" data-animate>
        <div class="container">
            <div class="section-heading homepage-section__heading" data-animate="left">
                <span class="eyebrow">Our Partners</span>
                <h2 id="partner-logos-heading">Connected with trusted brands across the referral journey</h2>
                <p>Partner recognition should build confidence quickly, so the logos are presented in a calmer, more premium grid instead of competing for attention.</p>
            </div>
            <div class="partner-logo-grid" data-stagger>
                @foreach($partnerLogos as $logo)
                    <div class="partner-logo-card">
                        <img src="{{ asset($logo['path']) }}" alt="{{ $logo['name'] }} logo" loading="lazy">
                    </div>
                @endforeach
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
@endsection
