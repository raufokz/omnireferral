@extends('layouts.app')

@section('content')
@php
    $policyLinks = [
        ['id' => 'overview', 'label' => 'Overview'],
        ['id' => 'promo', 'label' => 'Open Enrollment Promo'],
        ['id' => 'refund-policy', 'label' => 'Refund Policy'],
        ['id' => 'guarantee', 'label' => '160-Day Guarantee'],
        ['id' => 'refund-request', 'label' => 'Refund Request Process'],
        ['id' => 'refund-evaluation', 'label' => 'Refund Evaluation'],
        ['id' => 'eligibility', 'label' => 'Refund Eligibility'],
        ['id' => 'important-notes', 'label' => 'Important Notes'],
        ['id' => 'post-cancellation', 'label' => 'Post-Cancellation Revenue'],
        ['id' => 'cancellation', 'label' => 'Cancellation Process'],
        ['id' => 'contact-policy', 'label' => 'Contact'],
    ];
@endphp

<section class="page-hero page-hero--omni legal-page-hero">
    <div class="container legal-page-hero__content">
        <div class="legal-page-hero__copy">
            <span class="eyebrow">Payment &amp; Cancellation</span>
            <h1>Payment, Refund &amp; Cancellation Policy for OmniReferral</h1>
            <p>This page explains OmniReferral payment terms, refund review conditions, cancellation procedures, and promotional policy details for agent enrollment offers.</p>
            <div class="legal-page-hero__chips">
                <span>Last updated {{ now()->format('F j, Y') }}</span>
                <span>Includes payment, refund, and cancellation terms</span>
                <span>Applies to OmniReferral offers and memberships</span>
            </div>
        </div>

        <div class="legal-page-hero__panel cockpit-table-card">
            <span class="eyebrow">Policy Notice</span>
            <h2>Policy updates take effect when posted.</h2>
            <p>OmniReferral reserves the right to update this Payment, Refund &amp; Cancellation Policy at any time, for any reason. Any changes will be effective immediately upon posting on our website, and continued use of our services signifies agreement to the updated terms.</p>
            <div class="legal-page-hero__summary">
                <div>
                    <span>Support</span>
                    <strong><a href="mailto:support@omnireferral.us">support@omnireferral.us</a></strong>
                </div>
                <div>
                    <span>Cancellations</span>
                    <strong><a href="mailto:cancellations@omnireferral.us">cancellations@omnireferral.us</a></strong>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section--gray legal-page-section">
    <div class="container legal-page-layout">
        <aside class="legal-page-sidebar">
            <div class="legal-page-sidebar__card cockpit-table-card">
                <span class="eyebrow">On This Page</span>
                <nav class="legal-page-nav" aria-label="Payment and cancellation policy sections">
                    @foreach($policyLinks as $link)
                        <a href="#{{ $link['id'] }}">{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            </div>
        </aside>

        <div class="legal-page-main">
            <article class="legal-card cockpit-table-card" id="overview">
                <span class="eyebrow">Overview</span>
                <h2>Policy scope and acceptance</h2>
                <p>This Payment, Refund &amp; Cancellation Policy governs how OmniReferral handles membership payments, promotional offers, refund review requests, and service cancellations.</p>
                <p>Any revised version of this policy will reflect the most current "Last Updated" date above. Your continued use of OmniReferral services after changes are posted signifies your acceptance of those updated terms.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="promo">
                <span class="eyebrow">Open Enrollment Promo</span>
                <h2>5th Anniversary Edition promotional terms</h2>
                <p>As part of Realtor Open Enrollment and in celebration of the 5th anniversary campaign, OmniReferral may offer a limited promotional enrollment campaign across the U.S. and Canada. Based on the policy language provided, this promotional window runs through <strong>November 30, 2025</strong>.</p>
                <ul class="legal-list">
                    <li>Agents who enroll before the stated deadline may unlock 0% referral fees for Q4 2025, meaning no fees on closings from October 2025 through December 2025.</li>
                    <li>Eligible agents may also receive up to 80% discounts on referral fees through June 2026.</li>
                    <li>The promotion may include targeted buyer and seller referrals instead of recycled or cold leads.</li>
                    <li>The promotion may include exclusive area assignment, full social media boost, pipeline management support, and a holiday marketing kit with seasonal templates.</li>
                    <li>The campaign may also include an extended 160-day money-back guarantee and increased referral volume during the first half of 2026.</li>
                </ul>
                <div class="legal-callout">
                    <strong>Date note:</strong>
                    <p>This section uses the exact campaign timing from the text you provided, including November 30, 2025, Q4 2025, June 2026, and the first half of 2026.</p>
                </div>
            </article>

            <article class="legal-card cockpit-table-card" id="refund-policy">
                <span class="eyebrow">Refund Policy</span>
                <h2>General refund position</h2>
                <p>This Refund Policy explains the conditions under which a refund may be considered for an initial membership fee.</p>
                <p>Under the standard policy, membership fees are non-refundable. However, OmniReferral states that if it fails to meet promised service standards, a full or partial refund may be considered subject to review and the conditions outlined below.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="guarantee">
                <span class="eyebrow">160-Day Guarantee</span>
                <h2>Money-back performance review window</h2>
                <p>Agents who enroll during the Open Enrollment period are described as being covered by a <strong>160-day performance guarantee</strong>.</p>
                <p>If OmniReferral does not deliver any qualified referrals within 160 days from the enrollment date, the agent may request a full or partial refund, subject to account review and policy compliance.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="refund-request">
                <span class="eyebrow">Refund Request Process</span>
                <h2>How a refund request should be submitted</h2>
                <p>To request a refund, the policy states that the agent should email <a href="mailto:support@omnireferral.us">support@omnireferral.us</a> with the subject line <strong>"Refund Request"</strong>.</p>
                <ul class="legal-list">
                    <li>Include your full name.</li>
                    <li>Include your contact details.</li>
                    <li>Explain the reason for the request.</li>
                    <li>Attach any relevant supporting documentation.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="refund-evaluation">
                <span class="eyebrow">Refund Evaluation</span>
                <h2>How OmniReferral reviews requests</h2>
                <p>Once a request is received, OmniReferral may review the account activity, audit referral communications and delivery records, and may contact leads for verification where needed.</p>
                <p>The standard review process may take up to <strong>45 business days</strong>.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="eligibility">
                <span class="eyebrow">Refund Eligibility</span>
                <h2>When a refund may be considered</h2>
                <ul class="legal-list">
                    <li>If no referrals are delivered within 160 days and the agent has followed all communication and reporting guidelines, a full refund may be issued.</li>
                    <li>If a lead appears invalid, including being non-exclusive, fake, or unresponsive, the agent must notify OmniReferral within 48 hours of receipt and provide written details with supporting evidence.</li>
                    <li>OmniReferral will first attempt to replace the lead within 60 days.</li>
                    <li>If no replacement is made, a refund request may then be considered.</li>
                    <li>Reports must be submitted promptly, and failure to report issues within 24 hours of receiving a lead voids refund eligibility for that specific lead.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="important-notes">
                <span class="eyebrow">Important Notes</span>
                <h2>Additional refund limitations</h2>
                <ul class="legal-list">
                    <li>A change of mind is not a valid reason for a refund.</li>
                    <li>Refunds cannot exceed the amount paid for the current membership term.</li>
                    <li>OmniReferral reserves the right to verify all leads provided during the investigation process.</li>
                    <li>Refunds will not be processed if the agent or broker fails to provide activity updates or fails to communicate regarding leads.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="post-cancellation">
                <span class="eyebrow">Post-Cancellation Referral Revenue</span>
                <h2>Referral fees after cancellation</h2>
                <p>If you cancel your membership, OmniReferral retains the right to referral fees for any transactions resulting from leads delivered during your active membership term.</p>
                <p>This coverage remains effective for <strong>24 months from the lead delivery date</strong>, and any closings within that window remain subject to the referral fee outlined in your plan.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="cancellation">
                <span class="eyebrow">Cancellation Process</span>
                <h2>How to cancel a referral agreement</h2>
                <p>To cancel your referral agreement, send the cancellation request to <a href="mailto:cancellations@omnireferral.us">cancellations@omnireferral.us</a>.</p>
                <p>All cancellations are processed in accordance with OmniReferral's Terms of Service and any applicable agreement terms tied to your plan or enrollment offer.</p>
                <div class="legal-action-row">
                    <a href="mailto:cancellations@omnireferral.us" class="button button--orange">Email Cancellation Request</a>
                    <a href="{{ route('terms') }}" class="button button--ghost-blue">Read Terms of Service</a>
                </div>
            </article>

            <article class="legal-card cockpit-table-card" id="contact-policy">
                <span class="eyebrow">Contact</span>
                <h2>Questions about payment, refunds, or cancellations</h2>
                <p>If you need help with billing questions, refund requests, or cancellation procedures, contact OmniReferral using the channels below.</p>
                <div class="legal-contact-grid">
                    <div>
                        <span>Refund Requests</span>
                        <strong><a href="mailto:support@omnireferral.us">support@omnireferral.us</a></strong>
                    </div>
                    <div>
                        <span>Cancellations</span>
                        <strong><a href="mailto:cancellations@omnireferral.us">cancellations@omnireferral.us</a></strong>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>
@endsection
