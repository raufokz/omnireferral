@php
    /** @var array $pricingPlan */
    /** @var string $planSlug */
    $iframeSrc = $iframeSrc ?? '';
    $iframeId = $iframeId ?? ('gohighlevel-' . ($planSlug ?? 'plan'));
    $iframeTitle = $iframeTitle ?? 'survey';

    // Fallback: if a specific plan didn't pass an iframe URL, keep layout from breaking.
    $hasIframe = !empty($iframeSrc);
@endphp

<div class="ghc-layout">
    <div class="ghc-left">
        <div class="panel panel--premium ghc-left__panel">
            <div class="ghc-left__header">
                <div class="ghc-badges">
                    @if(!empty($pricingPlan['badge']))
                        <span class="pdh-pill">{{ $pricingPlan['badge'] }}</span>
                    @endif
                    @if(!empty($pricingPlan['subtitle_badges']) && is_array($pricingPlan['subtitle_badges']))
                        @foreach($pricingPlan['subtitle_badges'] as $b)
                            <span class="pdh-pill">{{ $b }}</span>
                        @endforeach
                    @endif
                </div>

                <h1 class="ghc-left__title">{{ $pricingPlan['name'] ?? 'Plan' }}</h1>
                <p class="ghc-left__subtitle">{{ $pricingPlan['summary'] ?? 'Premium lead generation package with GoHighLevel follow-up.' }}</p>

                <div class="ghc-left__meta">
                    @if(isset($pricingPlan['price']))
                        <div class="ghc-price">
                            <strong>${{ number_format((int)($pricingPlan['price'] ?? 0)) }}</strong>
                            <span>{{ $pricingPlan['price_note'] ?? '/ month' }}</span>
                        </div>
                    @endif

                    <div class="ghc-meta-row">
                        @if(!empty($pricingPlan['billing_type']))
                            <div class="ghc-meta-item">
                                <span class="pdx-label">Billing type</span>
                                <p class="pdx-copy">{{ $pricingPlan['billing_type'] }}</p>
                            </div>
                        @endif

                        @if(!empty($pricingPlan['best_for']))
                            <div class="ghc-meta-item">
                                <span class="pdx-label">Best for</span>
                                <p class="pdx-copy">{{ $pricingPlan['best_for'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="ghc-divider"></div>

            <div class="ghc-section">
                <h2 class="panel__title">Package benefits</h2>
                @if(!empty($pricingPlan['features']) && is_array($pricingPlan['features']))
                    <ul class="feature-check-list pdx-list">
                        @foreach($pricingPlan['features'] as $f)
                            <li>{{ $f }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="panel__copy">
                        {{ $pricingPlan['what_you_get'] ?? 'A structured lead generation lane with verified routing into your workflow.' }}
                    </p>
                @endif
            </div>

            <div class="ghc-section">
                <h2 class="panel__title">What happens next</h2>
                <ol class="process-steps">
                    <li><strong>Submit</strong> the secure form</li>
                    <li><strong>Onboarding</strong> confirms markets and workflow</li>
                    <li><strong>Launch</strong> your GoHighLevel-ready pipeline</li>
                    <li><strong>Receive</strong> leads and support</li>
                    <li><strong>Track</strong> performance</li>
                </ol>
            </div>

            <div class="ghc-section ghc-section--two">
                <div>
                    <h2 class="panel__title">Support included</h2>
                    <p class="panel__copy">
                        {{ $pricingPlan['support_level'] ?? 'Dedicated support lane with performance updates so you always know what’s happening in your pipeline.' }}
                    </p>
                    <div class="ghc-trust-row">
                        <h3 class="panel__subtitle">Trust badges</h3>
                        <ul class="trust-list">
                            <li>Verified lead workflow</li>
                            <li>Real estate focused operations team</li>
                            <li>Performance tracking &amp; reporting</li>
                            <li>Fast response time</li>
                        </ul>
                    </div>
                </div>

                <div>
                    <h2 class="panel__title">Trust indicators</h2>
                    <ul class="trust-list">
                        <li>Transparent onboarding process</li>
                        <li>GoHighLevel workflow handoff</li>
                        <li>Clear expectations &amp; timelines</li>
                        <li>Consistent support coverage</li>
                    </ul>
                </div>
            </div>

            <div class="ghc-bottom-cta">
                <div class="ghc-bottom-cta__inner">
                    <a href="#ghc-form" class="button button--orange ghc-primary-cta">GET STARTED</a>
                    <a href="{{ route('contact') }}" class="button button--ghost-light">TALK TO SALES</a>
                </div>
            </div>
        </div>
    </div>

    <div class="ghc-right" id="ghc-form">
        <div class="ghc-frame panel panel--premium">
            <div class="ghc-loading" id="ghc-loading">
                <div class="ghc-spinner" aria-hidden="true"></div>
                <div class="ghc-loading__text">Loading secure form</div>
            </div>

            @if($hasIframe)
                <iframe
                    class="ghc-iframe"
                    src="{{ $iframeSrc }}"
                    id="{{ $iframeId }}"
                    title="{{ $iframeTitle }}"
                    style="border:none;width:100%;min-height:800px;background:transparent;"
                    scrolling="no"
                    loading="eager"
                    fetchpriority="high"
                    referrerpolicy="no-referrer"
                    onload="(function(){var el=document.getElementById('ghc-loading');if(el){el.style.display='none';}})();"
                ></iframe>
            @else
                <div class="ghc-missing-iframe">
                    <p class="panel__copy">Secure form is unavailable right now. Please contact sales.</p>
                    <a class="button button--orange" href="{{ route('contact') }}">CONTACT SALES</a>
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://link.msgsndr.com/js/form_embed.js" defer></script>

@push('styles')
<style>
    .ghc-layout{
        display:grid;
        grid-template-columns: 1.05fr 0.95fr;
        gap:24px;
        align-items:start;
        margin-top: 18px;
    }
    .ghc-left__panel{ position:relative; }
    .ghc-right{ position:sticky; top: 84px; }
    .ghc-frame{ padding:18px; overflow:hidden; }

    .ghc-left__header{ margin-bottom: 10px; }
    .ghc-badges{ display:flex; flex-wrap:wrap; gap:10px; margin-bottom:12px; }
    .ghc-left__title{ font-size: 2.1rem; margin:0 0 6px; line-height:1.15; color: var(--navy, #0b1b3a); }
    .ghc-left__subtitle{ margin:0; color: rgba(11,27,58,.78); font-size:1.02rem; }

    .ghc-divider{ height:1px; background: rgba(15, 40, 90, .12); margin: 18px 0; }

    .ghc-price{ display:flex; align-items:baseline; gap:10px; }
    .ghc-meta-row{ display:grid; grid-template-columns:1fr; gap:14px; margin-top: 14px; }

    .ghc-section{ margin-bottom: 22px; }
    .ghc-section--two{ display:grid; grid-template-columns: 1fr 1fr; gap:18px; }
    .ghc-bottom-cta{ margin-top: 16px; }
    .ghc-bottom-cta__inner{ display:flex; gap:12px; flex-wrap:wrap; }

    .ghc-loading{
        position:absolute;
        inset: 18px 18px auto;
        display:flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        min-height: 44px;
        background: linear-gradient(180deg, rgba(255,255,255,.94), rgba(255,255,255,.78));
        backdrop-filter: blur(4px);
        z-index:5;
        border-radius: 12px;
        pointer-events: none;
    }
    .ghc-iframe{ display:block; border-radius: 12px; }
    .ghc-spinner{
        width: 18px; height:18px;
        border: 2px solid rgba(11,27,58,.15);
        border-top-color: var(--orange, #ff7a18);
        border-radius:50%;
        animation: ghc-spin .9s linear infinite;
    }
    @keyframes ghc-spin { to { transform: rotate(360deg); } }
    .ghc-loading__text{
        font-weight: 700;
        color: rgba(11,27,58,.85);
        font-size: .82rem;
        text-align:center;
    }

    .ghc-missing-iframe{ padding:18px; border-radius: 12px; border:1px solid rgba(11,27,58,.12); }

    @media (max-width: 980px){
        .ghc-layout{ grid-template-columns: 1fr; }
        .ghc-right{ position:relative; top:auto; }
        .ghc-section--two{ grid-template-columns: 1fr; }
        .ghc-loading{ inset: 14px 14px auto; }
    }
</style>
@endpush
