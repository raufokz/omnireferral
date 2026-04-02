@extends('layouts.app')
@section('content')
<section class="page-hero faq-hero" data-reveal>
    <div class="container page-hero__content">
        <span class="eyebrow">FAQ</span>
        <h1>Questions we hear most often</h1>
        <p>Clear, concise answers for buyers, sellers, agents, and partner teams.</p>
    </div>
</section>

<section class="section faq-section" data-stagger>
    <div class="container faq-grid">
        <div class="faq-intro" data-reveal>
            <span class="eyebrow">Support</span>
            <h2>Need quick clarity?</h2>
            <p>Here are the essentials about packages, qualification, onboarding, and support. Still stuck? Our team replies within 24 hours.</p>
            <div class="faq-cta">
                <a class="button button--orange" href="{{ route('contact') }}">Talk to our team</a>
                <a class="button button--ghost-blue" href="{{ route('pricing') }}">View packages</a>
            </div>
        </div>

        <div class="faq-accordion" data-reveal>
            @foreach([
                ['What is the difference between Quick, Power, and Prime leads?','Quick is lighter-touch verified leads; Power adds richer qualification and urgency; Prime is highest-intent with priority routing and support.'],
                ['How does OmniReferral verify prospects?','Our ISA team confirms intent, location, budget, and timeline before sales assigns the package tier and routes to eligible agents.'],
                ['Can agents add virtual assistant support?','Yes. VA plans can be layered onto any lead package to handle CRM cleanup, nurture, and coordination.'],
                ['Do you support campaigns and surveys?','Yes. Campaigns, surveys, and feedback capture run through GoHighLevel-ready workflows with our forms or your embeds.'],
                ['How fast are leads routed?','Most leads route within 24–48 hours after qualification; Prime routes are prioritized.'],
                ['What happens after payment?','Stripe checkout confirms the package, then you complete the GoHighLevel onboarding form. We provision your account, set roles, and email login details.'],
            ] as [$q, $a])
                <article class="faq-item" data-accordion>
                    <button class="faq-question" type="button" aria-expanded="false">{{ $q }}<span class="faq-toggle">+</span></button>
                    <div class="faq-answer" hidden>
                        <p>{{ $a }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endsection
