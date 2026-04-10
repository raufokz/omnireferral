@extends('layouts.app')

@section('content')
@php
    $policyLinks = [
        ['id' => 'overview', 'label' => 'Overview'],
        ['id' => 'official-site', 'label' => 'Official Website'],
        ['id' => 'payment-requests', 'label' => 'Payment Request Scams'],
        ['id' => 'agent-sites', 'label' => 'Fake Agent Websites'],
        ['id' => 'tips', 'label' => 'Scam-Combating Tips'],
        ['id' => 'response', 'label' => 'If You May Have Been Scammed'],
        ['id' => 'credit-freeze', 'label' => 'Credit Freeze Resources'],
        ['id' => 'contact', 'label' => 'Contact'],
    ];
@endphp

<section class="page-hero page-hero--omni legal-page-hero">
    <div class="container legal-page-hero__content">
        <div class="legal-page-hero__copy">
            <span class="eyebrow">Scam Prevention</span>
            <h1>How to recognize and avoid OmniReferral impersonation scams</h1>
            <p>Like many companies, OmniReferral and its employees and agents may be targeted by bad actors using fraudulent schemes that impersonate our brand in an attempt to collect personal or financial information from consumers.</p>
            <div class="legal-page-hero__chips">
                <span>Official website: omnireferral.us</span>
                <span>Verify new payment requests before sending money</span>
                <span>Report suspicious activity to hello@omnireferral.us</span>
            </div>
        </div>

        <div class="legal-page-hero__panel cockpit-table-card">
            <span class="eyebrow">Stay Alert</span>
            <h2>Always double or triple-check emails, texts, and websites before sharing information.</h2>
            <p>Before engaging with anyone claiming to represent OmniReferral, verify the source carefully. Fraudsters may imitate our branding, our agents, or our websites to create urgency and pressure you into sharing sensitive information.</p>
            <div class="legal-page-hero__summary">
                <div>
                    <span>Official site</span>
                    <strong><a href="{{ route('home') }}">omnireferral.us</a></strong>
                </div>
                <div>
                    <span>Fraud reporting</span>
                    <strong><a href="mailto:hello@omnireferral.us">hello@omnireferral.us</a></strong>
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
                <nav class="legal-page-nav" aria-label="Scam prevention sections">
                    @foreach($policyLinks as $link)
                        <a href="#{{ $link['id'] }}">{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            </div>
        </aside>

        <div class="legal-page-main">
            <article class="legal-card cockpit-table-card" id="overview">
                <span class="eyebrow">Overview</span>
                <h2>Fraud awareness helps protect your information</h2>
                <p>Please exercise caution when communicating or working with anyone claiming to represent OmniReferral. Always review the source of an email, website, or text message carefully before sharing personal information, financial information, account credentials, or verification codes.</p>
                <p>Below are some of the most common scam patterns identified in the real estate and referral space. Awareness of these practices can help reduce the risk of fraud.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="official-site">
                <span class="eyebrow">Official Website</span>
                <h2>OmniReferral's official consumer-facing website</h2>
                <p><strong>OmniReferral.us is our only official consumer-facing website.</strong> Scammers may operate fake or copycat websites, including domains ending in <strong>.com</strong> or other variations, that falsely claim to represent OmniReferral.</p>
                <ul class="legal-list">
                    <li>These websites may closely resemble our official site and may display phone numbers, logos, or branding that appears legitimate.</li>
                    <li>Any website not hosted on <strong>omnireferral.us</strong> is unauthorized.</li>
                    <li>Our official website will be reflected as <strong>omnireferral.us</strong> or <strong>www.omnireferral.us</strong>.</li>
                </ul>
                <p>If you encounter a suspicious or copycat website, notify us immediately at <a href="mailto:hello@omnireferral.us">hello@omnireferral.us</a>.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="payment-requests">
                <span class="eyebrow">Payment Request Scams</span>
                <h2>Unexpected requests for Zelle, Venmo, or wire transfers are a major warning sign</h2>
                <p>Scammers posing as OmniReferral agents may contact prospective buyers, sellers, or renters and request payment through Zelle, Venmo, wire transfer, or other peer-to-peer methods using unfamiliar phone numbers or email addresses.</p>
                <div class="legal-callout">
                    <strong>Do not respond or click embedded links in suspicious payment requests.</strong>
                    <p>Instead, verify the request using the official contact information published on <a href="{{ route('home') }}">omnireferral.us</a>. Clicking suspicious links or replying to a scammer may expose you to phishing, malware, or additional fraud attempts.</p>
                </div>
                <p>OmniReferral does not request upfront payments through unofficial channels or from unverified contact details.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="agent-sites">
                <span class="eyebrow">Fake Agent Websites</span>
                <h2>Fraudsters may copy legitimate agent branding</h2>
                <p>Some OmniReferral agents may maintain their own independent websites, while others direct clients to their official presence on OmniReferral. Scammers may create fake agent websites using stolen photos, logos, or branding copied from legitimate agents or social media profiles.</p>
                <ul class="legal-list">
                    <li>These copycat sites are often minimally developed, unstable, or frequently offline.</li>
                    <li>If you are unsure whether an agent or website is legitimate, verify the agent's contact details directly through <a href="{{ route('home') }}">omnireferral.us</a>.</li>
                    <li>Be especially cautious when an outside website asks for payment, identity documents, or account credentials before you have verified the person through official OmniReferral channels.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="tips">
                <span class="eyebrow">Scam-Combating Tips</span>
                <h2>Practical ways to reduce fraud risk</h2>
                <ul class="legal-list">
                    <li>Do not share your password or one-time verification codes with anyone.</li>
                    <li>If you have already engaged an agent and receive communication from a new phone number or email address, verify that change using previously known contact details or through omnireferral.us.</li>
                    <li>If you receive a request for payment by wire transfer, money order, Zelle, or Venmo from an unfamiliar source, confirm the request using official contact information listed on omnireferral.us.</li>
                    <li>Be cautious of messages that create an unexpected sense of urgency. Scammers often rely on panic to pressure victims into acting before verifying legitimacy.</li>
                    <li>Only click links from trusted senders. Malware and phishing attacks are often embedded in unverified links.</li>
                    <li>Pay close attention to spelling, tone, and formatting in messages. Multiple spelling errors, unusual wording, or sudden changes in communication style can be warning signs.</li>
                    <li>Official OmniReferral email communications should come from an address ending in <strong>@omnireferral.us</strong>.</li>
                    <li>Limit the amount of personal information you share on social media. Publicly visible details such as travel plans, phone numbers, or addresses can increase fraud risk.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="response">
                <span class="eyebrow">Already Believe You May Have Been Scammed?</span>
                <h2>Take action quickly to reduce further harm</h2>
                <p>If you believe you shared personal or financial information with a scammer, contact your financial institution immediately. This may include your bank, credit card issuer, or payment service provider such as PayPal or Venmo. Ask about canceling fraudulent transactions and blocking future unauthorized charges.</p>
                <p>You may also consider filing a complaint with the Federal Trade Commission (FTC) or the FBI's Internet Crime Complaint Center (IC3), especially if money was sent or sensitive information was exposed.</p>
                <div class="legal-callout">
                    <strong>Fast response matters.</strong>
                    <p>The sooner you report suspicious activity, freeze exposed accounts, and change passwords, the better your chance of limiting additional fraud.</p>
                </div>
            </article>

            <article class="legal-card cockpit-table-card" id="credit-freeze">
                <span class="eyebrow">Credit Freeze Resources</span>
                <h2>Freeze your credit reports if identity theft may be involved</h2>
                <p>Freezing your credit reports can help prevent unauthorized accounts from being opened in your name. Visit each credit bureau's official website for the latest instructions on placing a security freeze.</p>
                <div class="legal-contact-grid">
                    <div>
                        <span>Equifax</span>
                        <strong>Freeze your Equifax credit report</strong>
                    </div>
                    <div>
                        <span>Experian</span>
                        <strong>Freeze your Experian credit report</strong>
                    </div>
                    <div>
                        <span>TransUnion</span>
                        <strong>Freeze your TransUnion credit report</strong>
                    </div>
                </div>
            </article>

            <article class="legal-card cockpit-table-card" id="contact">
                <span class="eyebrow">Contact</span>
                <h2>Report suspicious OmniReferral impersonation activity</h2>
                <p>If you find a suspicious website, receive a questionable payment request, or want to verify whether a contact is legitimate, reach out through OmniReferral's official channels before proceeding.</p>
                <div class="legal-contact-grid">
                    <div>
                        <span>Fraud reporting</span>
                        <strong><a href="mailto:hello@omnireferral.us">hello@omnireferral.us</a></strong>
                    </div>
                    <div>
                        <span>Official website</span>
                        <strong><a href="{{ route('home') }}">omnireferral.us</a></strong>
                    </div>
                    <div>
                        <span>Direct support</span>
                        <strong><a href="{{ route('contact') }}">Open contact page</a></strong>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>
@endsection
