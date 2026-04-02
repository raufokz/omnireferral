@extends('layouts.app')

@section('content')
<section class="page-hero pricing-page-hero pricing-page-hero--premium" style="background-image: linear-gradient(rgba(11, 54, 104, 0.85), rgba(11, 54, 104, 0.85)), url('{{ asset('images/auth/gateway-hero.png') }}'); background-size: cover; background-position: center;">
    <div class="container pricing-page-hero__content" data-animate="up">
        <span class="eyebrow" style="color: var(--color-gateway-accent);">Transparent Pricing</span>
        <h1 style="color: white; font-size: 3.5rem; line-height: 1.1; margin-bottom: 1.5rem;">Choose the lead engine that matches your growth stage</h1>
        <p style="color: rgba(255,255,255,0.8); max-width: 700px; margin: 0 auto 2.5rem;">From first-touch qualification to premium-intent routing, OmniReferral packages are built to help agents move faster with more confidence.</p>
        <div class="hero-chip-row hero-chip-row--pricing" style="justify-content: center;">
            <span style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2);">ISA-qualified leads</span>
            <span style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2);">Sales-backed packaging</span>
            <span style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2);">Optional VA support</span>
        </div>
    </div>
</section>

<section class="section pricing-page-section pricing-page-section--premium" x-data="{ plan: 'onetime', category: 'leads' }">
    <div class="container">
        <div class="pricing-header-toggle" role="tablist" style="margin-bottom: 4rem;">
            <button :class="category === 'leads' ? 'is-active' : ''" @click="category = 'leads'" type="button">Lead Packages</button>
            <button :class="category === 'va' ? 'is-active' : ''" @click="category = 'va'" type="button">Virtual Assistance</button>
        </div>

        <div x-show="category === 'leads'" data-animate="up">
            <div class="section-heading pricing-page-heading" style="text-align: center; margin-bottom: 4rem;">
                <span class="eyebrow">Real Estate Lead Packages</span>
                <h2 style="font-size: 2.5rem;">High-conversion packages for modern teams</h2>
                <div class="flex items-center justify-center gap-6 mt-8">
                    <span class="text-sm font-bold" :class="plan === 'onetime' ? 'text-blue-900' : 'text-gray-400'">One-Time Purchase</span>
                    <div @click="plan = plan === 'onetime' ? 'monthly' : 'onetime'" class="relative w-16 h-8 bg-gray-200 rounded-full cursor-pointer transition-colors" :class="plan === 'monthly' ? 'bg-orange-500' : ''">
                        <div class="absolute top-1 left-1 w-6 h-6 bg-white rounded-full transition-transform" :class="plan === 'monthly' ? 'translate-x-8' : ''"></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold" :class="plan === 'monthly' ? 'text-blue-900' : 'text-gray-400'">Monthly Plan</span>
                        <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded-full uppercase">Save 15%</span>
                    </div>
                </div>
            </div>

            <div class="pricing-grid pricing-grid--page">
                @foreach($leadPackages as $package)
                    <article class="pricing-card pricing-page-card {{ $package->is_featured ? 'pricing-card--featured' : '' }}" style="border-radius: 32px; padding: 3rem; background: white; border: 1px solid var(--color-border); box-shadow: 0 4px 24px rgba(0,0,0,0.04); transition: transform 0.3s, box-shadow 0.3s; position: relative; overflow: hidden;">
                        @if($package->is_featured)
                            <div class="absolute top-0 right-0 bg-orange-500 text-white text-[10px] font-bold uppercase tracking-widest py-2 px-6 rounded-bl-2xl">Recommended</div>
                        @endif
                        <div class="mb-8">
                            <span class="eyebrow" style="margin-bottom: 0.5rem;">{{ $package->is_featured ? 'Growth Tier' : 'Starting Tier' }}</span>
                            <h2 style="font-size: 2rem; margin-bottom: 1rem;">{{ $package->name }}</h2>
                            <p class="text-gray-500 text-sm leading-relaxed">{{ $package->description ?? 'ISA-qualified, sales-backed property leads.' }}</p>
                        </div>

                        <div class="mb-10">
                            <div x-show="plan === 'onetime'">
                                <strong style="font-size: 3rem; color: var(--color-gateway-brand-bg);">${{ number_format($package->one_time_price) }}</strong>
                                <span class="text-gray-400 text-sm">one-time</span>
                            </div>
                            <div x-show="plan === 'monthly'">
                                <strong style="font-size: 3rem; color: var(--color-gateway-accent);">${{ number_format($package->monthly_price) }}</strong>
                                <span class="text-gray-400 text-sm">/ month</span>
                            </div>
                        </div>

                        <ul class="mb-12 p-0" style="list-style: none;">
                            @foreach($package->features as $feature)
                                <li style="margin-bottom: 1rem; font-size: 0.9rem; color: var(--color-gray-700); display: flex; align-items: center; gap: 12px;">
                                    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <a href="{{ route('packages.checkout', $package) }}" class="button w-full block text-center" style="padding: 1.25rem; border-radius: 16px; font-weight: bold; background: {{ $package->is_featured ? 'var(--color-gateway-accent)' : 'var(--color-gateway-brand-bg)' }}; border: none; color: white;">Select Tier</a>
                    </article>
                @endforeach
            </div>
        </div>

        <div x-show="category === 'va'" style="display: none;" data-animate="up" :style="category === 'va' ? 'display: block;' : ''">
            <div class="section-heading pricing-page-heading" style="text-align: center; margin-bottom: 4rem;">
                <span class="eyebrow">Virtual Support</span>
                <h2 style="font-size: 2.5rem;">Delegate the follow-up, keep the commission</h2>
            </div>
            
            <div class="pricing-grid pricing-grid--page">
                @foreach($assistantPackages as $package)
                    <article class="pricing-card pricing-page-card" style="border-radius: 32px; padding: 3rem; background: white; border: 1px solid var(--color-border);">
                        <div class="mb-8">
                            <span class="eyebrow">Support Layer</span>
                            <h2 style="font-size: 2rem;">{{ $package->name }}</h2>
                        </div>
                        <div class="mb-10">
                            <strong style="font-size: 3rem; color: var(--color-gateway-brand-bg);">${{ number_format($package->monthly_price) }}</strong>
                            <span class="text-gray-400 text-sm">/ month</span>
                        </div>
                        <ul class="feature-check-list mb-12" style="list-style: none; padding: 0;">
                            @foreach($package->features as $feature)
                                <li style="margin-bottom: 1rem; font-size: 0.9rem; color: var(--color-gray-700); display: flex; items-center; gap: 12px;">
                                    <span style="color: #10b981; font-weight: bold;">&#10003;</span> {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('packages.checkout', $package) }}" class="button w-full block text-center button--ghost-blue" style="padding: 1.25rem; border-radius: 16px; font-weight: bold;">Select Plan</a>
                    </article>
                @endforeach
            </div>
        </div>

        <!-- Comparison Table -->
        <div class="mt-24 pt-24 border-t border-gray-100" data-animate="up">
            <div class="text-center mb-16">
                <span class="eyebrow">Breakdown</span>
                <h2 style="font-size: 2.5rem;">Engine Capability Comparison</h2>
            </div>
            
            <div class="cockpit-table-card overflow-hidden">
                <table class="cockpit-table">
                    <thead>
                        <tr>
                            <th class="p-6">Capability</th>
                            <th class="text-center">Quick</th>
                            <th class="text-center">Power</th>
                            <th class="text-center">Prime</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-50">
                            <td class="p-6 font-bold text-gray-900">ISA Pre-Qualification</td>
                            <td class="text-center text-green-500 font-bold">&#10003;</td>
                            <td class="text-center text-green-500 font-bold">&#10003;</td>
                            <td class="text-center text-green-500 font-bold">&#10003;</td>
                        </tr>
                        <tr class="border-b border-gray-50">
                            <td class="p-6 font-bold text-gray-900">Lead Volume / Mo</td>
                            <td class="text-center text-gray-500">5-10</td>
                            <td class="text-center text-gray-500">15-25</td>
                            <td class="text-center text-gray-900 font-bold">30+</td>
                        </tr>
                        <tr class="border-b border-gray-50">
                            <td class="p-6 font-bold text-gray-900">Priority Routing</td>
                            <td class="text-center text-gray-300">&mdash;</td>
                            <td class="text-center text-green-500 font-bold">&#10003;</td>
                            <td class="text-center text-green-500 font-bold">&#10003;</td>
                        </tr>
                        <tr>
                            <td class="p-6 font-bold text-gray-900">Dedicated Success Exec</td>
                            <td class="text-center text-gray-300">&mdash;</td>
                            <td class="text-center text-gray-300">&mdash;</td>
                            <td class="text-center text-green-500 font-bold">&#10003;</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Features Grid -->
        <div class="grid grid-cols-3 gap-12 mt-24 pt-24 border-t border-gray-100">
            <article>
                <div class="mb-4">
                    <img src="{{ asset('images/illustrations/verification.png') }}" alt="Verified" style="width: 48px;">
                </div>
                <h4 class="font-bold text-lg mb-2">ISA Verification</h4>
                <p class="text-gray-500 text-sm leading-relaxed">No generic lists. Every lead is human-verified for budget, intent, and timeline before delivery.</p>
            </article>
            <article>
                <div class="mb-4">
                    <img src="{{ asset('images/illustrations/matching.png') }}" alt="Matching" style="width: 48px;">
                </div>
                <h4 class="font-bold text-lg mb-2">Strategic Matching</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Leads are packaged to match market demands and agent expertise specifically, increasing ROI.</p>
            </article>
            <article>
                <div class="mb-4">
                    <img src="{{ asset('images/illustrations/conversion.png') }}" alt="Conversion" style="width: 48px;">
                </div>
                <h4 class="font-bold text-lg mb-2">Conversion Focus</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Designed for agents who want a dashboard to manage their revenue pipeline, not just a spreadsheet.</p>
            </article>
        </div>
    </div>
</section>

<div class="modal-overlay" id="packageModal" hidden aria-hidden="true">
    <div class="modal-card package-modal-card" role="dialog" aria-modal="true" aria-labelledby="packageModalTitle">
        <button class="modal-close" type="button" id="packageModalClose" aria-label="Close package form">&times;</button>
        <div class="package-modal-card__intro">
            <span class="eyebrow">Package Form</span>
            <h2 id="packageModalTitle">Complete your package selection</h2>
            <p id="packageModalDescription">Finish the secure form to confirm your package and continue to onboarding.</p>
        </div>
        <div class="embed-card">
            <iframe id="packageModalFrame" src="about:blank" title="Package form" loading="lazy"></iframe>
        </div>
        <p class="package-modal-card__status" id="packageModalStatus" aria-live="polite">Complete the package form and payment to unlock onboarding.</p>
        <div class="package-modal-card__actions">
            <a id="packageModalStripeCheckout" href="{{ route('packages.checkout', $leadPackages->first()) }}" class="button button--ghost-blue">Review Payment Options</a>
            <a id="packageModalOnboarding" href="{{ $onboardingUrl }}?role=agent" class="button button--orange" hidden aria-hidden="true" aria-disabled="true" tabindex="-1">Complete Onboarding</a>
            <button class="button button--ghost-blue" type="button" id="packageModalCancel">Close</button>
        </div>
    </div>
</div>
<script src="https://link.msgsndr.com/js/form_embed.js"></script>
@endsection

