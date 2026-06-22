<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    @include('partials.google-tag')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0B3668">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
    $avatarUrl = $workspaceUser?->profilePhotoPublicUrl() ?? asset(\App\Support\AgentAvatar::defaultPath());

    $initials = collect(explode(' ', (string) ($workspaceUser?->name ?? 'Omni User')))
        ->filter()
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->take(2)
        ->implode('');

    $accountNavItems = [
        ['label' => 'Profile', 'route' => route('account.profile'), 'active' => ['account.profile'], 'icon' => 'profile'],
        ['label' => 'Security', 'route' => route('account.security'), 'active' => ['account.security'], 'icon' => 'security'],
    ];

    $operationsOverviewRoute = match (true) {
        $workspaceUser?->isSuperAdmin() => route('super-admin.dashboard'),
        $role === 'staff' => route('staff.dashboard'),
        $role === 'admin' => route('admin.dashboard'),
        default => route('dashboard'),
    };
    $operationsOverviewActive = ['admin.dashboard', 'staff.dashboard', 'super-admin.dashboard'];

    $dashboardNavItems = match ($role) {
        'buyer' => [
            ['label' => 'Overview', 'route' => route('dashboard.buyer'), 'active' => ['dashboard.buyer'], 'icon' => 'dashboard'],
            ['label' => 'Search', 'route' => route('listings'), 'active' => ['listings', 'properties.show'], 'icon' => 'search'],
            [
                'label' => 'Operations',
                'icon' => 'operations',
                'children' => [
                    ['label' => 'Saved Homes', 'route' => route('dashboard.buyer.saved'), 'active' => ['dashboard.buyer.saved'], 'icon' => 'saved'],
                    ['label' => 'Requests', 'route' => route('dashboard.buyer.requests'), 'active' => ['dashboard.buyer.requests'], 'icon' => 'requests'],
                    ['label' => 'Enquiries', 'route' => route('dashboard.enquiries.index'), 'active' => ['dashboard.enquiries.*'], 'icon' => 'enquiries'],
                ],
            ],
            [
                'label' => 'Content',
                'icon' => 'content',
                'children' => [
                    ['label' => 'Resources', 'route' => route('resources'), 'active' => ['resources'], 'icon' => 'content'],
                    ['label' => 'Reviews', 'route' => route('reviews'), 'active' => ['reviews'], 'icon' => 'saved'],
                ],
            ],
            ['label' => 'Marketplace', 'route' => route('listings'), 'active' => ['listings', 'properties.show'], 'icon' => 'marketplace'],
            ['label' => 'Account', 'icon' => 'profile', 'children' => $accountNavItems],
        ],
        'seller' => [
            ['label' => 'Overview', 'route' => route('dashboard.seller'), 'active' => ['dashboard.seller'], 'icon' => 'dashboard'],
            ['label' => 'Search', 'route' => route('listings'), 'active' => ['listings', 'properties.show'], 'icon' => 'search'],
            [
                'label' => 'Operations',
                'icon' => 'operations',
                'children' => [
                    ['label' => 'My Listings', 'route' => route('dashboard.seller.listings'), 'active' => ['dashboard.seller.listings', 'properties.edit'], 'icon' => 'listings'],
                    ['label' => 'Requests', 'route' => route('dashboard.seller.requests'), 'active' => ['dashboard.seller.requests'], 'icon' => 'requests'],
                    ['label' => 'Enquiries', 'route' => route('dashboard.enquiries.index'), 'active' => ['dashboard.enquiries.*'], 'icon' => 'enquiries'],
                ],
            ],
            [
                'label' => 'Content',
                'icon' => 'content',
                'children' => [
                    ['label' => 'Resources', 'route' => route('resources'), 'active' => ['resources'], 'icon' => 'content'],
                    ['label' => 'Reviews', 'route' => route('reviews'), 'active' => ['reviews'], 'icon' => 'saved'],
                ],
            ],
            ['label' => 'Marketplace', 'route' => route('listings'), 'active' => ['listings', 'properties.show'], 'icon' => 'marketplace'],
            ['label' => 'Account', 'icon' => 'profile', 'children' => $accountNavItems],
        ],
        'agent' => [
            ['label' => 'Overview', 'route' => route('dashboard.agent'), 'active' => ['dashboard.agent'], 'icon' => 'dashboard'],
            ['label' => 'Search', 'route' => route('listings'), 'active' => ['listings', 'properties.show'], 'icon' => 'search'],
            [
                'label' => 'Operations',
                'icon' => 'operations',
                'children' => [
                    ['label' => 'Leads', 'route' => route('agent.leads.index'), 'active' => ['agent.leads.*'], 'icon' => 'leads'],
                    ['label' => 'Listings', 'route' => route('agent.listings.index'), 'active' => ['agent.listings.*', 'properties.edit'], 'icon' => 'listings'],
                    ['label' => 'Messages', 'route' => route('agent.messages.index'), 'active' => ['agent.messages.*'], 'icon' => 'messages'],
                    ['label' => 'Enquiries', 'route' => route('dashboard.enquiries.index'), 'active' => ['dashboard.enquiries.*'], 'icon' => 'enquiries'],
                ],
            ],
            [
                'label' => 'Content',
                'icon' => 'content',
                'children' => [
                    ['label' => 'Resources', 'route' => route('resources'), 'active' => ['resources'], 'icon' => 'content'],
                    ['label' => 'Reviews', 'route' => route('reviews'), 'active' => ['reviews'], 'icon' => 'saved'],
                ],
            ],
            ['label' => 'Marketplace', 'route' => route('listings'), 'active' => ['listings', 'properties.show'], 'icon' => 'marketplace'],
            [
                'label' => 'Profile & Account',
                'icon' => 'profile',
                'children' => array_merge([
                    ['label' => 'Agent Profile', 'route' => route('agent.profile'), 'active' => ['agent.profile'], 'icon' => 'agent'],
                ], $accountNavItems),
            ],
        ],
        'staff' => [
            ['label' => 'Overview', 'route' => $operationsOverviewRoute, 'active' => $operationsOverviewActive, 'icon' => 'dashboard'],
            ['label' => 'Search', 'route' => route('admin.search'), 'active' => ['admin.search'], 'icon' => 'search'],
            [
                'label' => 'Operations',
                'icon' => 'operations',
                'children' => [
                    ['label' => 'Lead Registry', 'route' => route('admin.leads.index'), 'active' => ['admin.leads.*'], 'icon' => 'leads'],
                    ['label' => 'Agent Profiles', 'route' => route('admin.agents.manage'), 'active' => ['admin.agent-profiles.*', 'admin.agents.*'], 'icon' => 'users'],
                    ['label' => 'Properties', 'route' => route('admin.properties.index'), 'active' => ['admin.properties.*'], 'icon' => 'properties'],
                    ['label' => 'Enquiries', 'route' => route('admin.enquiries.index'), 'active' => ['admin.enquiries.*'], 'icon' => 'enquiries'],
                    ['label' => 'Users', 'route' => route('admin.users.index'), 'active' => ['admin.users.*'], 'icon' => 'users'],
                ],
            ],
            ['label' => 'Marketplace', 'route' => route('listings'), 'active' => ['listings', 'properties.show'], 'icon' => 'marketplace'],
            ['label' => 'Account', 'icon' => 'profile', 'children' => $accountNavItems],
        ],
        'admin' => array_values(array_filter(array_merge(
            [
                ['label' => 'Overview', 'route' => $operationsOverviewRoute, 'active' => $operationsOverviewActive, 'icon' => 'dashboard'],
                ['label' => 'Search', 'route' => route('admin.search'), 'active' => ['admin.search'], 'icon' => 'search'],
                [
                    'label' => 'Operations',
                    'icon' => 'operations',
                    'children' => [
                        ['label' => 'Users', 'route' => route('admin.users.index'), 'active' => ['admin.users.*'], 'icon' => 'users'],
                        ['label' => 'Agent Profiles', 'route' => route('admin.agents.manage'), 'active' => ['admin.agent-profiles.*', 'admin.agents.*'], 'icon' => 'users'],
                        ['label' => 'Properties', 'route' => route('admin.properties.index'), 'active' => ['admin.properties.*'], 'icon' => 'properties'],
                        ['label' => 'Enquiries', 'route' => route('admin.enquiries.index'), 'active' => ['admin.enquiries.*'], 'icon' => 'enquiries'],
                        ['label' => 'Lead Registry', 'route' => route('admin.leads.index'), 'active' => ['admin.leads.*'], 'icon' => 'leads'],
                    ],
                ],
                [
                    'label' => 'Content',
                    'icon' => 'content',
                    'children' => [
                        ['label' => 'Blog', 'route' => route('admin.blog.index'), 'active' => ['admin.blog.*'], 'icon' => 'content'],
                        ['label' => 'Testimonials', 'route' => route('admin.testimonials.index'), 'active' => ['admin.testimonials.*'], 'icon' => 'saved'],
                        ['label' => 'Pricing Plans', 'route' => route('admin.pricing-plans.index'), 'active' => ['admin.pricing-plans.*'], 'icon' => 'marketplace'],
                        ['label' => 'Packages', 'route' => route('admin.packages.index'), 'active' => ['admin.packages.*'], 'icon' => 'marketplace'],
                    ],
                ],
                [
                    'label' => 'System',
                    'icon' => 'security',
                    'children' => array_values(array_filter([
                        ['label' => 'Exports', 'route' => route('admin.exports.index'), 'active' => ['admin.exports.*'], 'icon' => 'audit'],
                        $workspaceUser?->can('webhook_events.view')
                            ? ['label' => 'Webhooks', 'route' => route('admin.webhook-events.index'), 'active' => ['admin.webhook-events.*'], 'icon' => 'audit']
                            : null,
                        $workspaceUser && $workspaceUser->can('viewAuditLog', $workspaceUser)
                            ? ['label' => 'Audit Log', 'route' => route('admin.activity.index'), 'active' => ['admin.activity.*'], 'icon' => 'audit']
                            : null,
                        ['label' => 'GoHighLevel', 'route' => route('admin.ghl.index'), 'active' => ['admin.ghl.*'], 'icon' => 'audit'],
                        ['label' => 'Email & Auth Logs', 'route' => route('admin.email.index'), 'active' => ['admin.email.*'], 'icon' => 'audit'],
                    ])),
                ],
                ['label' => 'Marketplace', 'route' => route('listings'), 'active' => ['listings', 'properties.show'], 'icon' => 'marketplace'],
                ['label' => 'Account', 'icon' => 'profile', 'children' => $accountNavItems],
            ]
        ))),
        default => [
            ['label' => 'Dashboard Home', 'route' => route('dashboard'), 'active' => ['dashboard'], 'icon' => 'dashboard'],
            ['label' => 'Account', 'icon' => 'profile', 'children' => $accountNavItems],
        ],
    };

    $isNavItemActive = null;
    $isNavItemActive = function (array $item) use (&$isNavItemActive): bool {
        $selfActive = collect($item['active'] ?? [])->contains(fn ($pattern) => request()->routeIs($pattern));
        $childActive = collect($item['children'] ?? [])->contains(fn ($child) => $isNavItemActive($child));

        return $selfActive || $childActive;
    };

    $settingsLinks = [
        ['label' => 'Profile & account', 'route' => route('account.profile')],
        ['label' => 'Account Security', 'route' => route('account.security')],
        ['label' => 'Help Center', 'route' => route('contact')],
    ];

    if ($role === 'agent') {
        $settingsLinks[] = ['label' => 'Edit Agent Profile', 'route' => route('agent.profile')];
    }

    $dashboardNotices = match ($role) {
        'admin', 'staff' => [
            ['title' => 'Review operational queues', 'copy' => 'Check enquiries, properties, users, and lead registry health.', 'route' => $operationsOverviewRoute],
            ['title' => 'Open platform search', 'copy' => 'Find users, listings, enquiries, and records quickly.', 'route' => route('admin.search')],
            ['title' => 'Monitor listing review', 'copy' => 'Approve or reject user-submitted property inventory.', 'route' => route('admin.properties.index')],
        ],
        'agent' => [
            ['title' => 'Prioritize new messages', 'copy' => 'Keep listing and profile conversations moving.', 'route' => route('agent.messages.index')],
            ['title' => 'Check lead queue', 'copy' => 'Update contact status while intent is fresh.', 'route' => route('agent.leads.index')],
            ['title' => 'Manage inventory', 'copy' => 'Review listing capacity and pending approvals.', 'route' => route('agent.listings.index')],
        ],
        'seller' => [
            ['title' => 'Keep listings current', 'copy' => 'Submit or review property visibility in the seller workspace.', 'route' => route('dashboard.seller.listings')],
            ['title' => 'Review seller requests', 'copy' => 'Track qualified demand and in-market activity.', 'route' => route('dashboard.seller.requests')],
        ],
        'buyer' => [
            ['title' => 'Review saved homes', 'copy' => 'Compare your shortlist and contact agents from listings.', 'route' => route('dashboard.buyer.saved')],
            ['title' => 'Track buyer requests', 'copy' => 'Follow request movement from submitted to matched.', 'route' => route('dashboard.buyer.requests')],
        ],
        default => [
            ['title' => 'Open marketplace', 'copy' => 'Browse active property listings and agent-backed opportunities.', 'route' => route('listings')],
            ['title' => 'Contact support', 'copy' => 'Ask for help configuring your workspace.', 'route' => route('contact')],
        ],
    };
@endphp
<body class="antialiased dashboard-shell-body" data-dashboard-role="{{ $role ?: 'guest' }}">
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
                <div class="dashboard-shell__system-chip">
                    <span></span>
                    Live workspace
                </div>
            </div>

            <nav class="dashboard-shell__nav" aria-label="{{ $roleLabel }} navigation" data-dashboard-nav>
                @foreach ($dashboardNavItems as $item)
                    @include('partials.dashboard.nav-item', [
                        'item' => $item,
                        'isNavItemActive' => $isNavItemActive,
                        'level' => 0,
                    ])
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

                <div class="dashboard-shell__header-copy">
                    <span>{{ now()->format('l, M j') }}</span>
                    <strong>{{ $roleLabel }} command center</strong>
                </div>

                <div class="dashboard-shell__actions">
                    <a href="{{ route('listings') }}" class="dashboard-shell__quick-link">Marketplace</a>

                    <button type="button" class="dashboard-shell__icon-button" data-dashboard-theme-toggle aria-label="Toggle dashboard dark mode" aria-pressed="false">
                        <span class="dashboard-shell__theme-light">☾</span>
                        <span class="dashboard-shell__theme-dark">☀</span>
                    </button>

                    <div class="dashboard-shell__notifications" data-dashboard-notifications>
                        <button type="button" class="dashboard-shell__icon-button" data-dashboard-notifications-toggle aria-label="Open notifications panel" aria-expanded="false">
                            <span>•</span>
                        </button>
                        <div class="dashboard-shell__notifications-panel">
                            <div>
                                <span class="eyebrow">Alerts</span>
                                <h2>Action Center</h2>
                            </div>
                            <ul>
                                @foreach($dashboardNotices as $notice)
                                    <li>
                                        <a href="{{ $notice['route'] }}">
                                            <strong>{{ $notice['title'] }}</strong>
                                            <span>{{ $notice['copy'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

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
            const subnavGroups = document.querySelectorAll('[data-dashboard-subnav]');

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

            subnavGroups.forEach((group) => {
                const toggle = group.querySelector('[data-dashboard-subnav-toggle]');
                const panel = group.querySelector('[data-dashboard-subnav-panel]');
                if (!toggle || !panel) {
                    return;
                }

                const setOpen = (open) => {
                    group.classList.toggle('is-open', open);
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                    panel.setAttribute('aria-hidden', open ? 'false' : 'true');
                    panel.inert = !open;
                };

                setOpen(group.classList.contains('is-open'));

                toggle.addEventListener('click', () => {
                    setOpen(!group.classList.contains('is-open'));
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
