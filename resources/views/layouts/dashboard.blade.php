<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FCF8F5">

    <title>{{ $meta['title'] ?? 'Platform Dashboard | OmniReferral' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('partials.favicon')

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/modules/dashboard.css'])
</head>
<body class="antialiased page-dashboard dashboard-vibrant">
    <div class="dash-app-wrapper">
        <div class="dash-app-shell">
            <!-- Left Sidebar -->
            <aside class="dash-sidebar-left">
                <div class="dash-sidebar__logo">
                    <img src="{{ asset('images/logo.png') }}" alt="OmniReferral" style="height: 32px;">
                </div>

                <div class="dash-sidebar__profile">
                    @php
                        $userAvatar = auth()->user()?->avatar 
                            ? asset('storage/' . ltrim(auth()->user()->avatar, '/')) 
                            : asset('images/realtors/3.png');
                    @endphp
                    <img src="{{ $userAvatar }}" alt="Profile" class="dash-avatar">
                    <h3 class="dash-profile-name">{{ auth()->user()?->name ?? 'Sarah Connor' }}</h3>
                    <span class="dash-profile-email">{{ auth()->user()?->email ?? 'sarahc@gmail.com' }}</span>
                </div>

                <nav class="dash-nav">
                    @yield('dashboard_nav')
                </nav>

                <div class="dash-sidebar__bottom-graphic"></div>
            </aside>

            <!-- Main Content Area -->
            <main class="dash-main-area">
                <header class="dash-header">
                    <div class="dash-header__greeting">
                        <h1>Hello, {{ explode(' ', auth()->user()?->name ?? 'Sara')[0] }}</h1>
                        <p>Today is {{ now()->format('l, d F Y') }}</p>
                    </div>
                    <div class="dash-header__actions">
                        <button class="dash-btn-icon" aria-label="Search">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </button>
                        <a href="#" class="dash-btn-primary">Add New Project</a>
                    </div>
                </header>

                <div class="dash-content">
                    @if (session('success'))
                        <div class="app-flash" style="margin-bottom: 20px;">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="app-flash app-flash--error" style="margin-bottom: 20px;">{{ session('error') }}</div>
                    @endif

                    @yield('content')
                </div>
            </main>

            <!-- Right Sidebar (Calendar/Activity) -->
            <aside class="dash-sidebar-right">
                <div class="dash-sidebar-right__header">
                    <h2>Calendar</h2>
                    <button class="dash-btn-icon dash-btn-icon--circle" aria-label="Notifications">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        <span class="notification-dot"></span>
                    </button>
                </div>

                <div class="dash-timeline">
                    <!-- Static mockup as per design -->
                    <div class="timeline-day">
                        <div class="timeline-date">Oct 20, 2021 <span>...</span></div>
                        <div class="timeline-item">
                            <span class="time">10:00</span>
                            <div class="timeline-event timeline-event--teal">
                                <strong>Dribbble shot</strong>
                                <span>Facebook Brand</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <span class="time">13:20</span>
                            <div class="timeline-event timeline-event--orange">
                                <strong>Design</strong>
                                <span>Task Management</span>
                            </div>
                        </div>
                    </div>

                    <div class="timeline-day">
                        <div class="timeline-date">Oct 21, 2021 <span>...</span></div>
                        <div class="timeline-item">
                            <span class="time">10:00</span>
                            <div class="timeline-event timeline-event--purple">
                                <strong>UX Research</strong>
                                <span>Sleep App</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <span class="time">13:20</span>
                            <div class="timeline-event timeline-event--orange">
                                <strong>Design</strong>
                                <span>Task Management</span>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
