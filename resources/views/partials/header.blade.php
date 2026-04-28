@php
    $isHome = request()->routeIs('home');
    $isListings = request()->routeIs('listings') || request()->routeIs('properties.show');
    $isPricing = request()->routeIs('pricing') || request()->routeIs('packages.*');
    $isAgents = request()->routeIs('agents.*');
    $isReviews = request()->routeIs('reviews');
    $isWorkspace = request()->routeIs('dashboard', 'dashboard.*', 'admin.*');
    $isLeadOps = request()->routeIs('admin.leads.*');
    $isMoreActive = request()->routeIs(
        'about',
        'blog.*',
        'faq',
        'contact',
        'resources',
        'news',
        'careers',
        'surveys',
        'scam.prevention',
        'communication.policy'
    );
@endphp

<header class="site-header" data-animate>
    <div class="container nav-shell">
        <a href="{{ route('home') }}" aria-label="OmniReferral home" class="nav-brand">
            <img src="{{ asset('images/omnireferral-logo.png') }}" width="200" height="48" alt="OmniReferral Logo"
                class="nav-logo" loading="eager" decoding="async">
        </a>
        <div class="nav-offcanvas-slot">
        <nav class="main-nav" id="mainNav" aria-label="Primary navigation">
            <a class="{{ $isHome ? 'is-active' : '' }}" href="{{ route('home') }}#how-it-works"
                data-nav-section="how-it-works" @if($isHome) aria-current="page" @endif>How It Works</a>
            <a class="{{ $isListings ? 'is-active' : '' }}"
                href="{{ route('listings') }}" @if($isListings) aria-current="page" @endif>Listings</a>
            <a class="{{ $isPricing ? 'is-active' : '' }}"
                href="{{ route('pricing') }}" @if($isPricing) aria-current="page" @endif>Pricing</a>
            <a class="{{ $isAgents ? 'is-active' : '' }}"
                href="{{ route('agents.index') }}" @if($isAgents) aria-current="page" @endif>Agents</a>
            <a class="{{ $isReviews ? 'is-active' : '' }}"
                href="{{ route('reviews') }}" @if($isReviews) aria-current="page" @endif>Testimonials</a>
            <div class="nav-dropdown {{ $isMoreActive ? 'is-active' : '' }}"
                data-nav-dropdown>
                <button type="button"
                    class="nav-submenu-toggle {{ $isMoreActive ? 'is-active' : '' }}"
                    data-nav-submenu-toggle aria-expanded="false" aria-haspopup="true" aria-controls="mainNavMoreSubmenu">
                    More
                    <span class="nav-submenu-toggle__caret" aria-hidden="true"></span>
                </button>
                <div class="nav-submenu" data-nav-submenu id="mainNavMoreSubmenu">
                    <a class="{{ request()->routeIs('about') ? 'is-active' : '' }}"
                        href="{{ route('about') }}">About</a>
                    <a class="{{ request()->routeIs('blog.*') ? 'is-active' : '' }}"
                        href="{{ route('blog.index') }}">Blog</a>
                    <a class="{{ request()->routeIs('faq') ? 'is-active' : '' }}" href="{{ route('faq') }}">FAQ</a>
                    <a class="{{ request()->routeIs('contact') ? 'is-active' : '' }}"
                        href="{{ route('contact') }}">Contact</a>

                    <!-- <a class="{{ request()->routeIs('resources') ? 'is-active' : '' }}" href="{{ route('resources') }}">Resources</a> -->
                    <!-- <a class="{{ request()->routeIs('news') ? 'is-active' : '' }}" href="{{ route('news') }}">News</a> -->
                    <!-- <a class="{{ request()->routeIs('careers') ? 'is-active' : '' }}" href="{{ route('careers') }}">Careers</a> -->
                    <!-- <a class="{{ request()->routeIs('surveys') ? 'is-active' : '' }}" href="{{ route('surveys') }}">Campaign Tools</a> -->
                    <!-- <a class="{{ request()->routeIs('scam.prevention') ? 'is-active' : '' }}" href="{{ route('scam.prevention') }}">Scam Prevention</a> -->
                    <!-- <a class="{{ request()->routeIs('communication.policy') ? 'is-active' : '' }}"
                        href="{{ route('communication.policy') }}">Communication Policy</a> -->
                </div>
            </div>
            <!-- <a class="{{ request()->routeIs('contact') ? 'is-active' : '' }}" href="{{ route('contact') }}">Contact</a> -->
            @auth
                <a class="{{ $isWorkspace ? 'is-active' : '' }}"
                    href="{{ auth()->user()->dashboardRoute() }}" @if($isWorkspace) aria-current="page" @endif>Workspace</a>
                @if(auth()->user()->isStaff())
                    <a class="{{ $isLeadOps ? 'is-active' : '' }}"
                        href="{{ route('admin.leads.index') }}" @if($isLeadOps) aria-current="page" @endif>Lead Ops</a>
                @endif
            @endauth

            <div class="mobile-nav-actions">
                <hr class="mobile-nav-divider">
                @auth
                    <a href="{{ auth()->user()->dashboardRoute() }}" class="button button--orange">My Workspace</a>
                    <a href="{{ route('pricing') }}" class="button button--secondary">Packages</a>
                    <a href="{{ route('contact') }}" class="button button--ghost">Support</a>
                @else
                    <a href="{{ route('login') }}" class="button button--orange nav-auth-cta">Login / Register</a>
                    <a href="{{ route('pricing') }}" class="button button--secondary">Get Leads</a>
                @endauth
            </div>
        </nav>
        </div>
        <div class="nav-actions" id="navActions">
            @auth
                <a href="{{ auth()->user()->dashboardRoute() }}"
                    class="button button--ghost-blue">{{ auth()->user()->roleLabel() }}</a>
                <form method="POST" action="{{ route('logout') }}" class="nav-inline-form">
                    @csrf
                    <button type="submit" class="button button--orange">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="button button--blue nav-auth-cta">Login / Register</a>
                <a href="{{ route('pricing') }}" class="button button--orange">Get Leads</a>
            @endauth
        </div>
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu" aria-expanded="false"
            aria-controls="mainNav">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>
