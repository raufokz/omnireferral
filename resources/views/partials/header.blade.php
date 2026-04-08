<header class="site-header" data-animate >
    <div class="container nav-shell">
        <a href="{{ route('home') }}" class="brand-mark" aria-label="OmniReferral home">
            <img src="{{ asset('images/omnireferral-logo.png') }}" alt="OmniReferral Logo" >
        </a>
        <nav class="main-nav" id="mainNav" aria-label="Primary navigation">
            @guest
                <a class="{{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}#how-it-works" data-nav-section="how-it-works">How It Works</a>
                <a class="{{ request()->routeIs('listings') || request()->routeIs('properties.show') ? 'is-active' : '' }}" href="{{ route('listings') }}">Listings</a>
                <a class="{{ request()->routeIs('pricing') || request()->routeIs('packages.*') ? 'is-active' : '' }}" href="{{ route('pricing') }}">Pricing</a>
                <a class="{{ request()->routeIs('agents.*') ? 'is-active' : '' }}" href="{{ route('agents.index') }}">Agents</a>
                <a class="{{ request()->routeIs('blog.*') ? 'is-active' : '' }}" href="{{ route('blog.index') }}">Blog</a>
                <a class="{{ request()->routeIs('contact') ? 'is-active' : '' }}" href="{{ route('contact') }}">Contact</a>
            @else
                <a class="{{ request()->routeIs('dashboard') || request()->routeIs('dashboard.*') || request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}">Workspace</a>
                <a href="{{ route('dashboard') }}#leads">Leads</a>
                <a class="{{ request()->routeIs('pricing') || request()->routeIs('packages.*') ? 'is-active' : '' }}" href="{{ route('pricing') }}">Packages</a>
                <a class="{{ request()->routeIs('contact') ? 'is-active' : '' }}" href="{{ route('contact') }}">Support</a>
                <a href="{{ route('dashboard') }}#profile">Profile</a>
            @endguest

            <div class="mobile-nav-actions">
                <hr class="mobile-nav-divider">
                @auth
                    <a href="{{ route('dashboard') }}" class="button button--orange">My Workspace</a>
                    <a href="{{ route('pricing') }}" class="button button--secondary">Packages</a>
                    <a href="{{ route('contact') }}" class="button button--ghost">Support</a>
                @else
                    <a href="{{ route('register') }}" class="button button--orange">Get Started</a>
                    <a href="{{ route('login') }}" class="button button--ghost">Login</a>
                    <a href="{{ route('contact') }}" class="button button--secondary">Contact Sales</a>
                @endauth
            </div>
        </nav>
        <div class="nav-actions" id="navActions">
            @auth
                <a href="{{ auth()->user()->dashboardRoute() }}" class="button button--ghost-blue">{{ auth()->user()->roleLabel() }}</a>
                <form method="POST" action="{{ route('logout') }}" class="nav-inline-form">
                    @csrf
                    <button type="submit" class="button button--orange">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="button button--ghost-blue">Login</a>
                <a href="{{ route('register') }}" class="button button--blue">Sign Up</a>
                <a href="{{ route('pricing') }}" class="button button--orange">Get Leads</a>
            @endauth
        </div>
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu" aria-expanded="false" aria-controls="mainNav">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>
