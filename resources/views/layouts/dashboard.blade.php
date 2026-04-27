<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    @include('partials.google-tag')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0B3668">

    <title>{{ $meta['title'] ?? 'Dashboard | OmniReferral' }}</title>
    <meta name="description" content="{{ $meta['description'] ?? 'Role-based OmniReferral dashboard workspace.' }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
    @include('partials.favicon')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
@php
    $workspaceUser = auth()->user();
    $role = $workspaceUser?->role;
    $roleLabel = $workspaceUser?->roleLabel() ?? 'Workspace';
    $avatarUrl = $workspaceUser?->avatar
        ? asset('storage/' . ltrim($workspaceUser->avatar, '/'))
        : asset('images/realtors/3.png');

    $initials = collect(explode(' ', (string) ($workspaceUser?->name ?? 'Omni User')))
        ->filter()
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->take(2)
        ->implode('');

    $dashboardNavItems = match ($role) {
        'buyer' => [
            ['label' => 'Overview', 'route' => route('dashboard.buyer'), 'active' => ['dashboard.buyer']],
            ['label' => 'Saved Homes', 'route' => route('dashboard.buyer.saved'), 'active' => ['dashboard.buyer.saved']],
            ['label' => 'Requests', 'route' => route('dashboard.buyer.requests'), 'active' => ['dashboard.buyer.requests']],
            ['label' => 'Marketplace', 'route' => route('listings'), 'active' => ['listings', 'properties.show']],
        ],
        'seller' => [
            ['label' => 'Overview', 'route' => route('dashboard.seller'), 'active' => ['dashboard.seller']],
            ['label' => 'Listings', 'route' => route('dashboard.seller.listings'), 'active' => ['dashboard.seller.listings', 'properties.edit']],
            ['label' => 'Requests', 'route' => route('dashboard.seller.requests'), 'active' => ['dashboard.seller.requests']],
            ['label' => 'Marketplace', 'route' => route('listings'), 'active' => ['listings', 'properties.show']],
        ],
        'agent' => [
            ['label' => 'Overview', 'route' => route('dashboard.agent'), 'active' => ['dashboard.agent']],
            ['label' => 'Profile', 'route' => route('agent.profile'), 'active' => ['agent.profile']],
            ['label' => 'Leads', 'route' => route('agent.leads.index'), 'active' => ['agent.leads.*']],
            ['label' => 'Listings', 'route' => route('agent.listings.index'), 'active' => ['agent.listings.*', 'properties.edit']],
            ['label' => 'Messages', 'route' => route('agent.messages.index'), 'active' => ['agent.messages.*']],
        ],
        'admin', 'staff' => [
            ['label' => 'Overview', 'route' => route('admin.dashboard'), 'active' => ['admin.dashboard']],
            ['label' => 'Lead Registry', 'route' => route('admin.leads.index'), 'active' => ['admin.leads.*']],
            ['label' => 'Blog', 'route' => route('admin.blog.index'), 'active' => ['admin.blog.*']],
            ['label' => 'Testimonials', 'route' => route('admin.testimonials.index'), 'active' => ['admin.testimonials.*']],
            ['label' => 'Marketplace', 'route' => route('listings'), 'active' => ['listings', 'properties.show']],
        ],
        default => [
            ['label' => 'Dashboard Home', 'route' => route('dashboard'), 'active' => ['dashboard']],
        ],
    };

    $settingsLinks = [
        ['label' => 'Account Security', 'route' => route('account.security')],
        ['label' => 'Help Center', 'route' => route('contact')],
    ];

    if ($role === 'agent') {
        $settingsLinks[] = ['label' => 'Edit Agent Profile', 'route' => route('agent.profile')];
    }
@endphp
<body class="antialiased dashboard-shell-body">
    <a href="#main-content" class="skip-link">Skip to content</a>
    <div class="dashboard-shell" id="dashboardShell">
        <aside class="dashboard-shell__sidebar" id="dashboardSidebar" aria-label="Dashboard sidebar">
            <div class="dashboard-shell__brand">
                <a href="{{ route('dashboard') }}" class="dashboard-shell__brand-link">
                    <img src="{{ asset('images/omnireferral-logo.png') }}" alt="OmniReferral logo">
                    <div>
                        <strong>OmniReferral</strong>
                        <span>{{ $roleLabel }}</span>
                    </div>
                </a>
            </div>

            <nav class="dashboard-shell__nav">
                @foreach ($dashboardNavItems as $item)
                    @php
                        $isActive = collect($item['active'] ?? [])->contains(fn ($pattern) => request()->routeIs($pattern));
                    @endphp
                    <a href="{{ $item['route'] }}" class="{{ $isActive ? 'is-active' : '' }}" @if($isActive) aria-current="page" @endif>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="dashboard-shell__main">
            <header class="dashboard-shell__header">
                <button type="button" class="dashboard-shell__menu-toggle" data-sidebar-toggle aria-expanded="false" aria-controls="dashboardSidebar">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <div class="dashboard-shell__actions">
                    <a href="{{ route('dashboard') }}" class="dashboard-shell__avatar" aria-label="Go to dashboard home">
                        <img src="{{ $avatarUrl }}" alt="{{ $workspaceUser?->name ?? 'User' }} avatar" loading="lazy">
                        <span>{{ $initials ?: 'OU' }}</span>
                    </a>

                    <div class="dashboard-shell__settings" data-settings-menu>
                        <button type="button" class="dashboard-shell__settings-trigger" data-settings-toggle aria-expanded="false" aria-controls="dashboardSettingsMenu">
                            Settings
                            <svg viewBox="0 0 20 20" aria-hidden="true">
                                <path fill="currentColor" d="M5.5 7.5 10 12l4.5-4.5 1 1L10 14 4.5 8.5z"/>
                            </svg>
                        </button>

                        <div class="dashboard-shell__settings-menu" id="dashboardSettingsMenu" role="menu">
                            @foreach($settingsLinks as $setting)
                                <a href="{{ $setting['route'] }}" role="menuitem">{{ $setting['label'] }}</a>
                            @endforeach
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dashboard-shell__logout">Logout</button>
                    </form>
                </div>
            </header>

            <main id="main-content" class="dashboard-shell__content" tabindex="-1">
                @if (session('info'))
                    <div class="app-flash app-flash--info" role="alert">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 1010 10A10 10 0 0012 2z"></path></svg>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif
                @if (session('success'))
                    <div class="app-flash app-flash--success" role="alert">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="app-flash app-flash--error" role="alert">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"></path></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="app-flash app-flash--error" role="alert">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"></path></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @hasSection('dashboard_title')
                    <section class="dashboard-shell__page-head">
                        <div>
                            <span class="eyebrow">@yield('dashboard_eyebrow', $roleLabel)</span>
                            <h1>@yield('dashboard_title')</h1>
                            @hasSection('dashboard_description')
                                <p>@yield('dashboard_description')</p>
                            @endif
                        </div>
                        @hasSection('dashboard_actions')
                            <div class="dashboard-shell__page-actions">
                                @yield('dashboard_actions')
                            </div>
                        @endif
                    </section>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const shell = document.getElementById('dashboardShell');
            const sidebar = document.getElementById('dashboardSidebar');
            const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
            const settingsWrap = document.querySelector('[data-settings-menu]');
            const settingsToggle = document.querySelector('[data-settings-toggle]');

            if (sidebarToggle && shell && sidebar) {
                sidebarToggle.addEventListener('click', () => {
                    const isOpen = shell.classList.toggle('sidebar-open');
                    sidebarToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            }

            if (settingsWrap && settingsToggle) {
                settingsToggle.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const isOpen = settingsWrap.classList.toggle('is-open');
                    settingsToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });

                document.addEventListener('click', (event) => {
                    if (!settingsWrap.contains(event.target)) {
                        settingsWrap.classList.remove('is-open');
                        settingsToggle.setAttribute('aria-expanded', 'false');
                    }
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
