@extends('layouts.app')

@section('content')
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">About Us</span>
        <h1>Built by teams who understand the real estate handoff</h1>
        <p>OmniReferral is designed to make lead qualification, agent matching, and follow-up feel clear, professional, and approachable.</p>
    </div>
</section>

<section class="section">
    <div class="container two-column about-layout">
        <div>
            <h2>Mission</h2>
            <p>We help buyers, sellers, and agents move from first contact to better conversations through cleaner workflows, warmer referrals, and more dependable systems.</p>
            <h2>Why it works</h2>
            <p>ISA teams verify interest, sales executives shape the right package, marketing teams keep demand moving, and web teams maintain the platform that ties it all together.</p>
        </div>
        <div class="about-visual">
            <div class="about-visual__card">
                <img src="{{ asset('images/about/about-omnireferral.svg') }}" alt="OmniReferral teamwork and real estate workflow illustration" loading="lazy">
            </div>
            <div class="about-visual__note">
                <h3>One connected experience</h3>
                <p>From the first call to the final handoff, every team works from the same playbook so clients and agents get a smoother experience.</p>
            </div>
        </div>
    </div>
</section>

<section class="section section--gray">
    <div class="container">
        <div class="section-heading">
            <span class="eyebrow">Team</span>
            <h2>The people behind the platform</h2>
            <p>Operations, sales, marketing, and development teams working together to keep OmniReferral useful, credible, and human-centered.</p>
        </div>
        <div class="team-grid team-grid--refined">
            @foreach($team as $member)
                <article class="team-card team-card--refined">
                    <div class="team-card__badge" aria-hidden="true">{{ strtoupper(substr($member->name, 0, 1)) }}</div>
                    <h3>{{ $member->name }}</h3>
                    <p class="team-card__role">{{ $member->role }}</p>
                    <small>{{ $member->bio }}</small>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endsection
