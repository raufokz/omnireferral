<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#002366">

    <title>{{ $meta['title'] ?? 'OmniReferral | Top-Tier Real Estate Referral Platform' }}</title>
    <meta name="description" content="{{ $meta['description'] ?? 'Connect with elite real estate agents using the modern referral workflow trusted by top US teams. Premium ISA-qualified leads and automated routing.' }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@500;600;700;800&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(request()->routeIs('login', 'register', 'password.*'))
        <link rel="stylesheet" href="{{ asset('css/auth-custom.css') }}">
    @endif
    @php
        $organizationSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'OmniReferral',
            'url' => url('/'),
            'logo' => asset('images/logo.png'),
            'sameAs' => [
                'https://facebook.com/omnireferral',
                'https://twitter.com/omnireferral',
                'https://linkedin.com/company/omnireferral',
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
</head>
@php
    $bodyClass = collect([
        request()->routeIs('home') ? 'page-home' : null,
        request()->routeIs('dashboard*', 'admin.*') ? 'page-dashboard' : null,
        request()->routeIs('login', 'register', 'password.*') ? 'page-auth' : null,
    ])->filter()->implode(' ');
@endphp
<body class="antialiased {{ $bodyClass }}" data-google-maps-api-key="{{ config('services.google_maps.key') }}">


    @include('partials.header')

    <main id="main-content">
        @if (session('success'))
            <div class="app-flash" role="alert">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials.footer')

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    duration: 800,
                    easing: 'ease-out-cubic',
                    once: true,
                    offset: 50
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>

