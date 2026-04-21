@if (request()->routeIs('login', 'register', 'password.*'))
    <header class="site-header site-header--auth" data-animate>
        <div class="container nav-shell nav-shell--auth">
            <a href="{{ route('home') }}" aria-label="OmniReferral home" class="nav-brand nav-brand--auth">
                <img src="{{ asset('images/omnireferral-logo.png') }}" height="100" width="100" alt="OmniReferral Logo"
                    class="nav-logo" loading="eager" decoding="async">
            </a>

            <nav class="auth-header-actions" aria-label="Authentication navigation">
                <a href="{{ route('home') }}" class="auth-header-link">Back to Home</a>
                <a href="{{ route('contact') }}" class="auth-header-link">Support</a>
                @if (request()->routeIs('login'))
                    <a href="{{ route('register') }}" class="button button--orange auth-header-cta">Create Account</a>
                @else
                    <a href="{{ route('login') }}" class="button button--orange auth-header-cta">Sign In</a>
                @endif
            </nav>
        </div>
    </header>
@else
    <header class="site-header" data-animate>
        <div class="container nav-shell">
            <a href="{{ route('home') }}" aria-label="OmniReferral home" class="nav-brand">
                <img src="{{ asset('images/omnireferral-logo.png') }}" height="100" width="100" alt="OmniReferral Logo"
                    class="nav-logo" loading="eager" decoding="async">
            </a>
            <nav class="main-nav" id="mainNav" aria-label="Primary navigation">
                <a class="{{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}#how-it-works"
                    data-nav-section="how-it-works">How It Works</a>
                <a class="{{ request()->routeIs('listings') || request()->routeIs('properties.show') ? 'is-active' : '' }}"
                    href="{{ route('listings') }}">Listings</a>
                <a class="{{ request()->routeIs('pricing') || request()->routeIs('packages.*') ? 'is-active' : '' }}"
                    href="{{ route('pricing') }}">Pricing</a>
                <a class="{{ request()->routeIs('agents.*') ? 'is-active' : '' }}"
                    href="{{ route('agents.index') }}">Agents</a>
                <a class="{{ request()->routeIs('reviews') ? 'is-active' : '' }}"
                    href="{{ route('reviews') }}">Testimonials</a>
                <div class="nav-dropdown {{ request()->routeIs('about', 'blog.*', 'faq', 'resources', 'news', 'careers', 'surveys', 'scam.prevention', 'communication.policy') ? 'is-active' : '' }}"
                    data-nav-dropdown>
                    <button type="button"
                        class="nav-submenu-toggle {{ request()->routeIs('about', 'blog.*', 'faq', 'resources', 'news', 'careers', 'surveys', 'scam.prevention', 'communication.policy') ? 'is-active' : '' }}"
                        data-nav-submenu-toggle aria-expanded="false" aria-haspopup="true">
                        More+
                        <span class="nav-submenu-toggle__caret" aria-hidden="true"></span>
                    </button>
                    <div class="nav-submenu" data-nav-submenu>
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
                    <a class="{{ request()->routeIs('dashboard', 'dashboard.*', 'admin.*') ? 'is-active' : '' }}"
                        href="{{ auth()->user()->dashboardRoute() }}">Workspace</a>
                    @if(auth()->user()->isStaff())
                        <a class="{{ request()->routeIs('admin.leads.*') ? 'is-active' : '' }}"
                            href="{{ route('admin.leads.index') }}">Lead Ops</a>
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
@endif
