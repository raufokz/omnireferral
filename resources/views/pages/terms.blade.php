@extends('layouts.app')

@section('content')
@php
    $company = config('omnireferral.company');
    $supportEmail = $company['support_email'];
    $infoEmail = $company['info_email'];
    $policyLinks = [
        ['id' => 'definitions', 'label' => 'Definitions'],
        ['id' => 'company-role', 'label' => 'Company Role'],
        ['id' => 'ai-technology', 'label' => 'AI Technology'],
        ['id' => 'referral-services', 'label' => 'Referral Services'],
        ['id' => 'user-responsibilities', 'label' => 'User Responsibilities'],
        ['id' => 'communications', 'label' => 'Data and Consent'],
        ['id' => 'privacy', 'label' => 'Privacy'],
        ['id' => 'payments', 'label' => 'Payments'],
        ['id' => 'pay-per-lead', 'label' => 'Pay-Per-Lead'],
        ['id' => 'refunds', 'label' => 'Refunds and Disputes'],
        ['id' => 'payment-processing', 'label' => 'Payment Processing'],
        ['id' => 'security-verification', 'label' => 'Security Verification'],
        ['id' => 'third-party-services', 'label' => 'Third-Party Services'],
        ['id' => 'intellectual-property', 'label' => 'Intellectual Property'],
        ['id' => 'confidentiality', 'label' => 'Confidentiality'],
        ['id' => 'liability', 'label' => 'Limitation of Liability'],
        ['id' => 'warranties', 'label' => 'Disclaimer of Warranties'],
        ['id' => 'termination', 'label' => 'Termination'],
        ['id' => 'governing-law', 'label' => 'Governing Law'],
        ['id' => 'changes', 'label' => 'Changes to Terms'],
        ['id' => 'severability', 'label' => 'Severability'],
        ['id' => 'entire-agreement', 'label' => 'Entire Agreement'],
        ['id' => 'contact', 'label' => 'Contact'],
    ];
@endphp

<section class="page-hero page-hero--omni legal-page-hero">
    <div class="container legal-page-hero__content">
        <div class="legal-page-hero__copy">
            <span class="eyebrow">Terms &amp; Conditions</span>
            <h1>Terms and Conditions for OMNI REFERRALS services</h1>
            <p>These Terms govern your access to and use of OMNI REFERRALS services, website, technology platforms, referral services, and related offerings.</p>
            <div class="legal-page-hero__chips">
                <span>Effective Date: June 30, 2025</span>
                <span>Operated by Seven Tech LLC</span>
                <span>Includes AI, referrals, payments, and data terms</span>
            </div>
        </div>

        <div class="legal-page-hero__panel cockpit-table-card">
            <span class="eyebrow">Important Notice</span>
            <h2>Using OMNI REFERRALS creates a binding agreement.</h2>
            <p>By accessing or using OMNI REFERRALS services, you agree to be bound by these Terms. If you do not agree with any part of these Terms, you may not access or use our services.</p>
            <div class="legal-page-hero__summary">
                <div>
                    <span>Primary contact</span>
                    <strong><a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a></strong>
                </div>
                <div>
                    <span>Operator</span>
                    <strong>Seven Tech LLC, Michigan</strong>
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
                <nav class="legal-page-nav" aria-label="Terms and conditions sections">
                    @foreach($policyLinks as $link)
                        <a href="#{{ $link['id'] }}">{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            </div>
        </aside>

        <div class="legal-page-main">
            <article class="legal-card cockpit-table-card" id="definitions">
                <span class="eyebrow">Introduction</span>
                <h2>Definitions and acceptance</h2>
                <p>OMNI REFERRALS helps individuals make informed real estate decisions by combining artificial intelligence and human intelligence. Our patent-pending AI technology enables large-scale, personalized text and phone conversations with customers.</p>
                <p>OMNI REFERRALS is operated by Seven Tech LLC, a Michigan-registered limited liability company. Seven Tech LLC serves as the parent company responsible for the ownership, operation, technology development, and management of OMNI REFERRALS.</p>
                <ul class="legal-list">
                    <li>"OMNI REFERRALS," "Company," "we," "us," or "our" refers to Seven Tech LLC and its OMNI REFERRALS platform, technology, services, employees, contractors, affiliates, and authorized representatives.</li>
                    <li>"User," "Client," "Customer," "you," or "your" refers to any individual, business, real estate professional, agent, broker, organization, or entity accessing or using our services.</li>
                    <li>"Lead" or "Referral" means a potential buyer, seller, homeowner, investor, or consumer inquiry provided through OMNI REFERRALS systems.</li>
                    <li>"Services" include AI-powered conversations, referral matching, lead qualification, customer engagement, data processing, appointment scheduling, technology solutions, and related services provided by OMNI REFERRALS.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="company-role">
                <span class="eyebrow">Company Role</span>
                <h2>Technology-enabled referral services</h2>
                <p>OMNI REFERRALS provides technology-enabled referral and customer engagement services.</p>
                <p>OMNI REFERRALS does not act as a real estate broker unless separately licensed and authorized under applicable state laws. We do not provide legal, financial, tax, or investment advice.</p>
                <p>Real estate decisions, negotiations, offers, contracts, and transactions are solely between the consumer and the applicable licensed real estate professional.</p>
                <p>Users acknowledge that OMNI REFERRALS may utilize artificial intelligence, automation, third-party technology providers, and human representatives to improve customer experience and service delivery.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="ai-technology">
                <span class="eyebrow">AI Technology</span>
                <h2>Artificial intelligence disclosure</h2>
                <p>OMNI REFERRALS uses artificial intelligence systems designed to assist with customer communication, qualification, scheduling, personalization, and referral processes.</p>
                <ul class="legal-list">
                    <li>AI-generated communications may be reviewed, enhanced, or supported by human representatives.</li>
                    <li>AI systems may occasionally produce inaccurate, incomplete, or unexpected outputs.</li>
                    <li>AI tools are provided as a service enhancement and do not replace professional judgment.</li>
                    <li>Users remain responsible for independently verifying information before making decisions.</li>
                </ul>
                <p>OMNI REFERRALS continuously improves its technology but does not guarantee that AI-generated content will always be error-free.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="referral-services">
                <span class="eyebrow">Referral Services</span>
                <h2>Real estate referral services</h2>
                <p>OMNI REFERRALS connects consumers with real estate professionals and referral partners based on availability, location, preferences, and other qualification factors.</p>
                <p>The Company does not guarantee:</p>
                <ul class="legal-list">
                    <li>a successful real estate transaction;</li>
                    <li>acceptance of a referral by a real estate professional;</li>
                    <li>closing of any transaction;</li>
                    <li>specific financial outcomes;</li>
                    <li>commission earnings.</li>
                </ul>
                <p>Referral relationships, commissions, agreements, and obligations between real estate professionals and consumers may be governed by separate agreements.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="user-responsibilities">
                <span class="eyebrow">User Responsibilities</span>
                <h2>Required user conduct</h2>
                <p>Users agree to:</p>
                <ul class="legal-list">
                    <li>provide accurate and complete information;</li>
                    <li>maintain the security of account credentials;</li>
                    <li>comply with all applicable laws and regulations;</li>
                    <li>avoid misuse of the platform;</li>
                    <li>not attempt to reverse engineer, copy, modify, or exploit Company technology;</li>
                    <li>not use the platform for fraudulent, misleading, unlawful, or unauthorized purposes.</li>
                </ul>
                <p>Any misuse may result in suspension or termination of access.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="communications">
                <span class="eyebrow">Data and Consent</span>
                <h2>Data collection and communication consent</h2>
                <p>By using OMNI REFERRALS services, users acknowledge that information may be collected, processed, stored, and used to provide requested services.</p>
                <p>Users may receive communications through:</p>
                <ul class="legal-list">
                    <li>phone calls;</li>
                    <li>SMS/text messages;</li>
                    <li>emails;</li>
                    <li>automated communications;</li>
                    <li>AI-assisted conversations;</li>
                    <li>notifications.</li>
                </ul>
                <p>By submitting information, users consent to receive communications related to their inquiries, referrals, services, and account activity. Users may opt out of certain communications where legally applicable.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="privacy">
                <span class="eyebrow">Privacy</span>
                <h2>Privacy and data protection</h2>
                <p>OMNI REFERRALS takes reasonable measures to protect user information.</p>
                <p>Information may be used for:</p>
                <ul class="legal-list">
                    <li>providing services;</li>
                    <li>improving technology;</li>
                    <li>personalization;</li>
                    <li>analytics;</li>
                    <li>customer support;</li>
                    <li>fraud prevention;</li>
                    <li>compliance purposes.</li>
                </ul>
                <p>OMNI REFERRALS does not sell personal information in violation of applicable privacy laws. Additional privacy practices are outlined in our <a href="{{ route('privacy') }}">Privacy Policy</a>.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="payments">
                <span class="eyebrow">Payments</span>
                <h2>Payments, fees, and billing</h2>
                <p>Certain OMNI REFERRALS services may require payment of setup fees, subscription fees, referral fees, service fees, or other applicable charges.</p>
                <p>By purchasing or subscribing to any service, you authorize OMNI REFERRALS and its authorized payment processors to charge the payment method provided.</p>
                <ul class="legal-list">
                    <li>All payment information provided must be accurate and authorized.</li>
                    <li>Users are responsible for all charges associated with their account.</li>
                    <li>Fees may vary depending on the selected service package, agreement, or promotional offer.</li>
                    <li>Taxes, government fees, or processing fees may apply where required.</li>
                </ul>
                <p>Unless otherwise stated in a separate written agreement, all payments are due according to the applicable billing terms.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="pay-per-lead">
                <span class="eyebrow">Pay-Per-Lead</span>
                <h2>Referral payment terms</h2>
                <p>For services involving pay-per-lead, referral, or performance-based pricing models:</p>
                <ul class="legal-list">
                    <li>OMNI REFERRALS may provide leads, referrals, customer information, call recordings, qualification details, and related information.</li>
                    <li>Users may have a limited acceptance or review period for evaluating referrals.</li>
                    <li>A lead accepted by the User may become a billable event according to the applicable service agreement.</li>
                    <li>Failure to review, reject, or dispute a lead within the designated timeframe may result in the lead being considered accepted.</li>
                </ul>
                <p>Accepted referrals remain subject to applicable payment obligations regardless of whether the User successfully closes a transaction.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="refunds">
                <span class="eyebrow">Refunds</span>
                <h2>Refunds and disputes</h2>
                <p>Fees paid for services, technology access, setup, subscriptions, or accepted referrals are generally non-refundable unless otherwise stated in writing. Refund requests may be reviewed on a case-by-case basis.</p>
                <p>Users agree to first contact OMNI REFERRALS directly regarding any billing concern before initiating a payment dispute or chargeback.</p>
                <p>Unauthorized chargebacks, fraudulent disputes, or misuse of payment systems may result in:</p>
                <ul class="legal-list">
                    <li>suspension or termination of services;</li>
                    <li>collection efforts;</li>
                    <li>removal from referral programs;</li>
                    <li>legal action where applicable.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="payment-processing">
                <span class="eyebrow">Payment Processing</span>
                <h2>Payment processing and PCI compliance</h2>
                <p>OMNI REFERRALS uses third-party payment processors to securely process transactions.</p>
                <p>OMNI REFERRALS does not directly store complete payment card information unless specifically disclosed and permitted under applicable security requirements.</p>
                <p>Users acknowledge that payment processing may be subject to the terms, privacy policies, and security practices of third-party payment providers.</p>
                <p>OMNI REFERRALS maintains reasonable administrative, technical, and organizational safeguards designed to support payment security and compliance with applicable industry standards, including Payment Card Industry Data Security Standards (PCI DSS), where applicable.</p>
                <p>Users agree not to submit payment information through unauthorized methods or attempt to bypass payment security systems.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="security-verification">
                <span class="eyebrow">Security Verification</span>
                <h2>CAPTCHA and security verification</h2>
                <p>To protect OMNI REFERRALS systems, users, and customers from fraud, abuse, automated attacks, and unauthorized access, OMNI REFERRALS may implement CAPTCHA systems, bot detection tools, identity verification processes, rate limiting, and other security measures.</p>
                <ul class="legal-list">
                    <li>Security verification measures may be required before accessing certain features.</li>
                    <li>Attempts to bypass, disable, manipulate, or interfere with security protections are prohibited.</li>
                    <li>OMNI REFERRALS may restrict or terminate access where suspicious activity is detected.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="third-party-services">
                <span class="eyebrow">Third-Party Services</span>
                <h2>Third-party integrations and providers</h2>
                <p>OMNI REFERRALS may integrate or rely on third-party services, including but not limited to:</p>
                <ul class="legal-list">
                    <li>payment processors;</li>
                    <li>communication providers;</li>
                    <li>CRM platforms;</li>
                    <li>AI technology providers;</li>
                    <li>analytics tools;</li>
                    <li>hosting providers.</li>
                </ul>
                <p>OMNI REFERRALS does not control third-party services and is not responsible for their independent actions, outages, policies, or performance.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="intellectual-property">
                <span class="eyebrow">Intellectual Property</span>
                <h2>Ownership of technology and materials</h2>
                <p>All OMNI REFERRALS technology, software, systems, branding, designs, content, processes, AI models, workflows, databases, and related materials are owned by or licensed to Seven Tech LLC.</p>
                <p>Users may not:</p>
                <ul class="legal-list">
                    <li>copy;</li>
                    <li>reproduce;</li>
                    <li>modify;</li>
                    <li>distribute;</li>
                    <li>reverse engineer;</li>
                    <li>resell;</li>
                    <li>exploit any Company intellectual property without written permission.</li>
                </ul>
                <p>All trademarks, logos, and brand assets remain the property of their respective owners.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="confidentiality">
                <span class="eyebrow">Confidentiality</span>
                <h2>Confidential information</h2>
                <p>Users may receive confidential information relating to OMNI REFERRALS, its technology, customers, partners, pricing, operations, or business practices.</p>
                <p>Users agree to keep confidential information private and not disclose it to unauthorized parties. This obligation survives termination of services.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="liability">
                <span class="eyebrow">Liability</span>
                <h2>Limitation of liability</h2>
                <p>To the maximum extent permitted by law, Seven Tech LLC, OMNI REFERRALS, its owners, employees, contractors, partners, and affiliates shall not be liable for:</p>
                <ul class="legal-list">
                    <li>indirect damages;</li>
                    <li>lost profits;</li>
                    <li>lost business opportunities;</li>
                    <li>data loss;</li>
                    <li>service interruptions;</li>
                    <li>technology failures;</li>
                    <li>third-party actions;</li>
                    <li>outcomes from real estate transactions.</li>
                </ul>
                <p>The Company's total liability shall not exceed the amount paid by the User to OMNI REFERRALS during the applicable period giving rise to the claim.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="warranties">
                <span class="eyebrow">Warranties</span>
                <h2>Disclaimer of warranties</h2>
                <p>OMNI REFERRALS provides services on an "as available" and "as is" basis.</p>
                <p>The Company does not guarantee:</p>
                <ul class="legal-list">
                    <li>uninterrupted service;</li>
                    <li>error-free operation;</li>
                    <li>specific results;</li>
                    <li>guaranteed referrals;</li>
                    <li>guaranteed revenue;</li>
                    <li>transaction completion.</li>
                </ul>
                <p>Users acknowledge that business outcomes depend on multiple factors outside the Company's control.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="termination">
                <span class="eyebrow">Termination</span>
                <h2>Termination of services</h2>
                <p>OMNI REFERRALS may suspend or terminate access if a User:</p>
                <ul class="legal-list">
                    <li>violates these Terms;</li>
                    <li>engages in fraudulent activity;</li>
                    <li>abuses the platform;</li>
                    <li>fails to pay required fees;</li>
                    <li>creates risk to customers, partners, or the Company.</li>
                </ul>
                <p>Upon termination, outstanding payment obligations remain due.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="governing-law">
                <span class="eyebrow">Governing Law</span>
                <h2>Michigan law</h2>
                <p>These Terms shall be governed by and interpreted according to the laws of the State of Michigan, without regard to conflict of law principles.</p>
                <p>Any disputes arising from these Terms shall be resolved in the appropriate courts located within Michigan unless otherwise agreed in writing.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="changes">
                <span class="eyebrow">Changes</span>
                <h2>Changes to terms</h2>
                <p>OMNI REFERRALS reserves the right to modify these Terms at any time.</p>
                <p>Updated Terms will become effective when posted or otherwise communicated. Continued use of services after updates constitutes acceptance of the revised Terms.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="severability">
                <span class="eyebrow">Severability</span>
                <h2>Severability</h2>
                <p>If any provision of these Terms is found invalid or unenforceable, the remaining provisions shall remain in full force and effect.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="entire-agreement">
                <span class="eyebrow">Entire Agreement</span>
                <h2>Entire agreement</h2>
                <p>These Terms constitute the entire agreement between the User and OMNI REFERRALS regarding use of the services and supersede prior communications or understandings relating to the subject matter.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="contact">
                <span class="eyebrow">Contact</span>
                <h2>Contact information</h2>
                <p>OMNI REFERRALS is a service operated by Seven Tech LLC in Michigan, United States.</p>
                <p>For questions regarding these Terms, users may contact OMNI REFERRALS through the official communication channels provided on our website.</p>
                <div class="legal-contact-grid">
                    <div>
                        <span>General support</span>
                        <strong><a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a></strong>
                    </div>
                    <div>
                        <span>General information</span>
                        <strong><a href="mailto:{{ $infoEmail }}">{{ $infoEmail }}</a></strong>
                    </div>
                    <div>
                        <span>Contact page</span>
                        <strong><a href="{{ route('contact') }}">Open contact form</a></strong>
                    </div>
                    <div>
                        <span>Official website</span>
                        <strong><a href="{{ route('home') }}">omnireferrals.com</a></strong>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>
@endsection
