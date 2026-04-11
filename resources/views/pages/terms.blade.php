@extends('layouts.app')

@section('content')
@php
    $policyLinks = [
        ['id' => 'overview', 'label' => 'Overview'],
        ['id' => 'communications', 'label' => 'Communications Consent'],
        ['id' => 'eligibility', 'label' => 'Eligibility'],
        ['id' => 'account-data', 'label' => 'Account Information'],
        ['id' => 'acceptable-use', 'label' => 'Acceptable Use'],
        ['id' => 'availability', 'label' => 'Availability and Suspension'],
        ['id' => 'providers', 'label' => 'Service Providers'],
        ['id' => 'user-data', 'label' => 'User Data and Privacy'],
        ['id' => 'fees', 'label' => 'Fees and Renewals'],
        ['id' => 'ownership', 'label' => 'Ownership and Confidentiality'],
        ['id' => 'disclaimers', 'label' => 'Disclaimers and Liability'],
        ['id' => 'miscellaneous', 'label' => 'Miscellaneous Terms'],
        ['id' => 'arbitration', 'label' => 'Arbitration'],
        ['id' => 'trademarks', 'label' => 'Trademark Disclaimer'],
        ['id' => 'contact', 'label' => 'Contact'],
    ];
@endphp

<section class="page-hero page-hero--omni legal-page-hero">
    <div class="container legal-page-hero__content">
        <div class="legal-page-hero__copy">
            <span class="eyebrow">Terms &amp; Conditions</span>
            <h1>Terms governing your use of OmniReferral services</h1>
            <p>OmniReferral combines artificial intelligence and human support to help consumers and real estate professionals move through the purchasing and referral process with more clarity, personalization, and informed decision-making.</p>
            <div class="legal-page-hero__chips">
                <span>Applies to website, web app, phone, and text services</span>
                <span>Includes communication consent and arbitration terms</span>
                <span>Last updated {{ now()->format('F j, Y') }}</span>
            </div>
        </div>

        <div class="legal-page-hero__panel cockpit-table-card">
            <span class="eyebrow">Important Notice</span>
            <h2>Using OmniReferral creates a binding agreement.</h2>
            <p>By accessing or using OmniReferral services, you accept these Terms and Conditions. If you do not agree to be bound by them, do not use OmniReferral services, submit your information, or communicate with us through the platform.</p>
            <div class="legal-page-hero__summary">
                <div>
                    <span>Primary contact</span>
                    <strong><a href="mailto:hello@omnireferral.us">hello@omnireferral.us</a></strong>
                </div>
                <div>
                    <span>Coverage</span>
                    <strong>Adults using OmniReferral in supported markets</strong>
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
            <article class="legal-card cockpit-table-card" id="overview">
                <span class="eyebrow">Overview</span>
                <h2>Scope, acceptance, and updates</h2>
                <p>In these Terms and Conditions, "OmniReferral," "we," "us," and "our" refer to OmniReferral and its affiliated entities. "You," "your," and "user" refer to the individual or organization using OmniReferral services.</p>
                <p>OmniReferral services include our phone-based and text-based services, website and web app services, and related websites, applications, web applications, text messages, email notifications, and other communication channels provided by OmniReferral.</p>
                <p>We may update these Terms and Conditions at any time. Changes become effective when posted on the website, and continued use of OmniReferral services after an update means you accept the revised terms.</p>
                <div class="legal-callout">
                    <strong>If you use OmniReferral on behalf of an organization, you represent that you have authority to bind that organization to these terms.</strong>
                    <p>If a separate written agreement exists between OmniReferral and your organization, that separate agreement controls to the extent of any conflict.</p>
                </div>
            </article>

            <article class="legal-card cockpit-table-card" id="communications">
                <span class="eyebrow">Communications Consent</span>
                <h2>Calls, texts, and automated communications</h2>
                <p>By using OmniReferral services and providing your phone number, you authorize OmniReferral, its affiliates, partners, employees, contractors, and service providers to contact you by phone call and text message at the wireless number you provide, including through automated dialing technology, prerecorded voice, and auto-generated text messaging.</p>
                <p>You understand that message and data rates may apply based on your carrier plan and that consent to receive calls or texts is not a condition of purchase.</p>
                <ul class="legal-list">
                    <li>Communications may be sent even if your number appears on a Do-Not-Call registry, to the extent permitted by law and your consent.</li>
                    <li>You must promptly notify OmniReferral if your wireless number changes.</li>
                    <li>You agree to indemnify OmniReferral and its service providers for claims arising from your failure to update your phone number, including claims under the Telephone Consumer Protection Act.</li>
                    <li>You may revoke phone consent by requesting placement on an internal Do Not Call list.</li>
                    <li>You may revoke text consent by replying <strong>STOP</strong> to an OmniReferral text message.</li>
                </ul>
                <div class="legal-callout">
                    <strong>By providing your phone number, you agree to receive autodialed and prerecorded calls and texts from or on behalf of OmniReferral.</strong>
                    <p>Consent is not a condition of purchase.</p>
                </div>
            </article>

            <article class="legal-card cockpit-table-card" id="eligibility">
                <span class="eyebrow">Eligibility</span>
                <h2>Who may use OmniReferral services</h2>
                <p>OmniReferral services are intended for adults and are not directed to minors. We do not knowingly collect personally identifiable information from children under the age of 13, and you should not provide information about anyone under 13 through the services.</p>
                <ul class="legal-list">
                    <li>You must have the legal capacity to enter into a binding agreement.</li>
                    <li>You may not allow a person under the age of 18 to use OmniReferral services through your mobile phone number.</li>
                    <li>The services are primarily intended for users in the United States and Canada, subject to applicable law and market availability.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="account-data">
                <span class="eyebrow">Account Information</span>
                <h2>Accuracy of information and responsibility for your number</h2>
                <p>To use OmniReferral services, you may be asked to provide your mobile phone number, email address, physical address, and other onboarding information. You must provide true, accurate, current, and complete information and keep it updated.</p>
                <p>Your right to access and use OmniReferral services is personal to you and is not transferable.</p>
                <ul class="legal-list">
                    <li>You are solely responsible for all use of OmniReferral services connected to your phone number, whether authorized or not.</li>
                    <li>You are responsible for the quality and integrity of your user data.</li>
                    <li>You must take reasonable precautions to prevent unauthorized access and promptly notify us of unauthorized use.</li>
                    <li>OmniReferral is not liable for loss or damage arising from unauthorized use through your phone number.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="acceptable-use">
                <span class="eyebrow">Acceptable Use</span>
                <h2>Permitted use, restrictions, and prohibited conduct</h2>
                <p>You may use OmniReferral services only for lawful purposes and only in accordance with these Terms and our Privacy Policy.</p>
                <ul class="legal-list">
                    <li>You may not restrict, inhibit, or prevent others from using or enjoying OmniReferral services.</li>
                    <li>You may not use OmniReferral services to defame, abuse, harass, threaten, offend, or violate the rights of others.</li>
                    <li>You may not modify, scrape, embed, frame, reverse engineer, decompile, disassemble, or derive source code from OmniReferral services without prior written permission, except where applicable law expressly allows it.</li>
                    <li>You may not transfer, resell, lease, license, or otherwise make OmniReferral services available to third parties on a standalone basis.</li>
                    <li>You must ensure your use complies with all applicable laws, third-party rights, and these Terms.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="availability">
                <span class="eyebrow">Availability and Suspension</span>
                <h2>Service availability, discontinuation, and suspension rights</h2>
                <p>OmniReferral does not guarantee uninterrupted availability of the services at all times. We may discontinue services for any particular user, group of users, or all users, in our discretion.</p>
                <p>These Terms begin when you click "I Accept" or first use the services and continue until terminated.</p>
                <ul class="legal-list">
                    <li>We may suspend access immediately for cause if you violate these Terms or if we reasonably believe you have done so.</li>
                    <li>We may suspend access where traffic or activity appears fraudulent or negatively impacts the platform.</li>
                    <li>We may suspend or stop services where applicable law, regulation, or operational constraints make continued service impractical or prohibited.</li>
                    <li>Where reasonably possible, we will attempt to notify you before suspension.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="providers">
                <span class="eyebrow">Service Providers</span>
                <h2>Third-party providers, partner referrals, and no endorsement</h2>
                <p>Through OmniReferral services, you may receive information about, or be connected with, third-party service providers such as real estate brokers, mortgage-related providers, insurance brokers, and other referral partners.</p>
                <ul class="legal-list">
                    <li>OmniReferral does not endorse or recommend third-party products or services.</li>
                    <li>OmniReferral is not your agent, advisor, lender, mortgage provider, or direct service provider.</li>
                    <li>You are solely responsible for investigating any provider before hiring or engaging them.</li>
                    <li>Third-party providers are solely responsible for the services they offer to you.</li>
                    <li>OmniReferral is not liable for losses, claims, fees, damages, or disputes arising from your use of any third-party provider or partner.</li>
                    <li>OmniReferral may exclude providers from search results or referral visibility if they do not meet conduct or performance standards.</li>
                </ul>
                <p>OmniReferral may conduct criminal or financial background checks on certain providers in its discretion, but doing so does not create a warranty, endorsement, or waiver of our disclaimers or liability limitations.</p>
                <div class="legal-callout">
                    <strong>If you accept a referral to one of our service or referral partners, you authorize OmniReferral to share your user data with that partner so they can offer products or services to you.</strong>
                    <p>Any separate agreement for those products or services is directly between you and that third-party partner.</p>
                </div>
            </article>

            <article class="legal-card cockpit-table-card" id="user-data">
                <span class="eyebrow">User Data and Privacy</span>
                <h2>How data, submissions, and content are handled</h2>
                <p>"User data" includes information made available through your use of OmniReferral services, including usage data and content exchanged through the platform such as text message bodies, images, voice, video, and related metadata.</p>
                <p>By submitting information that is not protected by federally registered intellectual property rights, you grant OmniReferral a worldwide, fully paid, royalty-free right to use, copy, format, adapt, publish, and incorporate that information in connection with the services.</p>
                <ul class="legal-list">
                    <li>You may not submit content that infringes intellectual property rights, violates law, contains malware or destructive code, constitutes spam, or is false, inaccurate, or misleading.</li>
                    <li>We may edit, refuse to post, review, or remove transmissions, submissions, or postings in our discretion.</li>
                    <li>You remain solely responsible for your submissions and the consequences of submitting them.</li>
                    <li>Your use of OmniReferral services is also governed by our Privacy Policy.</li>
                </ul>
                <p>OmniReferral may use or disclose user data as necessary to provide support, maintain the platform, respond to emergencies, detect fraud or unlawful use, comply with law, and protect OmniReferral, users, and the public.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="fees">
                <span class="eyebrow">Fees and Renewals</span>
                <h2>Membership fees, plan renewals, and referral payments</h2>
                <p>Unless you are a service provider, OmniReferral generally does not charge consumers a fee to use the platform. Service providers, agents, brokers, or referral partners may pay OmniReferral fees for participation, lead access, subscriptions, or referral activity.</p>
                <ul class="legal-list">
                    <li>OmniReferral may charge an agent or broker a non-refundable membership fee for a subscription or plan.</li>
                    <li>Paid plans may renew automatically using the payment method on file at the applicable membership fee on or about the renewal date unless canceled according to the governing plan terms.</li>
                    <li>OmniReferral may update pricing, fees, or plan terms with notice as permitted by law and contract.</li>
                    <li>Referral fees owed to OmniReferral are not reduced or waived because you owe commissions, referral fees, or other payments to third parties.</li>
                    <li>Broker or agent referral fees may be payable after closing and funding and may be due by ACH, wire transfer, or another approved method.</li>
                    <li>If you dispute a commission referral fee, per-lead fee, or signup fee, written notice must be given promptly under the applicable agreement terms or dispute rights may be waived.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="ownership">
                <span class="eyebrow">Ownership and Confidentiality</span>
                <h2>Intellectual property, feedback, and confidential information</h2>
                <p>As between you and OmniReferral, OmniReferral owns all rights, title, and interest in and to OmniReferral services and OmniReferral confidential information. You retain ownership of your own confidential information.</p>
                <ul class="legal-list">
                    <li>If you send suggestions or feedback about OmniReferral services, you agree that OmniReferral may use or disclose those suggestions for any purpose without compensation to you.</li>
                    <li>Both parties agree to protect the other's confidential information and use it only as permitted under these Terms.</li>
                    <li>Confidential information excludes information that is public through no fault of the receiving party, already known without restriction, lawfully received from another source, or independently developed without use of confidential information.</li>
                    <li>Each party may seek injunctive or equitable relief for actual or threatened breaches of confidentiality.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="disclaimers">
                <span class="eyebrow">Disclaimers and Liability</span>
                <h2>Warranty disclaimer, indemnification, and limitation of liability</h2>
                <p>OmniReferral services are provided <strong>"as is"</strong> and to the fullest extent permitted by law without warranties of any kind, whether express or implied, including implied warranties of merchantability, non-infringement, and fitness for a particular purpose.</p>
                <ul class="legal-list">
                    <li>You agree to defend, indemnify, and hold harmless OmniReferral and its officers, directors, employees, members, stockholders, and affiliates from claims, losses, damages, liabilities, settlements, judgments, costs, and reasonable attorneys' fees arising from your breach of these Terms or your use of OmniReferral services.</li>
                    <li>Neither party will be liable to the other for indirect, incidental, special, consequential, or punitive damages, including lost profits, lost business, loss of goodwill, work stoppage, or lost data, to the extent permitted by law.</li>
                    <li>To the extent permitted by law, each party's direct liability is capped at the amounts paid or payable by you during the twelve months preceding the incident or claim.</li>
                    <li>Certain limits do not apply to confidentiality breaches, indemnification obligations, or liabilities that cannot be excluded by law.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="miscellaneous">
                <span class="eyebrow">Miscellaneous Terms</span>
                <h2>Compliance, notices, assignment, force majeure, and entire agreement</h2>
                <ul class="legal-list">
                    <li>Both you and OmniReferral will comply with applicable law in connection with activities under these Terms.</li>
                    <li>A failure to enforce any provision does not waive the right to enforce it later.</li>
                    <li>You may not assign or transfer these Terms without prior written consent from OmniReferral.</li>
                    <li>If part of these Terms is held unenforceable, the remaining provisions continue in full force and effect except as provided in the arbitration section.</li>
                    <li>Either party may give notices in writing by personal delivery, certified mail, overnight courier, or email upon confirmation of receipt.</li>
                    <li>These Terms supersede prior and contemporaneous proposals, marketing materials, statements, or agreements relating to the same subject matter, except for applicable attachments or separate written agreements.</li>
                    <li>Neither party is liable for failure or delay caused by events beyond reasonable control, including governmental action, labor disputes, fire, flood, terrorism, war, riot, theft, earthquake, or other natural disasters.</li>
                    <li>Except for arbitration-specific rules, these Terms are governed by the laws of the State of Virginia without regard to conflict-of-law principles.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="arbitration">
                <span class="eyebrow">Arbitration</span>
                <h2>Binding arbitration and class action waiver</h2>
                <p>Before filing a formal legal case, you should first try to resolve disputes through OmniReferral customer support. If a dispute cannot be resolved informally, you and OmniReferral agree to resolve qualifying disputes through binding individual arbitration.</p>
                <ul class="legal-list">
                    <li>The enforceability and interpretation of the arbitration agreement are governed by the Federal Arbitration Act.</li>
                    <li>The policy text provided for this page specifies arbitration in Florida, or another mutually agreed location.</li>
                    <li>Before arbitration, the parties agree to attempt mediation through the American Arbitration Association.</li>
                    <li>Arbitration will proceed under the then-current AAA Commercial Arbitration Rules unless these Terms provide otherwise.</li>
                    <li>The arbitrator decides issues of scope and enforceability of the arbitration agreement.</li>
                    <li>Intellectual property disputes and certain proceedings to compel arbitration or stay court actions may be brought in court.</li>
                    <li>Claims must be brought on an individual basis only. Class, consolidated, and representative proceedings are waived to the extent permitted by law.</li>
                </ul>
                <div class="legal-callout">
                    <strong>By using OmniReferral services, you waive the right to have qualifying disputes decided by a judge or jury.</strong>
                    <p>You also waive the ability to bring or participate in class actions except where such waiver is not enforceable under applicable law.</p>
                </div>
            </article>

            <article class="legal-card cockpit-table-card" id="trademarks">
                <span class="eyebrow">Trademark Disclaimer</span>
                <h2>Third-party marks remain the property of their owners</h2>
                <p>All third-party trademarks, including logos and icons mentioned on this website, are the sole property of their respective owners. Unless explicitly stated, reference to those marks by OmniReferral does not imply association, sponsorship, or endorsement between OmniReferral and the trademark owners.</p>
                <p>Any reference to third-party trademarks is made solely for identification of relevant third-party services and is intended as nominative fair use where applicable.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="contact">
                <span class="eyebrow">Contact</span>
                <h2>Questions about these terms</h2>
                <p>If you have questions about these Terms and Conditions, account access, provider referrals, or legal notices, contact OmniReferral through the channels below.</p>
                <div class="legal-contact-grid">
                    <div>
                        <span>General support</span>
                        <strong><a href="mailto:hello@omnireferral.us">hello@omnireferral.us</a></strong>
                    </div>
                    <div>
                        <span>Contact page</span>
                        <strong><a href="{{ route('contact') }}">Open contact form</a></strong>
                    </div>
                    <div>
                        <span>Official website</span>
                        <strong><a href="{{ route('home') }}">omnireferral.us</a></strong>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>
@endsection
