@extends('layouts.app')

@push('styles')
    @vite('resources/css/modules/service-seo.css')
@endpush

@section('head')
    @if($page->canonical_url)
        <link rel="canonical" href="{{ $page->canonical_url }}">
    @else
        <link rel="canonical" href="{{ route('service-seo-pages.show', $page->slug) }}">
    @endif
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
    @endphp
    @if(! empty($faqSchema))
        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqSchema,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
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
                <div class="service-seo__hero-keyword">
                    <span>Main Keyword</span>
                    <strong>{{ $page->primary_keyword ?: $page->title }}</strong>
                </div>
                <h1>{{ $page->hero_title ?: $page->title }}</h1>
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

            <aside class="service-seo__summary" aria-label="Pay at closing lead model summary">
                <span>Performance Model</span>
                <h2>No upfront lead cost. Pay only after a closed transaction.</h2>
                <div class="service-seo__summary-flow">
                    <div>
                        <strong>01</strong>
                        <p>Lead captured and screened by the OmniReferral team.</p>
                    </div>
                    <div>
                        <strong>02</strong>
                        <p>Matched by ZIP code, market fit, and agent capacity.</p>
                    </div>
                    <div>
                        <strong>03</strong>
                        <p>Referral fee is due only when the deal closes.</p>
                    </div>
                </div>
            </aside>
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
                            <span class="service-seo__section-label">Guide</span>
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
                                @if(! empty($section['heading']))
                                    <h2>{{ $section['heading'] }}</h2>
                                @endif
                                @if(! empty($section['image']))
                                    <div class="service-seo__info-media">
                                        <img src="{{ asset($section['image']) }}" alt="{{ $section['heading'] ?? 'Feature image' }}" loading="lazy">
                                    </div>
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
                            <p>Clear answers for agents comparing pay-at-closing referrals against pay-per-lead models.</p>
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
                    <h2>Built for agents who want qualified demand without prepaid lead spend.</h2>
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
            </aside>
        </div>
    </section>

    @if($page->cta_label && $page->cta_url)
        <section class="service-seo__final">
            <div class="container">
                <div class="service-seo__final-panel">
                    <div>
                        <span class="service-seo__kicker">Ready to Get Started?</span>
                        <h2>Build your pipeline with verified real estate leads you pay for only when you close.</h2>
                        <p>No setup fee, no monthly lead bill, and no payment for opportunities that never turn into a transaction.</p>
                    </div>
                    <a class="service-seo__btn service-seo__btn--primary" href="{{ url($page->cta_url) }}">{{ $page->cta_label }}</a>
                </div>
            </div>
        </section>
    @endif
</article>
@endsection
