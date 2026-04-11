@extends('layouts.app')

@section('content')
@php
    $policyLinks = [
        ['id' => 'overview', 'label' => 'Overview'],
        ['id' => 'channels', 'label' => 'Communication Channels'],
        ['id' => 'consent', 'label' => 'Consent to Contact'],
        ['id' => 'service-availability', 'label' => 'Service Availability'],
        ['id' => 'sms-alerts', 'label' => 'SMS and MMS Alerts'],
        ['id' => 'message-types', 'label' => 'Messaging Categories'],
        ['id' => 'call-recording', 'label' => 'Call Recording'],
        ['id' => 'social-platforms', 'label' => 'Social Platforms'],
        ['id' => 'sharing', 'label' => 'Information Sharing'],
        ['id' => 'opt-out', 'label' => 'Email and Opt-Out Rights'],
        ['id' => 'arbitration', 'label' => 'Dispute Resolution'],
        ['id' => 'contact', 'label' => 'Contact'],
    ];
@endphp

<section class="page-hero page-hero--omni legal-page-hero">
    <div class="container legal-page-hero__content">
        <div class="legal-page-hero__copy">
            <span class="eyebrow">Communication Policy</span>
            <h1>How OmniReferral communicates with users and handles consent</h1>
            <p>This Communication Policy outlines how OmniReferral communicates with users across calls, texts, email, apps, social platforms, and related services, including guidelines intended to support compliance with the Telephone Consumer Protection Act and similar communication rules.</p>
            <div class="legal-page-hero__chips">
                <span>Includes SMS, voice, social, and email practices</span>
                <span>Explains opt-out rights and call recording</span>
                <span>Last updated {{ now()->format('F j, Y') }}</span>
            </div>
        </div>

        <div class="legal-page-hero__panel cockpit-table-card">
            <span class="eyebrow">Important Notice</span>
            <h2>This policy includes individual arbitration and class-action waiver language.</h2>
            <p>Please review this page carefully. It describes our communication standards, consent framework, and dispute-resolution process, including a mandatory arbitration provision for qualifying disputes on an individual basis.</p>
            <div class="legal-page-hero__summary">
                <div>
                    <span>Support email</span>
                    <strong><a href="mailto:hello@omnireferral.us">hello@omnireferral.us</a></strong>
                </div>
                <div>
                    <span>SMS help</span>
                    <strong>Text HELP to +1 (786) 633-1912</strong>
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
                <nav class="legal-page-nav" aria-label="Communication policy sections">
                    @foreach($policyLinks as $link)
                        <a href="#{{ $link['id'] }}">{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            </div>
        </aside>

        <div class="legal-page-main">
            <article class="legal-card cockpit-table-card" id="overview">
                <span class="eyebrow">Overview</span>
                <h2>Scope of this communication policy</h2>
                <p>OmniReferral values the privacy of its users. This Communication Policy explains our practices concerning the collection, use, disclosure, and protection of information when you visit <strong>omnireferral.us</strong> or use any related site, app, communication channel, or service connected to OmniReferral.</p>
                <p>We encourage you to review this policy carefully. If you do not agree with its terms, you should not access or use the Site or related communication services.</p>
                <p>We may revise this policy at any time by updating the posted version and its "Last Updated" date. Continued use after a revision is posted means you accept the updated policy.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="channels">
                <span class="eyebrow">Communication Channels</span>
                <h2>How OmniReferral may contact you</h2>
                <p>OmniReferral reserves the right to communicate with you through a range of channels on behalf of itself, its affiliates, and, where permitted, advertisers or partners.</p>
                <ul class="legal-list">
                    <li>SMS and MMS text messages</li>
                    <li>Voice calls and prerecorded calls</li>
                    <li>Email messages</li>
                    <li>Website or mobile application notifications</li>
                    <li>Facebook, Google, Instagram, Pinterest, Twitter, WhatsApp, and similar communication or social platforms</li>
                    <li>Other channels reasonably available for support, transactional updates, or permitted marketing communications</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="consent">
                <span class="eyebrow">Consent to Contact</span>
                <h2>Telemarketing, consent standards, and future contact</h2>
                <p>By accepting this policy and providing your contact information, you grant OmniReferral, its corporate parents, affiliates, partners, members, and authorized third parties permission to deliver communications and telemarketing promotions that may be relevant to your interests, including communications unrelated to a specific product or service you originally requested.</p>
                <ul class="legal-list">
                    <li>Communications may use an automatic telephone dialing system, prerecorded or artificial voice, and text messaging.</li>
                    <li>Consent to these communications is not a condition of purchasing property, goods, or services.</li>
                    <li>Promotions may be delivered directly by OmniReferral or by authorized third parties acting on OmniReferral's behalf.</li>
                </ul>
                <div class="legal-callout">
                    <strong>Consent requirements vary by communication type.</strong>
                    <p>Conversational communications may rely on implied consent, while informational and promotional communications generally require express permission under applicable law and channel-specific rules.</p>
                </div>
            </article>

            <article class="legal-card cockpit-table-card" id="service-availability">
                <span class="eyebrow">Service Availability</span>
                <h2>Communication services are provided on an as-is basis</h2>
                <p>OmniReferral communication services are provided <strong>as is</strong> and may not be available in all locations, through all wireless carriers, or at all times. Functionality may stop working because of carrier changes, device software changes, coverage limitations, product modifications, or third-party service interruptions.</p>
                <ul class="legal-list">
                    <li>OmniReferral may discontinue or modify communication programs without prior notice or liability.</li>
                    <li>OmniReferral, its related companies, and their officers, directors, employees, and consultants are not liable for losses or injuries arising directly or indirectly from communications or technical failures or delays.</li>
                    <li>Wireless carriers and service providers are not responsible for delayed or undelivered text alerts.</li>
                    <li>OmniReferral may terminate delivery of communications to any individual at any time in its discretion.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="sms-alerts">
                <span class="eyebrow">SMS and MMS Alerts</span>
                <h2>Enrollment, message frequency, charges, HELP, and STOP</h2>
                <p>OmniReferral or its vendors may provide recurring SMS or MMS alerts related to orders, services, delivery updates, account activity, support, or related business communications. To enroll, you must provide a mobile phone number with area code and complete the applicable opt-in process.</p>
                <ul class="legal-list">
                    <li>Message frequency varies based on your activity, engagement, and the nature of the service requested.</li>
                    <li>OmniReferral does not separately charge for text alerts, but your carrier's message and data rates may apply.</li>
                    <li>Anyone with access to your device or carrier account may be able to view messages containing limited personal information.</li>
                    <li>For support, text <strong>HELP</strong> to <strong>+1 (786) 633-1912</strong>.</li>
                    <li>To opt out at any time, reply <strong>STOP</strong> to a message from OmniReferral. You may receive a final confirmation text after opting out.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="message-types">
                <span class="eyebrow">Messaging Categories</span>
                <h2>Conversational, informational, and promotional messaging</h2>
                <p>Different levels of consent may apply depending on whether a message is conversational, informational, or promotional and whether it relates to an existing transaction or marketing activity.</p>
                <ul class="legal-list">
                    <li><strong>Conversational messaging:</strong> A one-to-one exchange with an existing customer or known contact. If a user texts first and OmniReferral replies promptly with relevant information, the communication may rely on implied consent.</li>
                    <li><strong>Informational messaging:</strong> Messages that provide updates about services, products, account activity, scheduling, or related business matters. These generally require express permission after the user provides a phone number and agrees to future contact.</li>
                    <li><strong>Promotional messaging:</strong> Messages used to market or sell services. These generally require affirmative opt-in, such as signing a form, checking a box online, or another compliant enrollment step.</li>
                    <li><strong>Calls to action:</strong> Text messages may include links or buttons that prompt an action such as paying an invoice, opting in or out, visiting a website, viewing a calendar, or accessing another online property.</li>
                </ul>
                <p>Clients may opt out of OmniReferral text communications by using keywords such as <strong>STOP</strong> or <strong>UNSUBSCRIBE</strong>, or by indicating that they do not want to receive future calls where such an option is legally applicable.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="call-recording">
                <span class="eyebrow">Call Recording</span>
                <h2>Recording, monitoring, storage, and consent to recorded calls</h2>
                <p>OmniReferral may record incoming and outgoing calls for lawful purposes including quality monitoring, compliance, training, coaching, complaint investigation, employee safety, abuse prevention, and crime prevention or detection.</p>
                <p>Whenever reasonably possible, OmniReferral will notify participants that a call may be monitored or recorded so they may choose whether to continue. If you continue with the call or accept this policy, you consent to calls being monitored or recorded for the purposes described here.</p>
                <ul class="legal-list">
                    <li>Recorded data is processed lawfully and handled in accordance with applicable law.</li>
                    <li>Collected data should be adequate, relevant, and not excessive.</li>
                    <li>Data is used only for the specified purposes described in this policy.</li>
                    <li>Access is limited to designated managerial or senior staff and specifically authorized personnel.</li>
                    <li>Data is handled confidentially, stored securely, and retained only as long as necessary before secure destruction.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="social-platforms">
                <span class="eyebrow">Social Platforms</span>
                <h2>Linked accounts, platform permissions, and user-submitted activities</h2>
                <p>If you use OmniReferral through or alongside third-party platforms such as Facebook, Google, Instagram, Pinterest, Twitter, WhatsApp, or similar services, OmniReferral may receive certain profile or account information that the platform makes available based on your settings and permissions.</p>
                <ul class="legal-list">
                    <li>That information may include your name, email address, social username, location, gender, birth date, profile image, and other public profile data.</li>
                    <li>If you use a mobile application or invite contacts through integrated features, OmniReferral may receive information about invited contacts where the platform provides it.</li>
                    <li>If you participate in surveys, contests, or giveaways, OmniReferral may collect the information you submit in connection with those activities.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="sharing">
                <span class="eyebrow">Information Sharing</span>
                <h2>How communication-related information may be disclosed</h2>
                <p>OmniReferral may disclose information about you where reasonably necessary to comply with law, investigate possible policy violations, protect rights, property, or safety, reduce fraud or credit risk, or operate the platform with third-party support.</p>
                <ul class="legal-list">
                    <li>Third-party service providers such as payment processors, data analysts, email delivery vendors, hosting providers, customer-service vendors, and marketing support partners.</li>
                    <li>Marketing partners or other third parties where you have given consent or where law otherwise permits the communication.</li>
                    <li>Other users when you interact publicly on the site or app, including posts, comments, invitations, or profile interactions.</li>
                    <li>Advertising providers that may use cookie-based data to help serve interest-based ads.</li>
                    <li>Affiliates, parent companies, subsidiaries, joint-venture entities, business partners, advertisers, investors, or successor entities in a merger, sale, reorganization, or bankruptcy-related transaction.</li>
                </ul>
                <p>OmniReferral is not responsible for the conduct of third parties with whom you independently share personal or sensitive data.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="opt-out">
                <span class="eyebrow">Email and Opt-Out Rights</span>
                <h2>How to manage preferences and stop communications</h2>
                <p>If you do not wish to receive correspondence, email, or other communications from OmniReferral, you may manage your preferences using any available registration settings, account settings, or direct contact channels.</p>
                <ul class="legal-list">
                    <li>Set or adjust preferences during registration where available.</li>
                    <li>Log in to your account and update communication settings where the feature exists.</li>
                    <li>Contact OmniReferral directly using the contact information listed below.</li>
                    <li>To stop texts, reply <strong>STOP</strong>.</li>
                </ul>
                <p>If you want to stop receiving messages from third parties, you must contact those third parties directly.</p>
            </article>

            <article class="legal-card cockpit-table-card" id="arbitration">
                <span class="eyebrow">Dispute Resolution</span>
                <h2>Mandatory individual arbitration for qualifying disputes</h2>
                <p>For disputes arising out of or related to this Communication Policy or your relationship with OmniReferral, the parties agree to resolve qualifying disputes through arbitration before a neutral arbitrator instead of in court, except where a claim properly qualifies for small claims court or another non-waivable legal forum.</p>
                <ul class="legal-list">
                    <li>By agreeing to this policy, you waive the right to a jury trial for qualifying disputes.</li>
                    <li>You also waive the right to participate in class actions or representative actions to the extent permitted by law.</li>
                    <li>Arbitration will be administered by the American Arbitration Association under its Consumer Arbitration Rules, as modified by the governing agreement where applicable.</li>
                    <li>The arbitrator may award injunctive relief or specific performance where warranted by the individual claim.</li>
                    <li>If a portion of the dispute-resolution provision is found invalid, the remaining parts remain effective unless the class-action waiver itself is held unenforceable, in which case the full arbitration provision may become void.</li>
                </ul>
            </article>

            <article class="legal-card cockpit-table-card" id="contact">
                <span class="eyebrow">Contact</span>
                <h2>Questions about this communication policy</h2>
                <p>If you have questions or feedback about this Communication Policy, or want help updating your communication preferences, contact OmniReferral through the channels below.</p>
                <div class="legal-contact-grid">
                    <div>
                        <span>Email</span>
                        <strong><a href="mailto:hello@omnireferral.us">hello@omnireferral.us</a></strong>
                    </div>
                    <div>
                        <span>SMS Help</span>
                        <strong>+1 (786) 633-1912</strong>
                    </div>
                    <div>
                        <span>Contact page</span>
                        <strong><a href="{{ route('contact') }}">Open contact form</a></strong>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>
@endsection
