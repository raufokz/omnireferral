@extends('layouts.app')

@section('content')
@php
    $policyLinks = [
        ['id' => 'overview', 'label' => 'Overview'],
        ['id' => 'data-collection', 'label' => '1. Data Collection'],
        ['id' => 'use-of-information', 'label' => '2. How Information Is Used'],
        ['id' => 'analytics-advertising', 'label' => '3. Analytics and Advertising'],
        ['id' => 'dnt', 'label' => '4. Do Not Track'],
        ['id' => 'data-governance', 'label' => '5. Data Handling'],
        ['id' => 'data-transfer', 'label' => '6. Data Transfer'],
        ['id' => 'minors', 'label' => '7. Privacy of Minors'],
        ['id' => 'data-protection', 'label' => '8. Data Protection'],
        ['id' => 'external-links', 'label' => '9. External Links'],
        ['id' => 'california', 'label' => '10. California Privacy'],
        ['id' => 'revisions', 'label' => '11. Policy Revisions'],
        ['id' => 'ccpa-opt-out', 'label' => '12. CCPA Opt-Out'],
        ['id' => 'cookies', 'label' => 'Cookie Policy'],
        ['id' => 'accessibility', 'label' => 'Accessibility'],
        ['id' => 'contact-privacy', 'label' => 'Contact'],
    ];
@endphp

<section class="page-hero page-hero--omni legal-page-hero">
    <div class="container legal-page-hero__content">
        <div class="legal-page-hero__copy">
            <span class="eyebrow">Privacy Policy</span>
            <h1>How OmniReferral collects, uses, and protects your information</h1>
            <p>This Privacy Policy explains how OmniReferral and its affiliated entities handle the information collected through our websites, products, tools, promotions, and related services.</p>
            <div class="legal-page-hero__chips">
                <span>Last updated {{ now()->format('F j, Y') }}</span>
                <span>Applies to OmniReferral services</span>
                <span>Includes cookies and accessibility</span>
            </div>
        </div>

        <div class="legal-page-hero__panel cockpit-table-card">
            <span class="eyebrow">Important Notice</span>
            <h2>Review this policy carefully before using the Services.</h2>
            <p>By using any part of the Services, you agree to the collection, use, and disclosure of your information as described in this Policy. If you do not agree, please do not use the Services.</p>
            <div class="legal-page-hero__summary">
                <div>
                    <span>Contact</span>
                    <strong>hello@omnireferral.us</strong>
                </div>
                <div>
                    <span>Policy Scope</span>
                    <strong>Website, tools, promotions, and support</strong>
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
                <nav class="legal-page-nav" aria-label="Privacy policy sections">
                    @foreach($policyLinks as $link)
                        <a href="#{{ $link['id'] }}">{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            </div>
        </aside>

        <div class="legal-page-main">
            <article class="legal-card cockpit-table-card" id="overview">
                <span class="eyebrow">Overview</span>
                <h2>Use of the Services and marketing consent</h2>
                <p>This Privacy Policy delineates the procedures by which OmniReferral and its affiliated entities manage the acquisition, utilization, and revelation of your data while you engage with our websites, products, tools, promotions, or other services covered by this Policy, collectively referred to as the "Services."</p>
                <p>By employing the Services, you are granting OmniReferral, its corporate parents, affiliates, and partners permission to send or facilitate the delivery of telemarketing promotions for products or services that may be relevant to your interests or those you have previously expressed interest in. These communications may include an automatic telephone dialing system, artificial or prerecorded voice, and text messages sent to the phone numbers you have supplied. Accepting this arrangement is not mandatory for purchasing any property, goods, or services.</p>
                <p>Before engaging with or providing any information through or related to the Services, it is strongly recommended that you thoroughly review this Policy.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="data-collection">
                <span class="eyebrow">1. Data Collection</span>
                <h2>Information we collect</h2>
                <p>OmniReferral uses multiple methods to acquire information from users, including information provided directly by users and information collected passively from browsers or devices.</p>
                <ul class="legal-list">
                    <li>We collect information when users respond to communications, contact us directly, register for an account, use features that require information, complete surveys, provide feedback, request features, or contribute user-generated content.</li>
                    <li>Information collected directly from users may include name, email address, physical address, phone number, birthdate, property interests, home-search preferences, photographs, audio or video content, and financial information.</li>
                    <li>We may passively collect information from the computers or devices used to access the Services, including IP address, geolocation data, unique device identifiers, IMEI or TCP/IP addresses, browser type, browser language, operating system, mobile carrier information, state or country of access, and user interaction data.</li>
                    <li>To collect this data, we may use cookies, tracking pixels, web beacons, and related technologies to recognize devices, store preferences, track visited pages, improve user experience, perform analytics, and support security and administration.</li>
                    <li>We may collect general and precise location information, including ZIP code, IP-based location, and GPS-enabled device data where available, to provide location-based content and features such as listings that may be relevant to you.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="use-of-information">
                <span class="eyebrow">2. How Your Information Is Utilized</span>
                <h2>How OmniReferral uses your information</h2>
                <p>At OmniReferral, we use the information you provide to operate the Services, improve your experience, and support lead routing, customer service, and platform operations.</p>
                <div class="legal-callout">
                    <strong>Mobile information will not be shared with third parties or affiliates for marketing or promotional purposes.</strong>
                    <p>All categories exclude text messaging originator opt-in data and consent; that information will not be shared with third parties.</p>
                </div>
                <ul class="legal-list">
                    <li>Using information for the specific purpose for which it was provided.</li>
                    <li>Recognizing and authenticating users on the Services.</li>
                    <li>Providing the features, services, and products available through the Services.</li>
                    <li>Processing transactions and sending related information such as confirmations and invoices.</li>
                    <li>Sending technical notices, updates, account or security alerts, newsletters, and other service communications.</li>
                    <li>Processing and responding to user inquiries and support requests.</li>
                    <li>Conducting internal research, reporting, trend analysis, usage analysis, and service improvement.</li>
                    <li>Measuring effectiveness of content, features, services, and developing new offerings.</li>
                    <li>Personalizing content and advertising on the Services or other websites.</li>
                    <li>With user consent, contacting users by phone or SMS regarding the Services, their relationship with OmniReferral, or offers that may interest them.</li>
                    <li>Detecting, investigating, and preventing fraud or other illegal activities.</li>
                    <li>Enforcing the legal terms that govern users' use of the Services.</li>
                    <li>Administering and troubleshooting the Services.</li>
                </ul>
                <p><strong>Consent:</strong> We may share your information with partner agents if you have given consent. For example, if you are working with a real estate brokerage or searching for a property, we may share your information and preferences with them.</p>
                <p><strong>Service Providers and Subcontractors:</strong> We may provide subcontractors access to your information to perform services on our behalf, including marketing, market research, customer support, data storage, analysis, processing, and legal services.</p>
                <p><strong>Public Record Information:</strong> Certain information related to home sales and purchases, such as buyer name and address, price paid, and property taxes, may become part of the public record. We may disclose information that typically becomes publicly available through a home sale or purchase.</p>
                <p><strong>Protection of OmniReferral and Others:</strong> We may access, preserve, and disclose your information when required by law or where we have a good-faith belief such action is permitted by this Policy to comply with legal process, enforce contracts, respond to rights claims, address customer service issues, protect rights, property, or safety, and prevent fraud, spam, malware, or abuse.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="analytics-advertising">
                <span class="eyebrow">3. Online Analytics and Advertising</span>
                <h2>Analytics tools, advertising technologies, and retargeting</h2>
                <p>We use third-party analytics services to collect and analyze information from the Services for auditing, research, reporting, and fraud prevention. These providers may collect information including IP address using cookies, clear GIFs, web beacons, and similar technologies.</p>
                <ul class="legal-list">
                    <li>Analytics tools help us measure traffic sources, usage patterns, email engagement, feature usage, and the effectiveness of communications and campaigns.</li>
                    <li>We may use third-party advertising technologies to show relevant content and advertisements on our Services and on other websites you visit.</li>
                    <li>Advertising and website analytics partners may place cookies in your browser and may tailor ads based on page content, provided information, searches, demographic signals, user-generated content, and historical activity.</li>
                    <li>We may allow ad networks or ad servers to deliver personalized ads and to access their own cookies or tracking technologies on devices you use to access the Services.</li>
                    <li>We may provide customer information, such as email addresses, to service providers that anonymize and match this information with cookies or other identifiers to target or retarget advertising.</li>
                </ul>
                <p>Even if you opt out of certain targeted advertising tools, you may still receive advertisements, but those ads may be less personalized.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="dnt">
                <span class="eyebrow">4. Do Not Track</span>
                <h2>Browser-based DNT disclosures</h2>
                <p>At present, OmniReferral does not recognize or respond to browser-initiated Do Not Track (DNT) signals. The meaning of DNT and a uniform industry standard for responding to those signals are still evolving.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="data-governance">
                <span class="eyebrow">5. Data Handling and Governance</span>
                <h2>Access, updates, deletion, and retention</h2>
                <p>Subject to applicable law, you may request access to certain information stored by us and request corrections to help ensure accuracy.</p>
                <ul class="legal-list">
                    <li>If information you provided changes or is inaccurate, you should notify us promptly so it can be updated.</li>
                    <li>You may request deletion or modification of your information by using the Services or contacting us at <a href="mailto:hello@omnireferral.us">hello@omnireferral.us</a>.</li>
                    <li>Upon request, we will close your account and remove your information from view as soon as reasonably possible.</li>
                    <li>We may retain certain data where required by law or for legitimate business purposes.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="data-transfer">
                <span class="eyebrow">6. Consent for Data Transfer</span>
                <h2>International and cross-border processing</h2>
                <p>Our computer systems are located in the United States and may also be located in other countries. By using the Services, you acknowledge and agree to this Privacy Policy, including the transfer of your information to those countries.</p>
                <p>You understand that privacy and data protection laws in those countries may differ from those in your country of residence, and you agree that your information may be handled as described in this Policy.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="minors">
                <span class="eyebrow">7. Privacy of Minors</span>
                <h2>Children under 13</h2>
                <p>The Services are not intended for children under the age of 13. If OmniReferral becomes aware that personal information has been collected from a child under 13, we will take reasonable steps to delete that information in compliance with applicable law, including the Children's Online Privacy Protection Act where applicable.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="data-protection">
                <span class="eyebrow">8. Data Protection</span>
                <h2>Security measures and mobile data protections</h2>
                <p>We implement administrative, technical, and physical safeguards to protect your information from loss, misuse, alteration, unauthorized access, use, or disclosure. However, no system can be guaranteed to be completely secure.</p>
                <p>It is your responsibility to maintain the security of your account and, if you use the Services through SMS or mobile devices, to prevent unauthorized access to those devices.</p>
                <div class="legal-callout">
                    <strong>No mobile information will be shared with third parties or affiliates for marketing or promotional purposes.</strong>
                    <p>Information sharing to subcontractors in support services, such as customer service, is permitted. All other use-case categories exclude text messaging originator opt-in data and consent, and that information will not be shared with third parties.</p>
                </div>
            </article>

            <article class="legal-card cockpit-table-card" id="external-links">
                <span class="eyebrow">9. External Links and Services</span>
                <h2>Third-party sites and tools</h2>
                <p>The Services may contain links to third-party websites, applications, and services. OmniReferral is not responsible for the privacy practices of those third parties. We encourage you to review the privacy statements of any site that collects your information before leaving our Services.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="california">
                <span class="eyebrow">10. California Privacy Protections</span>
                <h2>California direct-marketing disclosures</h2>
                <p>Under California law, residents may have the right to request information once each year regarding the disclosure of their personal information to third parties for direct marketing purposes.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="revisions">
                <span class="eyebrow">11. Revisions to this Privacy Policy</span>
                <h2>How updates to this policy work</h2>
                <p>OmniReferral reserves the right to modify and update this Privacy Policy at any time without prior notice. Changes take effect when posted on the website unless a later effective date is stated.</p>
                <p>Your continued use of the Services after any changes are posted indicates your acceptance of the updated Policy.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="ccpa-opt-out">
                <span class="eyebrow">12. Opt-Out Rights for California Consumers</span>
                <h2>CCPA-related notice</h2>
                <p>Under the California Consumer Privacy Act (CCPA), California consumers may have the right to opt out of the sale of personal information, subject to the scope and definitions set by applicable law.</p>
                <p>Please review this Policy thoroughly before using or providing information through the Services. By using the Services, you agree to the collection, use, and disclosure of your information as described here. If you do not agree, please refrain from using the Services.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="cookies">
                <span class="eyebrow">Cookie Policy</span>
                <h2>How cookies and similar technologies are used</h2>
                <p>OmniReferral uses cookies, tracking pixels, web beacons, and similar technologies to recognize devices, remember settings, improve navigation, support login and account functions, analyze usage patterns, measure advertising performance, and improve the Services.</p>
                <ul class="legal-list">
                    <li>Some cookies are necessary for site functionality and security.</li>
                    <li>Some cookies help us understand performance, traffic, and engagement.</li>
                    <li>Some cookies or similar technologies may support advertising and retargeting.</li>
                </ul>
                <p>You may be able to control certain cookies through your browser settings or third-party opt-out tools. Disabling some cookies may affect how the Services function.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="accessibility">
                <span class="eyebrow">Accessibility</span>
                <h2>Access to this policy and our Services</h2>
                <p>OmniReferral aims to make its website and legal information reasonably accessible. If you need this Privacy Policy in another format or need assistance accessing any part of the Services, please contact us and we will try to provide a reasonable accommodation where possible.</p>
                <div class="legal-action-row">
                    <a href="{{ route('contact') }}" class="button button--orange">Contact OmniReferral</a>
                    <a href="mailto:hello@omnireferral.us" class="button button--ghost-blue">Email Support</a>
                </div>
            </article>

            <article class="legal-card cockpit-table-card" id="contact-privacy">
                <span class="eyebrow">Contact</span>
                <h2>Questions about this Privacy Policy</h2>
                <p>If you have questions about this Privacy Policy or want to request access, updates, or deletion of your information, contact OmniReferral through our contact page or by email.</p>
                <div class="legal-contact-grid">
                    <div>
                        <span>Email</span>
                        <strong><a href="mailto:hello@omnireferral.us">hello@omnireferral.us</a></strong>
                    </div>
                    <div>
                        <span>Contact Page</span>
                        <strong><a href="{{ route('contact') }}">Open Contact Form</a></strong>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>
@endsection
