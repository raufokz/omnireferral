@extends('layouts.app')

@push('styles')
    @vite('resources/css/modules/contact.css')
@endpush

@section('content')

<section class="page-hero agent-directory-hero contact-page-hero">
    <div class="agent-directory-hero__glow" aria-hidden="true"></div>
    <div class="container agent-directory-hero__inner" data-animate="up">
        <div class="agent-directory-hero__copy">
            <span class="eyebrow">Get In Touch</span>
            <h1>Let&apos;s talk about your next lead, listing, or partnership.</h1>
            <p>We keep conversations simple, helpful, and fast. Expect a response within one business day from the OmniReferral team.</p>
            <div class="agent-directory-hero__actions">
                <a href="mailto:hello@omnireferral.us" class="button button--orange">Email Support</a>
                <a href="tel:+18005550147" class="button button--ghost-light">Call Directly</a>
            </div>
            <div class="agent-directory-hero__proof">
                <span>24-hour average response</span>
                <span>Real team, no bots</span>
                <span>Lead, listing, and billing help</span>
            </div>
        </div>

        <aside class="agent-directory-hero__panel">
            <span class="agent-directory-hero__panel-eyebrow">Response Window</span>
            <h2>We route the right conversation to the right OmniReferral team.</h2>
            <p>Sales, support, partnership, and marketplace questions all follow the same branded intake flow, so your message does not get lost.</p>
            <div class="agent-directory-hero__stats contact-page-hero__stats">
                <div class="agent-directory-hero__stat">
                    <strong>&lt; 24h</strong>
                    <span>average response target</span>
                </div>
                <div class="agent-directory-hero__stat">
                    <strong>Mon-Fri</strong>
                    <span>9am-6pm ET support hours</span>
                </div>
                <div class="agent-directory-hero__stat">
                    <strong>Email</strong>
                    <span>hello@omnireferral.us</span>
                </div>
                <div class="agent-directory-hero__stat">
                    <strong>Phone</strong>
                    <span>(800) 555-0147</span>
                </div>
            </div>
        </aside>
    </div>
</section>

<section class="section contact-body-section">
    <div class="container contact-body-grid">

        {{-- LEFT: Contact Form --}}
        <div class="contact-form-col">
            <div class="contact-form-card">
                <div class="contact-form-card__header">
                    <h2>Send us a message</h2>
                    <p>Tell us what you need and we will guide you to the right next step.</p>
                </div>

                @if(session('success'))
                <div class="contact-success-alert" role="alert">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    <div>
                        <strong>Message sent successfully!</strong>
                        <span>Our team will respond within one business day.</span>
                    </div>
                </div>
                @endif

                <div id="contactSuccessInline" style="display:none;" class="contact-success-alert" role="alert">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    <div>
                        <strong>Message sent successfully!</strong>
                        <span>Our team will respond within one business day.</span>
                    </div>
                </div>

                <form class="contact-form-v2" id="contactForm" method="POST" action="{{ route('contact.submit') }}" novalidate>
                    @csrf

                    <div class="cf-row cf-row--2">
                        <div class="cf-field">
                            <label class="cf-label" for="contactName">Full name <span class="cf-req">*</span></label>
                            <input class="cf-input" type="text" name="name" id="contactName" placeholder="Taylor Morgan" required autocomplete="name" value="{{ old('name') }}">
                            @error('name')<span class="cf-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="cf-field">
                            <label class="cf-label" for="contactEmail">Email address <span class="cf-req">*</span></label>
                            <input class="cf-input" type="email" name="email" id="contactEmail" placeholder="you@example.com" required autocomplete="email" value="{{ old('email') }}">
                            @error('email')<span class="cf-error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="cf-row cf-row--2">
                        <div class="cf-field">
                            <label class="cf-label" for="contactPhone">Phone number</label>
                            <input class="cf-input" type="tel" name="phone" id="contactPhone" placeholder="(555) 123-4567" autocomplete="tel" value="{{ old('phone') }}">
                        </div>
                        <div class="cf-field">
                            <label class="cf-label" for="contactRole">I am a...</label>
                            <select class="cf-input cf-select" name="role" id="contactRole">
                                <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select role</option>
                                <option value="buyer" {{ old('role') === 'buyer' ? 'selected' : '' }}>Buyer</option>
                                <option value="seller" {{ old('role') === 'seller' ? 'selected' : '' }}>Seller</option>
                                <option value="agent" {{ old('role') === 'agent' ? 'selected' : '' }}>Agent / Realtor</option>
                                <option value="partner" {{ old('role') === 'partner' ? 'selected' : '' }}>Partner</option>
                            </select>
                        </div>
                    </div>

                    <div class="cf-field">
                        <label class="cf-label" for="contactSubject">Subject</label>
                        <input class="cf-input" type="text" name="subject" id="contactSubject" placeholder="How can we help you?" value="{{ old('subject') }}">
                    </div>

                    <div class="cf-field">
                        <label class="cf-label" for="contactMessage">Message <span class="cf-req">*</span></label>
                        <textarea class="cf-input cf-textarea" name="message" id="contactMessage" rows="5" placeholder="Tell us about your goals, timeline, or any specific questions..." required>{{ old('message') }}</textarea>
                        @error('message')<span class="cf-error">{{ $message }}</span>@enderror
                    </div>

                    <button class="button button--orange cf-submit-btn" type="submit" id="cfSubmitBtn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Send Message
                    </button>
                    <p class="cf-security-note">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        Safe and encrypted communication
                    </p>
                </form>
            </div>
        </div>

        {{-- RIGHT: Info Panel --}}
        <div class="contact-side-col">

            {{-- Contact details --}}
            <aside class="contact-side-card" aria-label="Contact details">
                <div class="contact-side-card__header">
                    <span class="eyebrow">Contact Details</span>
                    <h3 class="contact-side-card__title">Reach the right OmniReferral team faster.</h3>
                    <p class="contact-side-card__copy">
                        Choose the best contact path for sales, support, billing, or partnership questions.
                        Every inquiry is reviewed by a real team member and routed manually.
                    </p>
                </div>

                <div class="contact-side-list">
                    <a href="mailto:hello@omnireferral.us" class="contact-side-item">
                        <span class="contact-side-item__icon contact-side-item__icon--blue" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </span>
                        <span class="contact-side-item__body">
                            <span class="contact-side-item__label">Email support</span>
                            <span class="contact-side-item__value">hello@omnireferral.us</span>
                            <span class="contact-side-item__note">Best for package questions, billing help, onboarding, and detailed support requests.</span>
                        </span>
                    </a>

                    <a href="tel:+18005550147" class="contact-side-item">
                        <span class="contact-side-item__icon contact-side-item__icon--orange" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8 19.79 19.79 0 01.22 1.18 2 2 0 012.18 0H5.18a2 2 0 012 1.72c.128.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.14 7.94a16 16 0 006.29 6.29l1.3-1.3a2 2 0 012.11-.45c.907.339 1.85.572 2.81.7A2 2 0 0122 16.92z"/></svg>
                        </span>
                        <span class="contact-side-item__body">
                            <span class="contact-side-item__label">Direct line</span>
                            <span class="contact-side-item__value">(800) 555-0147</span>
                            <span class="contact-side-item__note">Best for urgent follow-up, sales conversations, and live support during business hours.</span>
                        </span>
                    </a>

                    <div class="contact-side-item contact-side-item--static">
                        <span class="contact-side-item__icon contact-side-item__icon--green" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </span>
                        <span class="contact-side-item__body">
                            <span class="contact-side-item__label">Office hours</span>
                            <span class="contact-side-item__value">Mon-Fri, 9am-6pm ET</span>
                            <span class="contact-side-item__note">Messages are monitored on weekdays and routed to the right team as quickly as possible.</span>
                        </span>
                    </div>

                    <div class="contact-side-item contact-side-item--static">
                        <span class="contact-side-item__icon contact-side-item__icon--purple" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </span>
                        <span class="contact-side-item__body">
                            <span class="contact-side-item__label">Support hub</span>
                            <span class="contact-side-item__value">New York, NY</span>
                            <span class="contact-side-item__note">Campaign coordination for agent packages, partnerships, and nationwide lead support.</span>
                        </span>
                    </div>
                </div>
            </aside>

            {{-- Response time card --}}
            <div class="contact-side-banner" aria-label="Response summary">
                <div class="contact-side-banner__stat">
                    <strong>&#60; 24h</strong>
                    <span>Average response time</span>
                </div>
                <div class="contact-side-banner__divider" aria-hidden="true"></div>
                <div class="contact-side-banner__stat">
                    <strong>Real Team</strong>
                    <span>No automated bots</span>
                </div>
            </div>

            {{-- Map --}}
            <div class="contact-location-card">
                <div class="contact-location-card__header">
                    <span class="eyebrow">Coverage</span>
                    <h3>Built in New York, supporting agent campaigns across U.S. markets.</h3>
                </div>

                <iframe
                    title="OmniReferral location map"
                    src="https://www.google.com/maps?q=New+York,+NY&output=embed"
                    loading="lazy"
                ></iframe>
            </div>
        </div>

    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('contactForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        var btn = document.getElementById('cfSubmitBtn');
        if (btn) {
            btn.textContent = 'Sending...';
            btn.disabled = true;
        }
    });
});
</script>
@endpush

@endsection
