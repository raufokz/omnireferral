@php
    $footerCompany = config('omnireferral.company');
    $footerSupportEmail = trim((string) ($footerCompany['support_email'] ?? 'support@omnireferrals.com'));
    $footerInfoEmail = trim((string) ($footerCompany['info_email'] ?? 'info@omnireferrals.com'));
    $footerPhoneE164 = trim((string) ($footerCompany['support_phone_e164'] ?? '+12312813131'));
    $footerPhoneDisplay = trim((string) ($footerCompany['support_phone_display'] ?? '+1 231-281-3131'));
    $footerSocialLabels = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'linkedin' => 'LinkedIn',
        'pinterest' => 'Pinterest',
    ];
    $footerSocialLinks = collect($footerCompany['social_links'] ?? [])
        ->only(array_keys($footerSocialLabels))
        ->filter()
        ->map(function ($url, $platform) use ($footerSocialLabels) {
            $platformKey = strtolower((string) $platform);

            return [
                'label' => $footerSocialLabels[$platformKey] ?? ucfirst($platformKey),
                'platform' => preg_replace('/[^a-z0-9-]/', '', str_replace('_', '-', $platformKey)),
                'url' => $url,
            ];
        })
        ->values();
@endphp

<footer class="site-footer site-footer--premium">
    <div class="container footer-premium">
        <section class="footer-hero-cta" aria-labelledby="footer-cta-title">
            <div class="footer-hero-cta__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 12a8 8 0 0 1 16 0"></path>
                    <path d="M4 13v3a2 2 0 0 0 2 2h1v-7H6a2 2 0 0 0-2 2Z"></path>
                    <path d="M20 13v3a2 2 0 0 1-2 2h-1v-7h1a2 2 0 0 1 2 2Z"></path>
                    <path d="M15 20h-3"></path>
                </svg>
            </div>
            <div class="footer-hero-cta__copy">
                <span>OmniReferral Concierge</span>
                <h2 id="footer-cta-title">Need help finding the right referral solution?</h2>
                <p>Our team is here to help you generate more leads, close more deals, and grow your real estate business with a cleaner referral system.</p>
            </div>
            <div class="footer-hero-cta__actions">
                <a href="{{ route('contact') }}" class="footer-cta-button footer-cta-button--primary">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M21.8 3.4 3.5 10.5c-.8.3-.8 1.4 0 1.7l5.2 2 2 5.3c.3.8 1.4.8 1.8.1l8.6-15.4c.3-.6-.2-1.2-.9-.8ZM10.7 14l7.1-7.1-5.7 9.9-1.4-2.8Zm-1-1-2.7-1.1 9.6-3.7L9.7 13Z"></path>
                    </svg>
                    <span>Talk To Our Team</span>
                </a>
                <a href="{{ route('pricing') }}" class="footer-cta-button footer-cta-button--secondary">
                    <span>Get Started Free</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                </a>
            </div>
        </section>

        <section class="footer-trust-strip" aria-label="OmniReferral trust indicators">
            <article class="footer-trust-card footer-trust-card--response">
                <span class="footer-trust-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m13 2-8 12h6l-1 8 9-13h-6l1-7Z"></path>
                    </svg>
                </span>
                <div>
                    <strong>24-Hour Response</strong>
                    <p>Fast follow-up for every verified inquiry.</p>
                </div>
            </article>
            <article class="footer-trust-card footer-trust-card--leads">
                <span class="footer-trust-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8.4 11.6a3.4 3.4 0 1 0 0-6.8 3.4 3.4 0 0 0 0 6.8Zm7.2 0a3.4 3.4 0 1 0 0-6.8 3.4 3.4 0 0 0 0 6.8ZM2.7 19.2c0-3.2 2.5-5.8 5.7-5.8s5.7 2.6 5.7 5.8v.6H2.7v-.6Zm9.9.6h8.7v-.5c0-3.1-2.4-5.6-5.5-5.8a7.1 7.1 0 0 1 2.2 5.2v1.1h-5.4Z"></path>
                    </svg>
                </span>
                <div>
                    <strong>Verified Leads</strong>
                    <p>Every lead is verified and qualified before delivery.</p>
                </div>
            </article>
            <article class="footer-trust-card footer-trust-card--network">
                <span class="footer-trust-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3 20 7v5c0 5-3.4 8-8 9-4.6-1-8-4-8-9V7l8-4Z"></path>
                        <path d="m9.5 12 1.8 1.8 3.7-4"></path>
                    </svg>
                </span>
                <div>
                    <strong>Licensed Agent Network</strong>
                    <p>Local agents, brokers, and admin support ready to respond.</p>
                </div>
            </article>
            <article class="footer-trust-card footer-trust-card--support">
                <span class="footer-trust-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 12a8 8 0 0 1 16 0"></path>
                        <path d="M4 13v3a2 2 0 0 0 2 2h1v-7H6a2 2 0 0 0-2 2Z"></path>
                        <path d="M20 13v3a2 2 0 0 1-2 2h-1v-7h1a2 2 0 0 1 2 2Z"></path>
                        <path d="M15 20h-3"></path>
                    </svg>
                </span>
                <div>
                    <strong>Live Support Hours</strong>
                    <p>Mon-Fri, 8am-7pm local business time.</p>
                </div>
            </article>
        </section>

        <div class="footer-main-grid">
            <section class="footer-brand-panel" aria-labelledby="footer-brand-title">
                <a href="{{ route('home') }}" class="footer-brand-panel__logo">
                    <img src="{{ asset('images/omnireferral-logo.png') }}" alt="" aria-hidden="true">
                    <span id="footer-brand-title" class="footer-brand-panel__wordmark">
                        <span>Omni</span><strong>Referral</strong>
                    </span>
                </a>
                <p>The modern lead generation system built around trust, clarity, and faster handoffs for real estate professionals.</p>

                <div class="footer-contact-list" aria-label="Support and information contacts">
                    @if ($footerSupportEmail)
                        <a href="mailto:{{ $footerSupportEmail }}" class="footer-contact-item">
                            <span class="footer-contact-item__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                    <path d="m3 7 9 6 9-6"></path>
                                </svg>
                            </span>
                            <span>{{ $footerSupportEmail }}</span>
                        </a>
                    @endif
                    @if ($footerInfoEmail)
                        <a href="mailto:{{ $footerInfoEmail }}" class="footer-contact-item">
                            <span class="footer-contact-item__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                    <path d="m3 7 9 6 9-6"></path>
                                </svg>
                            </span>
                            <span>{{ $footerInfoEmail }}</span>
                        </a>
                    @endif
                    @if ($footerPhoneE164 && $footerPhoneDisplay)
                        <a href="tel:{{ $footerPhoneE164 }}" class="footer-contact-item">
                            <span class="footer-contact-item__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v2.2a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 3.4 2 2 0 0 1 4.11 1.3h2.2a2 2 0 0 1 2 1.72c.12.9.33 1.78.64 2.62a2 2 0 0 1-.45 2.11L7.6 8.65a16 16 0 0 0 7.75 7.75l.9-.9a2 2 0 0 1 2.11-.45c.84.31 1.72.52 2.62.64A2 2 0 0 1 22 16.92Z"></path>
                                </svg>
                            </span>
                            <span>{{ $footerPhoneDisplay }}</span>
                        </a>
                    @endif
                </div>

                @if ($footerSocialLinks->isNotEmpty())
                    <div class="footer-social" aria-label="Follow OmniReferral">
                        @foreach ($footerSocialLinks as $socialLink)
                            <a
                                href="{{ $socialLink['url'] }}"
                                class="footer-social__link footer-social__link--{{ $socialLink['platform'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="Follow OmniReferral on {{ $socialLink['label'] }}"
                            >
                                @switch($socialLink['platform'])
                                    @case('facebook')
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M14 8.5h2.2V5.1c-.38-.05-1.7-.16-3.23-.16-3.2 0-5.38 1.95-5.38 5.54v3.12H4v3.8h3.59V24h4.41v-6.6h3.45l.55-3.8h-4v-2.74c0-1.1.3-2.36 2-2.36Z"></path>
                                        </svg>
                                        @break
                                    @case('instagram')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <rect x="3" y="3" width="18" height="18" rx="5"></rect>
                                            <circle cx="12" cy="12" r="4"></circle>
                                            <circle cx="17.4" cy="6.6" r="1.2" fill="currentColor" stroke="none"></circle>
                                        </svg>
                                        @break
                                    @case('linkedin')
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M5.1 7.5H1.2V22h3.9V7.5ZM3.15 1.5a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5ZM22.8 14.1c0-4.18-2.23-6.12-5.2-6.12-2.4 0-3.47 1.32-4.07 2.25V7.5H9.8V22h3.9v-7.18c0-1.9.36-3.74 2.72-3.74 2.32 0 2.35 2.17 2.35 3.86V22h4.03v-7.9Z"></path>
                                        </svg>
                                        @break
                                    @case('pinterest')
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M12.1 1.6C6.5 1.6 3.7 5.3 3.7 9.4c0 1.9 1 4.2 2.6 5 .24.1.37.06.43-.17.04-.17.26-1.02.36-1.42.03-.13.02-.25-.09-.38-.53-.64-.96-1.8-.96-2.88 0-2.84 2.15-5.59 5.82-5.59 3.17 0 5.38 2.16 5.38 5.25 0 3.5-1.77 5.93-4.08 5.93-1.27 0-2.22-1.05-1.92-2.34.36-1.54 1.06-3.2 1.06-4.31 0-.99-.53-1.82-1.64-1.82-1.3 0-2.35 1.34-2.35 3.14 0 1.15.39 1.92.39 1.92s-1.29 5.46-1.53 6.48c-.26 1.1-.16 2.65-.05 3.66.05.43.58.52.8.16.34-.56.9-1.57 1.17-2.64.15-.59.76-2.98.76-2.98.4.76 1.55 1.4 2.77 1.4 3.64 0 6.28-3.35 6.28-7.5 0-3.99-3.26-7.05-7.85-7.05Z"></path>
                                        </svg>
                                        @break
                                @endswitch
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            <nav class="footer-link-group" aria-labelledby="footer-platform-title">
                <h3 id="footer-platform-title">Platform</h3>
                <a href="{{ route('about') }}">About Us</a>
                <a href="{{ route('pricing') }}">Pricing</a>
                <a href="{{ route('agents.index') }}">Agent Directory</a>
                <a href="{{ route('blog.index') }}">Blog</a>
            </nav>

            <nav class="footer-link-group" aria-labelledby="footer-agents-title">
                <h3 id="footer-agents-title">For Agents</h3>
                <a href="{{ route('agents.index') }}">Find Agents</a>
                <a href="{{ route('pricing') }}">Packages</a>
                <a href="{{ route('contact') }}">Agent Support</a>
                <a href="{{ route('surveys') }}">Campaign Tools</a>
            </nav>

            <nav class="footer-link-group" aria-labelledby="footer-consumers-title">
                <h3 id="footer-consumers-title">For Buyers &amp; Sellers</h3>
                <a href="{{ route('listings') }}">Listings</a>
                <a href="{{ route('contact') }}">Request Match</a>
                <a href="{{ route('reviews') }}">Testimonials</a>
                <a href="{{ route('faq') }}">FAQ</a>
            </nav>

            <nav class="footer-link-group" aria-labelledby="footer-legal-title">
                <h3 id="footer-legal-title">Legal</h3>
                <a href="{{ route('privacy') }}">Privacy Policy</a>
                <a href="{{ route('terms') }}">Terms of Service</a>
                <a href="{{ route('payment.policy') }}">Payment &amp; Cancellation</a>
                <a href="{{ route('scam.prevention') }}">Scam Prevention</a>
                <a href="{{ route('privacy') }}#accessibility">Accessibility</a>
                <a href="{{ route('sitemap') }}">Sitemap</a>
            </nav>
        </div>

        <div class="footer-bottom">
            <span class="footer-bottom__copyright">&copy; 2026 OmniReferral. All Rights Reserved.</span>
            <span class="footer-powered">Powered by <strong>OmniReferral</strong></span>
        </div>
    </div>
</footer>
