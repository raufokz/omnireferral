@extends('layouts.app')

@push('styles')
    @vite('resources/css/modules/agent-directory.css')
@endpush

@section('content')
@php
    $profileCollection = $profiles->getCollection();
    $totalCount = method_exists($profiles, 'total') ? $profiles->total() : $profiles->count();
    $locationLabel = $location['label'] ?? null;
@endphp

<section class="page-hero agent-directory-hero">
    <div class="agent-directory-hero__glow" aria-hidden="true"></div>
    <div class="container agent-directory-hero__inner">
        <div class="agent-directory-hero__copy" data-animate="left">
            <span class="eyebrow">Agent Directory</span>
            <h1>{{ $locationLabel ? 'Real Estate Agents in '.$locationLabel : 'Find Real Estate Agents Nationwide' }}</h1>
            <p>Browse thousands of agent profiles by city, state, and specialty. Featured agents receive priority placement and more visibility.</p>
            <div class="agent-directory-hero__actions">
                <a href="#agent-directory-results" class="button button--orange">Browse Agents</a>
                <a href="{{ route('pricing') }}" class="button button--ghost-light">Get Featured Placement</a>
            </div>
            <div class="agent-directory-hero__proof">
                <span>City &amp; state coverage</span>
                <span>Featured priority placement</span>
                <span>Centralized lead routing</span>
            </div>
        </div>
        <aside class="agent-directory-hero__panel" data-animate="right">
            <span class="agent-directory-hero__panel-eyebrow">Directory Snapshot</span>
            <h2>{{ number_format($totalCount) }} agents{{ $locationLabel ? ' in '.$locationLabel : '' }}</h2>
            <p>Featured agents appear first, sorted by rating and profile freshness.</p>
        </aside>
    </div>
</section>

<section class="section agent-directory-section" id="agent-directory-results"
    x-data="agentDirectoryModal()"
    x-on:keydown.escape.window="closeModal()">
    <div class="container">
        <div class="agent-directory-toolbar" data-animate>
            <form method="get" action="{{ $location ? route('agents.location', Str::slug(strtolower($location['label']))) : route('agents.index') }}" class="agent-directory-filters">
                <label>
                    <span>Search</span>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Name, brokerage, specialty…">
                </label>
                <label>
                    <span>City</span>
                    <select name="city">
                        <option value="">All cities</option>
                        @foreach($filterCities ?? [] as $cityOption)
                            <option value="{{ $cityOption }}" @selected(mb_strtolower((string) request('city')) === mb_strtolower((string) $cityOption))>{{ $cityOption }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>State</span>
                    <select name="state">
                        <option value="">All states</option>
                        @foreach($filterStates ?? [] as $stateOption)
                            <option value="{{ $stateOption }}" @selected(strtoupper((string) request('state')) === strtoupper((string) $stateOption))>{{ $stateOption }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="agent-directory-filters__actions">
                    <button type="submit" class="button button--orange">Apply</button>
                    <a href="{{ route('agents.index') }}" class="button button--ghost-blue">Reset</a>
                </div>
            </form>
        </div>

        <div class="agent-seo-locations">
            @foreach(['texas','florida','california','arizona','georgia','dallas','miami','phoenix','atlanta','austin'] as $seoSlug)
                <a href="{{ route('agents.location', $seoSlug) }}">{{ Str::title(str_replace('-', ' ', $seoSlug)) }}</a>
            @endforeach
        </div>

        <div class="agent-directory-premium" style="margin-top:1.5rem;">
            @foreach($profiles as $profile)
                @php $card = \App\Support\AgentDirectory::publicCardPayload($profile); @endphp
                <article class="agent-card-premium" data-agent-card
                    x-on:click="openModal('{{ $profile->slug }}')"
                    role="button" tabindex="0"
                    x-on:keydown.enter="openModal('{{ $profile->slug }}')">
                    <div class="agent-card-premium__media">
                        <img src="{{ $card['headshot_url'] }}" alt="{{ $card['name'] }}" loading="lazy" width="400" height="300">
                        @if($card['is_featured'])
                            <span class="agent-card-premium__featured">⭐ Featured Agent</span>
                        @endif
                        <span class="agent-card-premium__rating">{{ $card['rating'] }} · {{ $card['review_count'] }} reviews</span>
                    </div>
                    <div class="agent-card-premium__body">
                        <h3>{{ $card['name'] }}</h3>
                        <p class="agent-card-premium__brokerage">{{ $card['brokerage'] }}</p>
                        <div class="agent-card-premium__meta">
                            @if($card['city'] || $card['state'])
                                <span>{{ trim($card['city'].', '.$card['state'], ', ') }}</span>
                            @endif
                            @if($card['years_of_experience'])
                                <span>{{ $card['years_of_experience'] }}+ yrs</span>
                            @endif
                        </div>
                        <p class="agent-card-premium__specialty">{{ Str::limit($card['specialties_text'], 90) }}</p>
                    </div>
                </article>
            @endforeach
        </div>

        @if($profiles->count() === 0)
            <div class="agent-directory-empty-state">
                <h3>No agents found</h3>
                <p>Try another city or state, or browse the full directory.</p>
                <a href="{{ route('agents.index') }}" class="button button--orange">View all agents</a>
            </div>
        @endif

        <div class="pagination-wrap">{{ $profiles->links() }}</div>
    </div>

    <template x-if="open">
        <div class="agent-modal" x-show="open" x-transition>
            <div class="agent-modal__backdrop" x-on:click="closeModal()"></div>
            <div class="agent-modal__panel" x-on:click.stop role="dialog" aria-modal="true" :aria-label="profile?.name || 'Agent profile'">
                <button type="button" class="agent-modal__close" x-on:click="closeModal()" aria-label="Close">&times;</button>
                <template x-if="profile">
                    <div>
                        <div class="agent-modal__hero">
                            <img :src="profile.headshot_url" :alt="profile.name" loading="lazy">
                            <div>
                                <template x-if="profile.is_featured">
                                    <span class="agent-card-premium__featured">⭐ Featured Agent</span>
                                </template>
                                <h2 x-text="profile.name" style="margin:0.5rem 0 0.25rem;"></h2>
                                <p x-text="profile.brokerage" style="color:#5a6b82;"></p>
                                <p x-text="profile.service_area" style="margin-top:0.5rem;"></p>
                                <p style="margin-top:0.75rem;"><strong x-text="profile.rating"></strong> rating · <span x-text="profile.review_count"></span> reviews</p>
                                <template x-if="profile.years_of_experience">
                                    <p><span x-text="profile.years_of_experience"></span>+ years experience</p>
                                </template>
                            </div>
                        </div>
                        <div class="agent-modal__content">
                            <p x-text="profile.bio"></p>
                            <template x-if="profile.languages"><p><strong>Languages:</strong> <span x-text="profile.languages"></span></p></template>
                            <template x-if="profile.market_areas"><p><strong>Market areas:</strong> <span x-text="profile.market_areas"></span></p></template>
                            <template x-if="profile.specialties?.length">
                                <div class="agent-card-premium__meta">
                                    <template x-for="item in profile.specialties" :key="item">
                                        <span x-text="item"></span>
                                    </template>
                                </div>
                            </template>
                            <div class="agent-modal__actions">
                                <button type="button" class="button button--orange" x-on:click="inquiryType='contact'; showForm=true">Contact Agent</button>
                                <button type="button" class="button button--ghost-blue" x-on:click="inquiryType='referral'; showForm=true">Request Referral</button>
                                <button type="button" class="button button--ghost-blue" x-on:click="saveAgent()">Save Agent</button>
                            </div>
                            <form class="agent-inquiry-form" x-show="showForm" x-on:submit.prevent="submitInquiry()">
                                <p class="text-muted" style="font-size:0.9rem;">Your request goes to the OmniReferral team. We route opportunities centrally and follow up with you.</p>
                                <input type="hidden" name="inquiry_type" :value="inquiryType">
                                <label>Your name<input type="text" x-model="form.name" required></label>
                                <label>Email<input type="email" x-model="form.email" required></label>
                                <label>Phone<input type="tel" x-model="form.phone"></label>
                                <label>Your city<input type="text" x-model="form.city"></label>
                                <label>Message<textarea rows="4" x-model="form.message" required></textarea></label>
                                <label>Property requirements<textarea rows="3" x-model="form.property_requirements" placeholder="Budget, beds/baths, timeline…"></textarea></label>
                                <button type="submit" class="button button--orange" :disabled="submitting" x-text="submitting ? 'Sending…' : 'Submit to OmniReferral Team'"></button>
                                <p x-show="successMessage" x-text="successMessage" style="color:#0b3668;font-weight:600;"></p>
                            </form>
                        </div>
                    </div>
                </template>
                <template x-if="loading">
                    <div style="padding:3rem;text-align:center;">Loading profile…</div>
                </template>
            </div>
        </div>
    </template>
</section>

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
        async openModal(slug) {
            this.open = true;
            this.loading = true;
            this.showForm = false;
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
            document.body.style.overflow = '';
        },
        saveAgent() {
            if (!this.profile?.slug) return;
            const key = 'omnireferral_saved_agents';
            const saved = JSON.parse(localStorage.getItem(key) || '[]');
            if (!saved.includes(this.profile.slug)) saved.push(this.profile.slug);
            localStorage.setItem(key, JSON.stringify(saved));
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
