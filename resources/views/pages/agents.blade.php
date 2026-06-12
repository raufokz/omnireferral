@extends('layouts.app')

@push('styles')
    @vite('resources/css/modules/agent-directory.css')
@endpush

@section('content')
@php
    $totalCount = method_exists($profiles, 'total') ? $profiles->total() : $profiles->count();
    $locationLabel = $location['label'] ?? null;
    $filters = $activeFilters ?? [];
    $stats = array_merge([
        'total_agents' => $totalCount,
        'cities_covered' => count($filterCities ?? []),
        'referral_matches' => $totalCount,
        'featured_agents' => 0,
    ], $directoryStats ?? []);
    $referralMatches = max((int) ($stats['referral_matches'] ?? 0), (int) $totalCount);
@endphp

<div class="omni-agent-directory-page"
    x-data="agentDirectoryModal()"
    x-on:keydown.escape.window="closeModal()">
    <section class="omni-agent-hero">
        <div class="omni-agent-hero__bg" aria-hidden="true">
            <img src="{{ asset('images/home/hero_backdrop_v2.png') }}" alt="">
        </div>
        <div class="container omni-agent-hero__inner">
            <div class="omni-agent-hero__copy" data-animate="left">
                <span class="agent-kicker">Agent Directory</span>
                <h1>{{ $locationLabel ? 'Find top real estate agents in '.$locationLabel : 'Find the right real estate agent' }}</h1>
                <p>Browse verified real estate professionals by market, specialty, rating, and brokerage, then open a premium agent profile before you request a referral.</p>
                <div class="omni-agent-hero__actions">
                    <a href="#agent-directory-results" class="agent-btn agent-btn--orange">Browse Agents</a>
                    <a href="{{ route('register') }}" class="agent-btn agent-btn--light">Join Network</a>
                </div>
            </div>

            <div class="omni-agent-stats" data-animate="right" aria-label="Directory statistics">
                <div>
                    <span aria-hidden="true">AG</span>
                    <strong>{{ number_format((int) $stats['total_agents']) }}</strong>
                    <small>Total Agents</small>
                </div>
                <div>
                    <span aria-hidden="true">CT</span>
                    <strong>{{ number_format((int) $stats['cities_covered']) }}+</strong>
                    <small>Cities Covered</small>
                </div>
                <div>
                    <span aria-hidden="true">RM</span>
                    <strong>{{ number_format($referralMatches) }}+</strong>
                    <small>Referral Matches</small>
                </div>
                <div>
                    <span aria-hidden="true">FA</span>
                    <strong>{{ number_format((int) $stats['featured_agents']) }}</strong>
                    <small>Featured Agents</small>
                </div>
            </div>

            <form method="get" action="{{ url()->current() }}" class="omni-agent-search" data-animate="up" aria-label="Search agents">
                <label class="omni-agent-search__wide">
                    <span>Search</span>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name, brokerage, or specialty">
                </label>
                <label>
                    <span>Name</span>
                    <input type="search" name="name" value="{{ $filters['name'] ?? '' }}" placeholder="Agent name">
                </label>
                <label>
                    <span>Brokerage</span>
                    <input type="search" name="brokerage" value="{{ $filters['brokerage'] ?? '' }}" placeholder="Brokerage">
                </label>
                <label>
                    <span>City</span>
                    <select name="city">
                        <option value="">All Cities</option>
                        @foreach($filterCities ?? [] as $cityOption)
                            <option value="{{ $cityOption }}" @selected(mb_strtolower((string) ($filters['city'] ?? '')) === mb_strtolower((string) $cityOption))>{{ $cityOption }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>State</span>
                    <select name="state">
                        <option value="">All States</option>
                        @foreach($filterStates ?? [] as $stateOption)
                            <option value="{{ $stateOption }}" @selected(strtoupper((string) ($filters['state'] ?? '')) === strtoupper((string) $stateOption))>{{ $stateOption }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>ZIP</span>
                    <input type="search" name="zip" value="{{ $filters['zip'] ?? '' }}" placeholder="33101">
                </label>
                <label>
                    <span>Specialty</span>
                    <input type="search" name="specialty" value="{{ $filters['specialty'] ?? '' }}" placeholder="Luxury, relocation">
                </label>
                <label>
                    <span>Rating</span>
                    <select name="rating">
                        <option value="">Any Rating</option>
                        @foreach(['4.8' => '4.8+', '4.5' => '4.5+', '4.0' => '4.0+'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['rating'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="omni-agent-search__actions">
                    <button type="submit" class="agent-btn agent-btn--orange">Search Agents</button>
                    <a href="{{ route('agents.index') }}" class="agent-btn agent-btn--ghost">Reset</a>
                </div>
            </form>
        </div>
    </section>

    <section class="omni-agent-results" id="agent-directory-results">
        <div class="container">
            <div class="omni-agent-results__bar" data-animate="up">
                <p>Showing {{ number_format($profiles->firstItem() ?? 0) }} to {{ number_format($profiles->lastItem() ?? 0) }} of {{ number_format($totalCount) }} agents</p>
                <label>
                    <span>Sort by</span>
                    <select aria-label="Sort agents">
                        <option>Most Recommended</option>
                        <option>Highest Rated</option>
                        <option>Most Reviews</option>
                    </select>
                </label>
            </div>

            <div class="omni-agent-card-grid" data-stagger>
                @forelse($profiles as $profile)
                    @php
                        $card = \App\Support\AgentDirectory::publicCardPayload($profile);
                        $location = trim(($card['city'] ?: '').', '.($card['state'] ?: ''), ', ');
                        $specialties = array_slice($card['specialties'] ?: ['Residential', 'Referrals'], 0, 3);
                    @endphp
                    <article class="omni-agent-card" data-agent-card>
                        <button type="button" class="omni-agent-card__save" aria-label="Save {{ $card['name'] }}" x-on:click="quickSave('{{ $profile->slug }}')">&#9825;</button>
                        <div class="omni-agent-card__media">
                            <img src="{{ $card['headshot_url'] }}" alt="{{ $card['name'] }}" loading="lazy" width="180" height="180">
                        </div>
                        <div class="omni-agent-card__body">
                            <div class="omni-agent-card__top">
                                <div>
                                    <h2>{{ $card['name'] }}</h2>
                                    <p>{{ $card['brokerage'] }}</p>
                                    @if($location !== '')
                                        <span>{{ $location }}</span>
                                    @endif
                                </div>
                                <span class="omni-agent-card__verified">{{ $card['is_featured'] ? 'Featured Agent' : 'Verified' }}</span>
                            </div>

                            <div class="omni-agent-rating">
                                <strong>{{ $card['rating'] }}</strong>
                                <span aria-label="{{ $card['rating'] }} out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                                <small>({{ number_format($card['review_count']) }} reviews)</small>
                            </div>

                            <div class="omni-agent-card__chips">
                                @foreach($specialties as $specialty)
                                    <span>{{ $specialty }}</span>
                                @endforeach
                            </div>

                            <div class="omni-agent-card__actions">
                                <button type="button" class="agent-btn agent-btn--orange" x-on:click="openModal('{{ $profile->slug }}')">View Full Profile</button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="omni-agent-empty-state">
                        <h2>No agents found</h2>
                        <p>Try another city, ZIP code, specialty, or rating threshold.</p>
                        <a href="{{ route('agents.index') }}" class="agent-btn agent-btn--orange">View all agents</a>
                    </div>
                @endforelse
            </div>

            @if($profiles->hasPages())
                @php
                    $currentPage = $profiles->currentPage();
                    $lastPage = $profiles->lastPage();
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($lastPage, $currentPage + 2);
                @endphp
                <nav class="omni-agent-pagination" aria-label="Agent directory pagination">
                    <a href="{{ $profiles->previousPageUrl() ?: '#' }}" class="{{ $profiles->onFirstPage() ? 'is-disabled' : '' }}">Previous</a>
                    @if($startPage > 1)
                        <a href="{{ $profiles->url(1) }}">1</a>
                        @if($startPage > 2)
                            <span>...</span>
                        @endif
                    @endif
                    @for($page = $startPage; $page <= $endPage; $page++)
                        <a href="{{ $profiles->url($page) }}" class="{{ $page === $currentPage ? 'is-active' : '' }}" @if($page === $currentPage) aria-current="page" @endif>{{ $page }}</a>
                    @endfor
                    @if($endPage < $lastPage)
                        @if($endPage < $lastPage - 1)
                            <span>...</span>
                        @endif
                        <a href="{{ $profiles->url($lastPage) }}">{{ number_format($lastPage) }}</a>
                    @endif
                    <a href="{{ $profiles->nextPageUrl() ?: '#' }}" class="{{ $profiles->hasMorePages() ? '' : 'is-disabled' }}">Next</a>
                </nav>
            @endif
        </div>
    </section>

    <template x-if="open">
        <div class="omni-agent-modal" x-show="open" x-transition x-cloak>
            <div class="omni-agent-modal__backdrop" x-on:click="closeModal()"></div>
            <div class="omni-agent-modal__panel" x-on:click.stop role="dialog" aria-modal="true" :aria-label="profile?.name || 'Agent profile'">
                <button type="button" class="omni-agent-modal__close" x-on:click="closeModal()" aria-label="Close">&times;</button>

                <template x-if="loading">
                    <div class="omni-agent-modal__loading">Loading profile...</div>
                </template>

                <template x-if="profile && !loading">
                    <div class="omni-agent-modal__content">
                        <aside class="omni-agent-modal__side">
                            <img :src="profile.headshot_url" :alt="profile.name" loading="lazy">
                            <div class="omni-agent-contact-card">
                                <div><span>Phone</span><strong x-text="profile.phone_label"></strong></div>
                                <div><span>Email</span><strong x-text="profile.email_label"></strong></div>
                                <div><span>Website</span><strong x-text="profile.website_label"></strong></div>
                                <div><span>License</span><strong x-text="profile.license_label"></strong></div>
                            </div>
                            <div class="omni-agent-socials">
                                <span>Connect With Agent</span>
                                <template x-if="profile.social_links && Object.keys(profile.social_links).length">
                                    <div>
                                        <template x-for="[name, url] in Object.entries(profile.social_links)" :key="name">
                                            <a :href="url" target="_blank" rel="noopener" x-text="name.slice(0, 1).toUpperCase()"></a>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!profile.social_links || !Object.keys(profile.social_links).length">
                                    <div>
                                        <span aria-hidden="true">f</span>
                                        <span aria-hidden="true">ig</span>
                                        <span aria-hidden="true">in</span>
                                    </div>
                                </template>
                            </div>
                        </aside>

                        <main class="omni-agent-modal__main">
                            <div class="omni-agent-modal__head">
                                <div>
                                    <div class="omni-agent-modal__title-row">
                                        <h2 x-text="profile.name"></h2>
                                        <span x-text="profile.is_featured ? 'Featured Agent' : ('Verified' + ' Agent')"></span>
                                    </div>
                                    <p x-text="profile.brokerage"></p>
                                    <small x-text="profile.service_area"></small>
                                    <div class="omni-agent-rating omni-agent-rating--modal">
                                        <strong x-text="profile.rating"></strong>
                                        <span>&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                                        <small><span x-text="profile.review_count"></span> reviews</small>
                                    </div>
                                </div>
                            </div>

                            <div class="omni-agent-modal__actions">
                                <button type="button" class="agent-btn agent-btn--orange" x-on:click="startInquiry('contact')">Contact Agent</button>
                                <button type="button" class="agent-btn agent-btn--blue" x-on:click="startInquiry('referral')">Request Referral</button>
                                <button type="button" class="agent-btn agent-btn--ghost" x-on:click="saveAgent()">Save Agent</button>
                            </div>
                            <p class="omni-agent-modal__notice" x-show="successMessage" x-text="successMessage"></p>

                            <div class="omni-agent-modal__metrics">
                                <div><strong x-text="profile.years_of_experience ? profile.years_of_experience + '+' : '8+'"></strong><span>Years Experience</span></div>
                                <div><strong x-text="profile.leads_closed + '+'"></strong><span>Deals Closed</span></div>
                                <div><strong x-text="profile.satisfaction_rate"></strong><span>Client Satisfaction</span></div>
                                <div><strong x-text="profile.rank_label"></strong><span>Agent Nationwide</span></div>
                            </div>

                            <section class="omni-agent-modal__section">
                                <h3>About <span x-text="profile.name?.split(' ')[0] || 'Agent'"></span></h3>
                                <p x-text="profile.bio"></p>
                            </section>

                            <section class="omni-agent-modal__section">
                                <h3>Specialties</h3>
                                <div class="omni-agent-modal__pills">
                                    <template x-for="specialty in profile.specialties || []" :key="specialty">
                                        <span x-text="specialty"></span>
                                    </template>
                                </div>
                            </section>

                            <section class="omni-agent-modal__section">
                                <h3>Service Areas</h3>
                                <div class="omni-agent-modal__pills">
                                    <template x-for="area in profile.service_areas || []" :key="area">
                                        <span x-text="area"></span>
                                    </template>
                                </div>
                            </section>

                            <section class="omni-agent-modal__section">
                                <h3>Languages</h3>
                                <div class="omni-agent-modal__pills">
                                    <template x-for="language in profile.languages_list || []" :key="language">
                                        <span x-text="language"></span>
                                    </template>
                                </div>
                            </section>

                            <form class="omni-agent-inquiry" x-show="showForm" x-on:submit.prevent="submitInquiry()">
                                <input type="hidden" name="inquiry_type" :value="inquiryType">
                                <div class="omni-agent-inquiry__head">
                                    <strong x-text="inquiryType === 'referral' ? 'Request Referral' : 'Contact Agent'"></strong>
                                    <span>Your request is routed through the OmniReferral team.</span>
                                </div>
                                <label>Your name<input type="text" x-model="form.name" required></label>
                                <label>Email<input type="email" x-model="form.email" required></label>
                                <label>Phone<input type="tel" x-model="form.phone"></label>
                                <label>Your city<input type="text" x-model="form.city"></label>
                                <label class="omni-agent-inquiry__full">Message<textarea rows="4" x-model="form.message" required></textarea></label>
                                <label class="omni-agent-inquiry__full">Property requirements<textarea rows="3" x-model="form.property_requirements" placeholder="Budget, beds/baths, timeline"></textarea></label>
                                <button type="submit" class="agent-btn agent-btn--orange" :disabled="submitting" x-text="submitting ? 'Sending...' : 'Submit Request'"></button>
                                <p x-show="successMessage" x-text="successMessage" class="omni-agent-inquiry__success"></p>
                            </form>
                        </main>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
function agentDirectoryModal() {
    return {
        open: false,
        loading: false,
        profile: null,
        showForm: false,
        inquiryType: 'contact',
        submitting: false,
        successMessage: '',
        form: { name: '', email: '', phone: '', city: '', message: '', property_requirements: '' },
        async openModal(slug, mode = 'contact') {
            this.open = true;
            this.loading = true;
            this.profile = null;
            this.showForm = false;
            this.inquiryType = mode;
            this.successMessage = '';
            document.body.style.overflow = 'hidden';

            try {
                const res = await fetch(`/agent/${slug}/preview`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.profile = data.profile;
            } finally {
                this.loading = false;
            }
        },
        closeModal() {
            this.open = false;
            this.profile = null;
            this.showForm = false;
            document.body.style.overflow = '';
        },
        startInquiry(mode) {
            this.inquiryType = mode;
            this.showForm = true;
            this.successMessage = '';
        },
        quickSave(slug) {
            const key = 'omnireferral_saved_agents';
            const saved = JSON.parse(localStorage.getItem(key) || '[]');
            if (!saved.includes(slug)) saved.push(slug);
            localStorage.setItem(key, JSON.stringify(saved));
        },
        saveAgent() {
            if (!this.profile?.slug) return;
            this.quickSave(this.profile.slug);
            this.successMessage = 'Agent saved to your browser list.';
        },
        async submitInquiry() {
            if (!this.profile?.slug) return;
            this.submitting = true;
            this.successMessage = '';
            const token = document.querySelector('meta[name="csrf-token"]')?.content;

            try {
                const res = await fetch(`/agent/${this.profile.slug}/inquiry`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify({
                        inquiry_type: this.inquiryType,
                        name: this.form.name,
                        email: this.form.email,
                        phone: this.form.phone,
                        city: this.form.city,
                        message: this.form.message,
                        property_requirements: this.form.property_requirements,
                    }),
                });
                const data = await res.json();
                if (res.ok) {
                    this.successMessage = data.message;
                    this.showForm = false;
                    this.form = { name: '', email: '', phone: '', city: '', message: '', property_requirements: '' };
                }
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>
@endpush
@endsection
