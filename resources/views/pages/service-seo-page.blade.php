@extends('layouts.app')

@push('styles')
    @vite('resources/css/modules/service-seo.css')
@endpush

@section('head')
    <meta name="robots" content="index, follow, max-image-preview:large">
    @if($page->canonical_url)
        <link rel="canonical" href="{{ $page->canonical_url }}">
    @else
        <link rel="canonical" href="{{ route('service-seo-pages.show', $page->slug) }}">
    @endif

    @php
        $ogUrl = $page->canonical_url ?: route('service-seo-pages.show', $page->slug);
        $ogImage = asset($page->content['hero_image'] ?? 'images/home/hero_backdrop_v2.png');
        $ogTitle = $page->seo_title ?: $page->title . ' | OmniReferral';
        $ogDescription = $page->meta_description ?: '';
    @endphp
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:url" content="{{ $ogUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:site_name" content="OmniReferral">
    <meta property="og:locale" content="en_US">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
@endsection

@section('schema')
    @php
        $faqSchema = collect($page->getFaqs())->map(fn ($faq) => [
            '@type' => 'Question',
            'name' => $faq['question'] ?? '',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $faq['answer'] ?? '',
            ],
        ])->filter(fn ($faq) => $faq['name'] !== '')->values()->all();

        $pageUrl = $page->canonical_url ?: route('service-seo-pages.show', $page->slug);
        $schemas = [];

        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            '@id' => $pageUrl . '#webpage',
            'url' => $pageUrl,
            'name' => $page->title,
            'description' => $page->meta_description ?: '',
            'inLanguage' => 'en-US',
            'dateModified' => $page->updated_at->toAtomString(),
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => url('/') . '#website',
            ],
        ];

        if (! empty($faqSchema)) {
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqSchema,
            ];
        }
    @endphp
    @foreach($schemas as $schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endforeach
@endsection

@section('content')
@php
    $sections = $page->getSections();
    $faqs = $page->getFaqs();
    $heroImage = $page->content['hero_image'] ?? 'images/home/hero_backdrop_v2.png';
    $primarySections = collect($sections)->take(2);
    $remainingSections = collect($sections)->slice(2)->values();
    $renderRichText = function (?string $content): string {
        $content = trim((string) $content);

        if ($content === '') {
            return '';
        }

        if ($content !== strip_tags($content)) {
            return $content;
        }

        return nl2br(e($content));
    };
@endphp

<article class="service-seo">
    <section class="service-seo__hero">
        <div class="service-seo__hero-media" aria-hidden="true">
            <img src="{{ asset($heroImage) }}" alt="">
        </div>
        <div class="container service-seo__hero-inner">
            <div class="service-seo__hero-copy">
                <h1>{{ $page->hero_title ?: $page->title }}</h1>
                @if($page->meta_description)
                    <p class="service-seo__hero-description">{{ $page->meta_description }}</p>
                @endif
                <div class="service-seo__rich service-seo__hero-rich">
                    {!! $renderRichText($page->hero_body) !!}
                </div>
                <div class="service-seo__hero-actions">
                    @if($page->cta_label && $page->cta_url)
                        <a class="service-seo__btn service-seo__btn--primary" href="{{ url($page->cta_url) }}">{{ $page->cta_label }}</a>
                    @endif
                    <a class="service-seo__btn service-seo__btn--secondary" href="{{ route('pricing') }}">View Packages</a>
                </div>
            </div>
        </div>
    </section>

    <section class="service-seo__summary" aria-label="How it works summary">
        <div class="container">
            <span>How It Works</span>
            <h2>Qualified referrals delivered to your pipeline — with zero upfront cost.</h2>
            <div class="service-seo__summary-flow">
                <div>
                    <strong>01</strong>
                    <p>Buyer or seller opportunity is captured and verified by our team.</p>
                </div>
                <div>
                    <strong>02</strong>
                    <p>Matched to your market, ZIP code, and agent capacity preferences.</p>
                </div>
                <div>
                    <strong>03</strong>
                    <p>You pay a referral fee only after a successful closing.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="service-seo__metrics" aria-label="OmniReferral lead platform metrics">
        <div class="container service-seo__metric-grid">
            <div>
                <strong>181.7M+</strong>
                <span>Lead pool contacts</span>
            </div>
            <div>
                <strong>4,750+</strong>
                <span>New leads added daily</span>
            </div>
            <div>
                <strong>~7 min</strong>
                <span>Average routing time</span>
            </div>
            <div>
                <strong>$0</strong>
                <span>Upfront lead cost</span>
            </div>
        </div>
    </section>

    <section class="service-seo__content">
        <div class="container service-seo__layout">
            <main class="service-seo__main">
                @foreach($primarySections as $section)
                    <section class="service-seo__feature-section">
                        @if(! empty($section['heading']))
                            <span class="service-seo__section-label">Overview</span>
                            <h2>{{ $section['heading'] }}</h2>
                        @endif
                        @if(! empty($section['image']))
                            <div class="service-seo__feature-media">
                                <img src="{{ asset($section['image']) }}" alt="{{ $section['heading'] ?? 'Feature image' }}" loading="lazy">
                            </div>
                        @endif
                        <div class="service-seo__rich">
                            {!! $renderRichText($section['body'] ?? '') !!}
                        </div>
                    </section>
                @endforeach

                @if($remainingSections->isNotEmpty())
                    <div class="service-seo__section-grid">
                        @foreach($remainingSections as $section)
                            <section class="service-seo__info-card">
                                @if(! empty($section['image']))
                                    <div class="service-seo__info-media">
                                        <img src="{{ asset($section['image']) }}" alt="{{ $section['heading'] ?? 'Feature image' }}" loading="lazy">
                                    </div>
                                @endif
                                @if(! empty($section['heading']))
                                    <h2>{{ $section['heading'] }}</h2>
                                @endif
                                <div class="service-seo__rich">
                                    {!! $renderRichText($section['body'] ?? '') !!}
                                </div>
                            </section>
                        @endforeach
                    </div>
                @endif

                @if($faqs)
                    <section class="service-seo__faqs">
                        <div class="service-seo__section-head">
                            <span class="service-seo__kicker">Questions</span>
                            <h2>Frequently Asked Questions</h2>
                            <p>Get clear answers about how our referral model works.</p>
                        </div>
                        <div class="service-seo__faq-list">
                            @foreach($faqs as $faq)
                                <details>
                                    <summary>
                                        <span>{{ $faq['question'] }}</span>
                                        <i aria-hidden="true">+</i>
                                    </summary>
                                    <div class="service-seo__rich">
                                        {!! $renderRichText($faq['answer'] ?? '') !!}
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    </section>
                @endif
            </main>

            <aside class="service-seo__side">
                <div class="service-seo__side-card">
                    <span class="service-seo__kicker">Agent Pipeline</span>
                    <h2>Qualified referrals delivered without prepaid lead spend.</h2>
                    <ul>
                        <li>ISA-qualified buyer and seller intent</li>
                        <li>ZIP-based routing by active market</li>
                        <li>Dashboard delivery with lead context</li>
                        <li>Referral fee only after closing</li>
                    </ul>
                    @if($page->cta_label && $page->cta_url)
                        <a class="service-seo__btn service-seo__btn--primary" href="{{ url($page->cta_url) }}">{{ $page->cta_label }}</a>
                    @endif
                </div>
                <div class="service-seo__side-card service-seo__side-card--alt">
                    <span class="service-seo__kicker">Get Started</span>
                    <h2>Ready to grow your pipeline?</h2>
                    <p>Talk to our team about your market and get matched with qualified opportunities.</p>
                    <a class="service-seo__btn service-seo__btn--primary" href="{{ route('contact') }}">Contact Us</a>
                </div>
            </aside>
        </div>
    </section>

    @if($page->cta_label && $page->cta_url)
        <section class="service-seo__final">
            <div class="container">
                <div class="service-seo__final-panel">
                    <div>
                        <span class="service-seo__kicker">Ready to Get Started?</span>
                        <h2>Start receiving qualified real estate referrals — with no upfront cost.</h2>
                        <p>No setup fee, no monthly lead bill, and no payment for opportunities that never turn into a transaction.</p>
                    </div>
                    <div class="service-seo__final-actions">
                        <a class="service-seo__btn service-seo__btn--primary" href="{{ url($page->cta_url) }}">{{ $page->cta_label }}</a>
                        <a class="service-seo__btn service-seo__btn--outline" href="{{ route('pricing') }}">Compare Packages</a>
                    </div>
                </div>
            </div>
        </section>
    @endif
</article>
@endsection
