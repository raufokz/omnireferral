@extends('layouts.app')

@section('content')
@php
    $faqSections = [
        [
            'id' => 'getting-started',
            'eyebrow' => 'Getting Started',
            'title' => 'How OmniReferral works',
            'copy' => 'The questions below cover the core flow across the public site, the lead process, and the platform experience for every role.',
            'items' => [
                [
                    'q' => 'What is OmniReferral?',
                    'a' => 'OmniReferral is a real-estate referral and operations platform built for buyers, sellers, agents, and internal teams. It combines lead capture, qualification, listing workflows, messaging, dashboards, and support tools into one branded experience.',
                ],
                [
                    'q' => 'Who can use OmniReferral?',
                    'a' => 'Buyers, sellers, agents, admins, staff teams, and general community users can all interact with OmniReferral. Each role gets a different workspace, but the public experience is designed to feel consistent across listings, pricing, reviews, and support.',
                ],
                [
                    'q' => 'Do I need an account to browse listings or agents?',
                    'a' => 'No. Visitors can browse public listings, view agent profiles, read testimonials, and contact the right person from public pages. An account is mainly needed for workspaces, internal dashboards, package ownership, and role-based tools.',
                ],
                [
                    'q' => 'Can users submit their own review?',
                    'a' => 'Yes. Buyers, sellers, agents, and general users can submit reviews from the testimonials page. New reviews are not published immediately. They go to the admin team first, and only approved reviews appear publicly.',
                ],
            ],
        ],
        [
            'id' => 'pricing-packages',
            'eyebrow' => 'Pricing & Packages',
            'title' => 'Lead plans, VA support, and package decisions',
            'copy' => 'These are the most common package and pricing questions that come up across the pricing page, checkout flow, and support conversations.',
            'items' => [
                [
                    'q' => 'What is the difference between Quick, Power, and Prime?',
                    'a' => 'Quick is the lightest entry path for agents who want verified opportunities with a smaller footprint. Power adds stronger qualification depth, more support, and more visibility. Prime is the highest-touch tier with broader routing coverage, premium support, and deeper marketing help.',
                ],
                [
                    'q' => 'What do the virtual assistance plans cover?',
                    'a' => 'The VA options are for agents and teams that need more execution help after lead capture. Depending on the plan, that can include cold calling, CRM cleanup, nurture help, social content support, workflows, or coordination tasks.',
                ],
                [
                    'q' => 'Does package level affect listing access?',
                    'a' => 'Yes. For agent workspaces, listing access is tied to the purchased lead package. The current plan determines how many active listings can be submitted and kept live at one time.',
                ],
                [
                    'q' => 'Why can pricing copy and checkout details look slightly different?',
                    'a' => 'The marketing page explains the package strategy, while checkout uses the actual package record that billing relies on. We now align checkout to the same package presentation layer so the plan name, summary, feature list, and billing context stay much closer to what you saw on pricing.',
                ],
            ],
        ],
        [
            'id' => 'leads-listings',
            'eyebrow' => 'Leads & Listings',
            'title' => 'Qualification, routing, listing submission, and contact flows',
            'copy' => 'This section covers how public inquiries move into the platform and how listings are reviewed before they go live.',
            'items' => [
                [
                    'q' => 'How are leads qualified before an agent sees them?',
                    'a' => 'OmniReferral uses intake logic plus team review to confirm intent, market, and route readiness. Package tier, workflow stage, and role-based routing determine how opportunities are prioritized and who sees them next.',
                ],
                [
                    'q' => 'What happens when someone clicks Contact Agent on a listing?',
                    'a' => 'The inquiry goes to the listing or agent-specific contact flow instead of the generic site contact page. That keeps the message tied to the exact property or agent profile the user was viewing, and both admin and the assigned agent can see the context.',
                ],
                [
                    'q' => 'Can agents and sellers publish listings immediately?',
                    'a' => 'No. Agent and seller listing submissions now go into an admin review queue first. A listing must be approved before it becomes publicly visible in the marketplace.',
                ],
                [
                    'q' => 'What happens if a listing is rejected?',
                    'a' => 'If admin rejects a listing, the owner can update it and resubmit it for review. Rejected listings do not appear publicly until they are approved.',
                ],
                [
                    'q' => 'Can admin see listing-related messages too?',
                    'a' => 'Yes. Admin and staff can see listing and agent-profile inquiries in the operations workspace so there is oversight across buyer, seller, and agent communication tied to the marketplace.',
                ],
            ],
        ],
        [
            'id' => 'dashboards-accounts',
            'eyebrow' => 'Dashboards & Accounts',
            'title' => 'Role workspaces, login behavior, and messaging',
            'copy' => 'These questions come from the buyer, seller, agent, admin, and staff dashboards.',
            'items' => [
                [
                    'q' => 'Do all roles use the same dashboard design?',
                    'a' => 'Yes. The live role dashboards were unified into the same Omnireferral design system, so buyer, seller, agent, admin, and staff workspaces now share one visual shell while keeping role-specific data and actions.',
                ],
                [
                    'q' => 'Can plain passwords still work when logging in?',
                    'a' => 'The platform accepts normal plain-text passwords at login, but passwords are stored securely as hashes whenever possible. Legacy plain-text passwords can be upgraded automatically after a successful login.',
                ],
                [
                    'q' => 'Where do listing and profile messages appear for agents?',
                    'a' => 'Agents see listing and profile inquiries in their dedicated messages workspace. That keeps website conversations separate from generic support forms and tied to the right lead or listing context.',
                ],
                [
                    'q' => 'Can buyers and sellers manage everything from one place?',
                    'a' => 'Yes. Buyers can track shortlist-style activity, request progress, and marketplace exploration. Sellers can submit properties, review request flow, and track approval-related steps from their own dashboard.',
                ],
            ],
        ],
        [
            'id' => 'billing-trust',
            'eyebrow' => 'Billing, Support & Trust',
            'title' => 'Checkout, refund policy, reviews, security, and legal questions',
            'copy' => 'These are the questions that most often overlap with the payment policy, privacy policy, scam prevention, communication policy, and testimonials workflow.',
            'items' => [
                [
                    'q' => 'What happens after payment is completed?',
                    'a' => 'After checkout, OmniReferral moves into the setup flow associated with the purchased package. If a connected onboarding form is available, it helps capture the details needed for workspace provisioning, routing, and follow-up.',
                ],
                [
                    'q' => 'Where can I review refund and cancellation terms?',
                    'a' => 'The full payment, refund, and cancellation rules are published on the Payment, Refund & Cancellation Policy page. That page explains review windows, eligibility, cancellation instructions, and referral-fee obligations after cancellation.',
                ],
                [
                    'q' => 'How do I know if a message or website is really from OmniReferral?',
                    'a' => 'Use the Scam Prevention page and official OmniReferral contact channels before sharing sensitive information. If something looks suspicious, verify it through the official site instead of replying directly to the message.',
                ],
                [
                    'q' => 'Does OmniReferral send SMS or phone communications?',
                    'a' => 'Yes, depending on the context and consent provided. Communication expectations, opt-out behavior, call recording language, and related dispute terms are described on the Communication Policy page.',
                ],
                [
                    'q' => 'What is the fastest way to get help?',
                    'a' => 'If your question is sales, package, onboarding, or support related, use the contact page. If you need policy guidance, the Privacy, Terms, Payment Policy, Scam Prevention, and Communication Policy pages now cover the platform rules in much more detail.',
                ],
            ],
        ],
    ];
@endphp

<div class="faq-page">
    <section class="page-hero page-hero--omni faq-hero" data-reveal>
        <div class="container omni-page-hero__grid faq-hero__grid">
            <div class="omni-page-hero__copy">
                <span class="eyebrow">FAQ</span>
                <h1>Questions we hear across the whole OmniReferral website.</h1>
                <p>This page pulls together the main questions users ask after browsing pricing, listings, reviews, legal pages, dashboards, and checkout, so buyers, sellers, agents, and staff all start from one clearer source.</p>
                <div class="faq-page__quick-links">
                    @foreach($faqSections as $section)
                        <a href="#{{ $section['id'] }}">{{ $section['title'] }}</a>
                    @endforeach
                </div>
            </div>

            <aside class="omni-page-hero__panel faq-hero__panel">
                <span class="eyebrow">Quick Snapshot</span>
                <h2>Start with the part of the platform you are using.</h2>
                <p>Pricing, listings, dashboards, checkout, and trust pages all connect here so users do not have to jump between multiple screens for basic answers.</p>
                <div class="omni-page-hero__meta">
                    <div>
                        <span>Coverage</span>
                        <strong>5 core question groups</strong>
                    </div>
                    <div>
                        <span>Audience</span>
                        <strong>Buyers, sellers, agents, and staff</strong>
                    </div>
                    <div>
                        <span>Related</span>
                        <strong>Pricing, legal, reviews, and support</strong>
                    </div>
                    <div>
                        <span>Next Step</span>
                        <strong>Contact OmniReferral for anything custom</strong>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <section class="section faq-section" data-stagger>
        <div class="container faq-page__shell">
            <aside class="faq-intro" data-reveal>
                <span class="eyebrow">Support</span>
                <h2>Need quick clarity?</h2>
                <p>Use this page for the questions that show up most often across the public website and the role-based workspace experience.</p>

                <div class="faq-page__mini-grid">
                    <article>
                        <strong>5</strong>
                        <span>Topic groups</span>
                    </article>
                    <article>
                        <strong>20+</strong>
                        <span>Core answers</span>
                    </article>
                    <article>
                        <strong>1</strong>
                        <span>Unified source</span>
                    </article>
                    <article>
                        <strong>24 hr</strong>
                        <span>Support target</span>
                    </article>
                </div>

                <div class="faq-cta">
                    <a class="button button--orange" href="{{ route('contact') }}">Talk to our team</a>
                    <a class="button button--ghost-blue" href="{{ route('pricing') }}">View packages</a>
                </div>

                <div class="faq-page__knowledge-card">
                    <span class="eyebrow">Related Pages</span>
                    <h3>Helpful links</h3>
                    <p>If you need policy details, pricing specifics, or public trust information, these pages go deeper than the FAQ.</p>
                    <div class="faq-page__link-list">
                        <a href="{{ route('reviews') }}">Testimonials</a>
                        <a href="{{ route('payment.policy') }}">Payment Policy</a>
                        <a href="{{ route('scam.prevention') }}">Scam Prevention</a>
                        <a href="{{ route('communication.policy') }}">Communication Policy</a>
                    </div>
                </div>
            </aside>

            <div class="faq-page__content">
                @foreach($faqSections as $section)
                    <section class="faq-page__section-card" id="{{ $section['id'] }}" data-reveal>
                        <div class="faq-page__section-header">
                            <span class="eyebrow">{{ $section['eyebrow'] }}</span>
                            <h2>{{ $section['title'] }}</h2>
                            <p>{{ $section['copy'] }}</p>
                        </div>

                        <div class="faq-page__accordion faq-list--compact">
                            @foreach($section['items'] as $index => $item)
                                <details class="faq-accordion" {{ $index === 0 ? 'open' : '' }}>
                                    <summary>{{ $item['q'] }}</summary>
                                    <p>{{ $item['a'] }}</p>
                                </details>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <section class="faq-page__knowledge-grid" data-reveal>
                    <article class="faq-page__knowledge-card">
                        <span class="eyebrow">Pricing</span>
                        <h3>Need help picking a plan?</h3>
                        <p>Compare Quick, Power, Prime, and the VA support options on the pricing page before checkout.</p>
                        <a href="{{ route('pricing') }}">Explore pricing</a>
                    </article>
                    <article class="faq-page__knowledge-card">
                        <span class="eyebrow">Trust</span>
                        <h3>Want real feedback first?</h3>
                        <p>Read approved reviews from buyers, sellers, agents, and community users on the testimonials page.</p>
                        <a href="{{ route('reviews') }}">Read testimonials</a>
                    </article>
                    <article class="faq-page__knowledge-card">
                        <span class="eyebrow">Support</span>
                        <h3>Still need a human answer?</h3>
                        <p>If your question is specific to your market, package, or account, send it through the contact page.</p>
                        <a href="{{ route('contact') }}">Contact OmniReferral</a>
                    </article>
                </section>
            </div>
        </div>
    </section>
</div>
@endsection
